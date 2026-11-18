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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Rolling retention for visit tracking: real-visitor detail rows live for a
 * bounded window, bot IP dedup rows for a shorter one. The five aggregate
 * tables (visit_daily / visit_country_daily / visit_hourly_daily /
 * visit_device_daily / conversion_daily) and bot_daily are NEVER pruned —
 * statistics survive forever, only detail ages out.
 *
 * Schedule AFTER visits:aggregate (e.g. 00:10 aggregate, 00:40 prune):
 * expiring rows are long since aggregated, and the aggregateDaily guard
 * refuses to re-aggregate detail-less days anyway.
 */
class PruneVisits extends Command
{
    protected $signature = 'visits:prune
                            {--visit-days=180 : Real-visitor detail retention in days}
                            {--bot-days=30 : bot_daily_ips retention in days}
                            {--chunk=5000 : Rows per delete chunk}
                            {--dry-run : Report what would be deleted without deleting}';

    protected $description = 'Delete visit detail rows past the retention window (aggregates are never touched)';

    public function handle(): int
    {
        $dryRun    = (bool) $this->option('dry-run');
        $chunk     = max(100, (int) $this->option('chunk'));
        $visitDays = max(1, (int) $this->option('visit-days'));
        $botDays   = max(1, (int) $this->option('bot-days'));

        $visitCutoff = Carbon::today()->subDays($visitDays)->startOfDay();
        $botCutoff   = Carbon::today()->subDays($botDays)->startOfDay();

        $this->line("Real-visitor cutoff: first_visited_at < {$visitCutoff->toDateString()} ({$visitDays}d)");
        $this->line("Bot IP dedup cutoff: date < {$botCutoff->toDateString()} ({$botDays}d)");

        $visitCount = DB::table('visits')->where('is_bot', false)->where('first_visited_at', '<', $visitCutoff)->count();
        $eventCount = DB::table('visit_events')->where('created_at', '<', $visitCutoff)->count();
        $botIpCount = DB::table('bot_daily_ips')->where('date', '<', $botCutoff->toDateString())->count();

        $this->line("Would delete: {$visitCount} visits, {$eventCount} visit_events, {$botIpCount} bot_daily_ips rows.");

        if ($dryRun) {
            $this->warn('Dry run only — nothing deleted.');

            return self::SUCCESS;
        }

        $this->deleteInChunks('visit_events', 'created_at', $visitCutoff, $eventCount, $chunk);
        $this->deleteInChunks('visits', 'first_visited_at', $visitCutoff, $visitCount, $chunk);
        $this->deleteInChunksComposite('bot_daily_ips', 'date', $botCutoff->toDateString(), $botIpCount, $chunk);

        $this->info('Prune complete.');

        return self::SUCCESS;
    }

    /**
     * Chunked delete by id so a big first run never holds a long transaction
     * or blows the binlog.
     */
    protected function deleteInChunks(string $table, string $column, mixed $cutoff, int $expected, int $chunk): void
    {
        if ($expected === 0) {
            return;
        }

        $deleted = 0;
        $bar     = $this->output->createProgressBar($expected);

        do {
            $ids = DB::table($table)
                ->where($column, '<', $cutoff)
                ->limit($chunk)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            $count = DB::table($table)->whereIn('id', $ids)->delete();
            $deleted += $count;
            $bar->advance($count);
        } while (true);

        $bar->finish();
        $this->newLine();
        $this->info("{$table}: deleted {$deleted} rows.");
    }

    /**
     * Chunked delete for tables without an id column (bot_daily_ips is keyed
     * by date/ip/brand): delete bounded batches straight off the indexed
     * date prefix.
     */
    protected function deleteInChunksComposite(string $table, string $column, string $cutoff, int $expected, int $chunk): void
    {
        if ($expected === 0) {
            return;
        }

        $deleted = 0;
        $bar     = $this->output->createProgressBar($expected);

        do {
            $count = DB::table($table)
                ->where($column, '<', $cutoff)
                ->limit($chunk)
                ->delete();

            if ($count === 0) {
                break;
            }

            $deleted += $count;
            $bar->advance($count);
        } while (true);

        $bar->finish();
        $this->newLine();
        $this->info("{$table}: deleted {$deleted} rows.");
    }
}
