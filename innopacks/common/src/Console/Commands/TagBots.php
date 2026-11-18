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
use InnoShop\Common\Models\Visit\Visit;
use InnoShop\Common\Services\VisitStatisticsService;

class TagBots extends Command
{
    protected $signature = 'visits:tag-bots
                            {--dry-run : 只统计,不实际写入}
                            {--include-suspicious : 额外标记行为可疑的 session (高频零 page_view)}';

    protected $description = '根据 user_agent 关键字回填 visits.is_bot,可选识别可疑高频零事件 session';

    private array $botPatterns = [
        'bot', 'crawler', 'spider', 'scrap', 'scout', 'scan', 'check', 'fetch',
        'archive', 'heritrix', 'archive.org',
        'slurp', 'teoma', 'ia_archiver',
        'googlebot', 'googleother', 'google-inspectiontool', 'storebot-google',
        'bingbot', 'baiduspider', 'sogou', 'yisouspider', 'bytespider',
        'duckduckbot', 'yandexbot', 'exabot', 'konqueror', 'facebot', 'facebookexternalhit',
        'twitterbot', 'linkedinbot', 'telegrambot', 'whatsapp', 'skypeuripreview',
        'ahrefsbot', 'semrushbot', 'dotbot', 'mj12bot', 'petalbot', 'applebot',
        'gptbot', 'chatgpt-user', 'claudebot', 'claude-user', 'ccbot', 'perplexitybot',
        'uptimerobot', 'statuscake', 'pingdom', 'site24x7', 'newrelicpinger',
        'python-requests', 'python-urllib', 'aiohttp', 'httpx', 'axios',
        'curl', 'wget', 'httpclient', 'okhttp', 'java/', 'go-http-client',
        'node-fetch', 'got ', 'lwp-',
        'masscan', 'nmap', 'nikto', 'sqlmap', 'wpscan', 'dirbuster', 'gobuster',
        'zgrab', 'censys', 'shodan', 'shadow',
    ];

    public function handle(): int
    {
        @set_time_limit(0);

        $dryRun            = (bool) $this->option('dry-run');
        $includeSuspicious = (bool) $this->option('include-suspicious');

        // 1. UA keyword matching
        $affectedDates = $this->tagByUserAgent($dryRun);

        // 2. Suspicious sessions (only when explicitly opted in)
        if ($includeSuspicious) {
            $extraDates    = $this->tagSuspiciousSessions($dryRun);
            $affectedDates = array_values(array_unique(array_merge($affectedDates, $extraDates)));
        }

        // 3. Report
        $total = Visit::query()->count();
        $bots  = Visit::query()->where('is_bot', true)->count();
        $this->newLine();
        $this->info("总记录: {$total} · 爬虫: {$bots} (".($total > 0 ? round($bots / $total * 100, 1) : 0).'%)');

        // 4. Re-aggregate only the dates that actually changed (NOT a fixed 8-day window).
        if (! $dryRun && ! empty($affectedDates)) {
            $this->reaggregateAffectedDates($affectedDates);
        }

        return self::SUCCESS;
    }

    /**
     * Tag visits whose user_agent matches a bot/crawler/scanner keyword.
     * Bulk UPDATE per chunk (one SQL round-trip per 500 rows), with an
     * is_bot=false gate to stay race-safe against concurrent writers.
     *
     * @return string[] distinct visit dates affected by this run
     */
    private function tagByUserAgent(bool $dryRun): array
    {
        $query = Visit::query()->where('is_bot', false)
            ->where(function ($q) {
                $q->whereNull('user_agent')
                    ->orWhere('user_agent', '');
                foreach ($this->botPatterns as $p) {
                    $q->orWhereRaw('LOWER(user_agent) LIKE ?', ['%'.$p.'%']);
                }
            });

        $matchedCount = (clone $query)->count();
        $this->info("UA 关键字命中: {$matchedCount} 条");

        if ($dryRun) {
            $this->warn('DRY RUN: 不写入');
            $this->previewMatched(10);

            return [];
        }

        // Collect affected DATES, never the id list: one run can tag millions
        // of rows, and holding every id in PHP exhausted the 128M CLI memory
        // limit before the aggregation step could even start.
        $affectedDates = [];
        $updated       = 0;
        $query->chunkById(500, function ($visits) use (&$updated, &$affectedDates) {
            $ids = $visits->pluck('id')->all();
            if (empty($ids)) {
                return;
            }
            $count = Visit::query()
                ->whereIn('id', $ids)
                ->where('is_bot', false)
                ->update(['is_bot' => true, 'device_type' => 'bot']);
            $updated += $count;
            foreach ($this->datesOf($ids) as $date) {
                $affectedDates[$date] = true;
            }
        });

        $this->info("已回填 is_bot=true: {$updated} 条");

        return array_keys($affectedDates);
    }

    /**
     * Distinct visit dates of the given visit ids.
     *
     * @param  int[]  $ids
     * @return string[]
     */
    private function datesOf(array $ids): array
    {
        return DB::table('visits')
            ->whereIn('id', $ids)
            ->select(DB::raw('DISTINCT DATE(first_visited_at) as d'))
            ->pluck('d')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Tag suspicious sessions: visits table exists but zero page_view events,
     * and UA also lacks any recognizable browser keyword (Chrome / Safari /
     * Firefox / Edge / Opera / IE). Conservative so legit API-only sessions
     * aren't collateral damage.
     *
     * @return string[] distinct visit dates affected by this run
     */
    private function tagSuspiciousSessions(bool $dryRun): array
    {
        $this->newLine();
        $this->info('扫描可疑 session (visits 存在但无任何 page_view 事件)...');

        $eventsTable = 'visit_events';

        $baseQuery = Visit::query()->where('is_bot', false)
            ->whereNotExists(function ($q) use ($eventsTable) {
                $q->select(DB::raw(1))
                    ->from($eventsTable)
                    ->whereColumn($eventsTable.'.session_id', 'visits.session_id')
                    ->whereIn('event_type', ['page_view', 'home_view', 'category_view', 'product_view', 'cart_view']);
            });

        $candidates = (clone $baseQuery)->count();
        $this->info("候选 (零 page_view 的 session): {$candidates}");

        if ($dryRun) {
            $this->warn('DRY RUN: 不写入');

            return [];
        }

        if ($candidates === 0) {
            return [];
        }

        $browserKeywords = ['Chrome', 'Safari', 'Firefox', 'Edg/', 'OPR/', 'MSIE', 'Trident'];

        $affectedDates = [];
        $updated       = 0;

        // Bulk UPDATE per chunk; re-apply both filter conditions inside the
        // UPDATE so a row that became eligible between SELECT and UPDATE is
        // caught, and a row that became ineligible is skipped.
        $baseQuery
            ->where(function ($q) use ($browserKeywords) {
                foreach ($browserKeywords as $b) {
                    $q->where('user_agent', 'not like', '%'.$b.'%');
                }
            })
            ->orderBy('id')
            ->chunkById(5000, function ($visits) use (&$updated, &$affectedDates, $eventsTable, $browserKeywords) {
                $ids = $visits->pluck('id')->all();
                if (empty($ids)) {
                    return;
                }
                $count = Visit::query()
                    ->whereIn('id', $ids)
                    ->where('is_bot', false)
                    ->whereNotExists(function ($q) use ($eventsTable) {
                        $q->select(DB::raw(1))
                            ->from($eventsTable)
                            ->whereColumn($eventsTable.'.session_id', 'visits.session_id')
                            ->whereIn('event_type', ['page_view', 'home_view', 'category_view', 'product_view', 'cart_view']);
                    })
                    ->where(function ($q) use ($browserKeywords) {
                        foreach ($browserKeywords as $b) {
                            $q->where('user_agent', 'not like', '%'.$b.'%');
                        }
                    })
                    ->update(['is_bot' => true, 'device_type' => 'bot']);
                $updated += $count;
                foreach ($this->datesOf($ids) as $date) {
                    $affectedDates[$date] = true;
                }
            });

        $this->info("已保守标记可疑 (非浏览器 UA): {$updated} 条");

        return array_keys($affectedDates);
    }

    /**
     * Re-run aggregateDaily for ONLY the dates that just changed, not the
     * previous fixed 8-day window.
     *
     * @param  string[]  $dates  Y-m-d dates
     */
    private function reaggregateAffectedDates(array $dates): void
    {
        try {
            $dates = array_values(array_unique(array_filter($dates)));

            if (empty($dates)) {
                $this->info('无受影响的日期，跳过聚合刷新');

                return;
            }

            sort($dates);
            $service = app(VisitStatisticsService::class);
            foreach ($dates as $d) {
                $service->aggregateDaily(Carbon::parse($d));
            }
            $this->info('已重算受影响日期: '.implode(', ', $dates));
        } catch (\Throwable $e) {
            $this->warn('重算聚合数据失败: '.$e->getMessage());
        }
    }

    private function previewMatched(int $limit): void
    {
        $rows = Visit::query()->where('is_bot', false)
            ->where(function ($q) {
                $q->whereNull('user_agent')->orWhere('user_agent', '');
                foreach ($this->botPatterns as $p) {
                    $q->orWhereRaw('LOWER(user_agent) LIKE ?', ['%'.$p.'%']);
                }
            })
            ->select('id', 'user_agent', 'ip_address')
            ->limit($limit)
            ->get();
        foreach ($rows as $r) {
            $ua = $r->user_agent ?: '(empty)';
            if (mb_strlen($ua) > 90) {
                $ua = mb_substr($ua, 0, 90).'...';
            }
            $this->line("  [{$r->id}] {$r->ip_address}  {$ua}");
        }
    }
}
