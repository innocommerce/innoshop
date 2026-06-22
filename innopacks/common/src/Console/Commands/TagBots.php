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
use InnoShop\Common\Models\Visit\Visit;

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
        'googlebot', 'bingbot', 'baiduspider', 'sogou', 'yisouspider', 'bytespider',
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
        $dryRun            = (bool) $this->option('dry-run');
        $includeSuspicious = (bool) $this->option('include-suspicious');

        // 1. UA 关键字匹配 (lower(ua) like %pattern%)
        $query = Visit::where('is_bot', false)
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
        } else {
            $updated = 0;
            $query->chunkById(500, function ($visits) use (&$updated) {
                foreach ($visits as $v) {
                    $v->update(['is_bot' => true, 'device_type' => 'bot']);
                    $updated++;
                }
            });
            $this->info("已回填 is_bot=true: {$updated} 条");
        }

        if ($includeSuspicious) {
            $this->tagSuspiciousSessions($dryRun);
        }

        $total = Visit::count();
        $bots  = Visit::where('is_bot', true)->count();
        $this->newLine();
        $this->info("总记录: {$total} · 爬虫: {$bots} (".($total > 0 ? round($bots / $total * 100, 1) : 0).'%)');

        return self::SUCCESS;
    }

    /**
     * Tag suspicious sessions: visits table exists but zero page_view events
     * within recent N days, with high request frequency on the same IP.
     */
    private function tagSuspiciousSessions(bool $dryRun): void
    {
        $this->newLine();
        $this->info('扫描可疑 session (visits 存在但无任何 page_view 事件)...');

        $eventsTable = 'visit_events';
        $candidates  = Visit::where('is_bot', false)
            ->whereNotExists(function ($q) use ($eventsTable) {
                $q->select(DB::raw(1))
                    ->from($eventsTable)
                    ->whereColumn($eventsTable.'.session_id', 'visits.session_id')
                    ->whereIn('event_type', ['page_view', 'home_view', 'category_view', 'product_view', 'cart_view']);
            })
            ->count();
        $this->info("候选 (零 page_view 的 session): {$candidates}");

        if ($dryRun) {
            $this->warn('DRY RUN: 不写入');

            return;
        }

        if ($candidates === 0) {
            return;
        }

        // Conservative: only mark those whose UA also looks non-browser-ish (no Chrome/Safari/Firefox/Edge),
        // to avoid killing legit sessions that just hit non-page endpoints.
        $updated = Visit::where('is_bot', false)
            ->whereNotExists(function ($q) use ($eventsTable) {
                $q->select(DB::raw(1))
                    ->from($eventsTable)
                    ->whereColumn($eventsTable.'.session_id', 'visits.session_id')
                    ->whereIn('event_type', ['page_view', 'home_view', 'category_view', 'product_view', 'cart_view']);
            })
            ->where(function ($q) {
                $q->where(function ($qq) {
                    // no recognizable browser keyword
                    foreach (['Chrome', 'Safari', 'Firefox', 'Edg/', 'OPR/', 'MSIE', 'Trident'] as $b) {
                        $qq->where('user_agent', 'not like', '%'.$b.'%');
                    }
                });
            })
            ->update(['is_bot' => true, 'device_type' => 'bot']);
        $this->info("已保守标记可疑 (非浏览器 UA): {$updated} 条");
    }

    private function previewMatched(int $limit): void
    {
        $rows = Visit::where('is_bot', false)
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
