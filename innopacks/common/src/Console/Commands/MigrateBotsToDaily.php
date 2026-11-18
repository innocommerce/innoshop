<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Services\BotClassifier;

/**
 * One-shot migration: fold legacy bot rows in `visits` into the bot_daily /
 * bot_daily_ips aggregates, then delete them. Idempotent — safe to re-run
 * until the delete step succeeds; after that there is simply nothing left
 * to fold.
 *
 * Order is a hard constraint: fold EVERYTHING first, delete last. Take a
 * backup (backup-db.sh) before running the deletion.
 */
class MigrateBotsToDaily extends Command
{
    protected $signature = 'visits:migrate-bots
                            {--dry-run : Report counts and a brand sample without writing anything}
                            {--chunk=2000 : Combination rows per chunk when folding and deleting}
                            {--keep-detail : Fold only — skip the deletion step}
                            {--delete-only : Skip folding, run only the deletion step (recovery: aggregates already correct, detail rows remain)}';

    protected $description = 'Fold legacy is_bot visits rows into bot_daily/bot_daily_ips, then delete them';

    public function handle(): int
    {
        $dryRun     = (bool) $this->option('dry-run');
        $deleteOnly = (bool) $this->option('delete-only');
        $chunk      = max(100, (int) $this->option('chunk'));

        $total = DB::table('visits')->where('is_bot', true)->count();
        $this->info("Legacy bot rows in visits: {$total}");

        if ($total === 0) {
            $this->info('Nothing to migrate.');

            return self::SUCCESS;
        }

        if ($deleteOnly) {
            // Recovery path: a previous run folded correctly but its chunk()
            // delete skipped rows. Aggregates are already correct — folding
            // again would double-count, so go straight to deletion.
            $this->warn('--delete-only: aggregates assumed correct, skipping fold.');
            $this->deleteBotRows($chunk);

            return self::SUCCESS;
        }

        $comboCount = DB::table('visits')
            ->where('is_bot', true)
            ->selectRaw('COUNT(DISTINCT DATE(first_visited_at), user_agent, ip_address) as c')
            ->value('c');
        $this->info("Unique (date, user_agent, ip) combinations to fold: {$comboCount}");

        if ($dryRun) {
            $this->showBrandSample($chunk);
            $this->warn('Dry run only — nothing written. Review the brand sample above, then run without --dry-run.');

            return self::SUCCESS;
        }

        $this->foldBotRows($chunk);

        if ($this->option('keep-detail')) {
            $this->warn('--keep-detail: folded only, detail rows left in place.');

            return self::SUCCESS;
        }

        $this->deleteBotRows($chunk);

        return self::SUCCESS;
    }

    /**
     * Show the top brands the fold would produce, with a sample UA per brand,
     * so a mapping bug is caught BEFORE detail rows are deleted — after
     * deletion there is no re-computing history.
     */
    protected function showBrandSample(int $chunk): void
    {
        $brands    = [];
        $sampleUas = [];

        $this->combinationRows($chunk, function (int $count, string $ua) use (&$brands, &$sampleUas) {
            $brand          = BotClassifier::classify($ua)['brand'] ?: 'unknown';
            $brands[$brand] = ($brands[$brand] ?? 0) + $count;
            $sampleUas[$brand] ??= $ua;
        });

        arsort($brands);
        $grandTotal = array_sum($brands) ?: 1;

        $this->table(
            ['brand', 'requests', 'category', 'sample user agent'],
            collect($brands)->take(20)->map(fn ($count, $brand) => [
                $brand,
                number_format($count),
                BotClassifier::categoryOf($brand),
                mb_substr($sampleUas[$brand] ?? '', 0, 60),
            ])->all()
        );

        $unknownShare = round((($brands['unknown'] ?? 0) / $grandTotal) * 100, 1);
        $this->line("unknown-brand share: {$unknownShare}%");
    }

    /**
     * Fold every bot row into bot_daily / bot_daily_ips. Each (date, ua, ip)
     * combination is classified once; page_views cannot be reconstructed
     * (bot requests are almost all GET), so page_views folds equal to visits.
     */
    protected function foldBotRows(int $chunk): void
    {
        $folded = 0;

        $this->combinationRows($chunk, function (int $count, string $ua, string $date, string $ip) use (&$folded) {
            $classification = BotClassifier::classify($ua);
            $brand          = $classification['brand'] ?: 'unknown';
            $category       = $classification['category'] ?: 'suspicious';

            // insertOrIgnore + row increment (not upsert/VALUES()) so the
            // fold runs identically on MySQL and sqlite.
            DB::table('bot_daily')->insertOrIgnore([
                'date'       => $date,
                'bot_brand'  => $brand,
                'category'   => $category,
                'visits'     => 0,
                'page_views' => 0,
                'ip_count'   => 0,
                'last_ip'    => $ip,
                'last_path'  => null,
            ]);

            DB::table('bot_daily')
                ->where('date', $date)
                ->where('bot_brand', $brand)
                ->update([
                    'visits'     => DB::raw('visits + '.$count),
                    'page_views' => DB::raw('page_views + '.$count),
                    'last_ip'    => $ip,
                ]);

            DB::table('bot_daily_ips')->insertOrIgnore([
                'date'  => $date,
                'ip'    => $ip,
                'brand' => $brand,
            ]);

            $folded += $count;
        });

        $this->info("Folded {$folded} bot requests into bot_daily / bot_daily_ips.");
    }

    /**
     * Iterate the folded rows grouped to unique (date, user_agent, ip)
     * combinations — bot UAs repeat heavily, so this set is far smaller
     * than the raw row count.
     *
     * @param  int  $chunk
     * @param  callable(int $count, string $userAgent, string $date, string $ip): void  $callback
     */
    protected function combinationRows(int $chunk, callable $callback): void
    {
        DB::table('visits')
            ->where('is_bot', true)
            ->selectRaw('DATE(first_visited_at) as d, user_agent, ip_address, COUNT(*) as c')
            ->groupByRaw('DATE(first_visited_at), user_agent, ip_address')
            ->orderBy('d')
            ->chunk($chunk, function ($rows) use ($callback) {
                foreach ($rows as $row) {
                    $callback((int) $row->c, (string) $row->user_agent, (string) $row->d, (string) $row->ip_address);
                }
            });
    }

    /**
     * Delete the folded detail rows in chunks — visits first, then the
     * events belonging to those bot sessions.
     */
    protected function deleteBotRows(int $chunk): void
    {
        if (! $this->confirm('Detail rows are about to be DELETED. Has a backup (backup-db.sh) been taken?')) {
            $this->warn('Aborted after folding — detail rows kept. Re-run to delete.');

            return;
        }

        $deleted = 0;
        $bar     = $this->output->createProgressBar();

        // chunkById, never chunk(): offset-based chunk() skips 2000 rows per
        // batch once the callback deletes what it just read (rows shift up,
        // the next offset lands past them). Verified in the field: chunk()
        // left 618k of 1.24M bot rows undeleted.
        DB::table('visits')
            ->where('is_bot', true)
            ->select('id', 'session_id')
            ->chunkById($chunk, function ($rows) use (&$deleted, $bar) {
                DB::table('visit_events')->whereIn('session_id', $rows->pluck('session_id'))->delete();
                DB::table('visits')->whereIn('id', $rows->pluck('id'))->delete();

                $deleted += $rows->count();
                $bar->advance();
            }, 'id');

        $bar->finish();
        $this->newLine(2);
        $this->info("Deleted {$deleted} bot visits rows and their events.");
    }
}
