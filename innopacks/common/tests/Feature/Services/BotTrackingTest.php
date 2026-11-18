<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Repositories\VisitRepo;
use InnoShop\Common\Services\EventTrackingService;
use InnoShop\Common\Services\VisitStatisticsService;
use InnoShop\Common\Services\VisitTrackingService;
use Tests\TestCase;

class BotTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_bot_request_goes_to_bot_daily_and_never_to_visits(): void
    {
        $request = $this->makeRequest('GPTBot/1.1 (+https://openai.com/gptbot)', '1.2.3.4');

        app(VisitTrackingService::class)->trackVisit($request, 'sess-bot-1');

        $this->assertSame(0, DB::table('visits')->count(), 'bots must never land in visits');
        $this->assertSame(0, DB::table('visit_events')->count(), 'bots must never land in visit_events');

        $row = DB::table('bot_daily')->first();
        $this->assertNotNull($row);
        $this->assertSame('openai', $row->bot_brand);
        $this->assertSame('geo', $row->category);
        $this->assertSame(1, (int) $row->visits);
        $this->assertSame(1, (int) $row->page_views, 'GET request counts as a page view');
        $this->assertSame('1.2.3.4', $row->last_ip);
    }

    public function test_bot_upsert_accumulates_and_dedups_ips(): void
    {
        $service = app(VisitTrackingService::class);

        // Same bot, same IP, twice.
        $service->trackVisit($this->makeRequest('curl/8.4.0', '1.2.3.4'), 's1');
        $service->trackVisit($this->makeRequest('curl/8.4.0', '1.2.3.4'), 's2');
        // Same bot, different IP.
        $service->trackVisit($this->makeRequest('curl/8.4.0', '5.6.7.8'), 's3');
        // Different brand, same day.
        $service->trackVisit($this->makeRequest('bingbot/2.0', '1.2.3.4'), 's4');

        $this->assertSame(2, DB::table('bot_daily')->count(), 'one row per brand per day');

        $curl = DB::table('bot_daily')->where('bot_brand', 'curl')->first();
        $this->assertSame(3, (int) $curl->visits);
        $this->assertSame(2, DB::table('bot_daily_ips')->where('brand', 'curl')->count(), 'same-day same-brand IPs dedup');
        $this->assertSame(1, DB::table('bot_daily_ips')->where('brand', 'bing')->count());
    }

    public function test_human_request_still_lands_in_visits(): void
    {
        $request = $this->makeRequest('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 Version/17.4 Safari/605.1.15', '9.9.9.9');

        app(VisitTrackingService::class)->trackVisit($request, 'sess-human-1');

        $this->assertSame(1, DB::table('visits')->count());
        $this->assertSame(0, DB::table('bot_daily')->count());
    }

    public function test_event_tracking_rejects_bot_user_agents(): void
    {
        $request = $this->makeRequest('masscan/1.3', '1.2.3.4');

        $event = app(EventTrackingService::class)->track('page_view', [], $request);

        $this->assertNull($event, 'bots must not produce events');
        $this->assertSame(0, DB::table('visit_events')->count());
    }

    public function test_loopback_ip_is_not_tracked(): void
    {
        $service = app(VisitTrackingService::class);

        $service->trackVisit($this->makeRequest('Mozilla/5.0 real-browser', '127.0.0.1'), 'loopback-1');
        $service->trackVisit($this->makeRequest('Mozilla/5.0 real-browser', '::1'), 'loopback-2');

        $this->assertSame(0, DB::table('visits')->count(), 'loopback traffic must not create visits');
        $this->assertSame(0, DB::table('visit_events')->count(), 'loopback traffic must not create events');
    }

    public function test_repo_bot_reads_work_against_prefixed_tables(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 12:00:00'));

        $service = app(VisitTrackingService::class);
        // Two brands, two IPs, two days.
        $service->trackVisit($this->makeRequest('curl/8.4.0', '1.2.3.4'), 's1');
        $service->trackVisit($this->makeRequest('curl/8.4.0', '5.6.7.8'), 's2');
        $service->trackVisit($this->makeRequest('GPTBot/1.1', '1.2.3.4'), 's3');

        $repo    = new VisitRepo;
        $filters = ['start_date' => '2026-06-15', 'end_date' => '2026-06-15'];

        // Reads hit the same tables the tracker wrote — a double prefix or
        // wrong table name here shows up as a 500 on /panel/analytics/bot.
        $stats = $repo->getBotStatistics($filters);
        $this->assertSame(3, $stats['sessions']);
        $this->assertSame(2, $stats['unique_visitors'], 'cross-brand distinct IPs');

        $agg = $repo->getBotAggregates($filters);
        $this->assertSame(3, $agg['daily'][0]['sessions']);
        $this->assertSame(3, $agg['page_views']);
        $this->assertSame(0, $agg['event_sessions']);

        $breakdown = $repo->getBotBreakdown($filters, 10);
        $brands    = array_column($breakdown['brands'], 'sessions', 'brand');
        $this->assertSame(2, $brands['curl']);
        $this->assertSame(1, $brands['openai']);
        $curlRow = collect($breakdown['brands'])->firstWhere('brand', 'curl');
        $this->assertSame(2, $curlRow['unique_ips']);
        $this->assertSame('suspicious', $breakdown['categories']['suspicious']['category']);
        $this->assertSame(2, $breakdown['categories']['suspicious']['sessions']);
        $this->assertSame(1, $breakdown['categories']['geo']['sessions']);
    }

    public function test_aggregate_guard_protects_detail_less_history(): void
    {
        $date = Carbon::parse('2026-06-15');

        // History day with aggregates but no detail rows (already pruned).
        DB::table('visit_daily')->insert([
            'date'       => $date->toDateString(),
            'pv'         => 111,
            'uv'         => 99,
            'ip'         => 88,
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        (new VisitStatisticsService)->aggregateDaily($date);

        $row = DB::table('visit_daily')->where('date', $date->toDateString())->first();
        $this->assertSame(99, (int) $row->uv, 'guard must keep pruned-day aggregates intact');
    }

    public function test_aggregate_without_detail_and_without_aggregates_creates_zero_row(): void
    {
        $date = Carbon::parse('2026-06-16');

        // aggregateVisitDaily directly: the full aggregateDaily chain uses
        // MySQL-only HOUR() in aggregateHourlyDaily, which sqlite cannot run.
        (new VisitStatisticsService)->aggregateVisitDaily($date);

        // sqlite stores date-cast values as 'YYYY-MM-DD 00:00:00'; the table
        // holds exactly this one row, so first() avoids exact-string matching.
        $row = DB::table('visit_daily')->first();
        $this->assertNotNull($row, 'zero-traffic days aggregate once so ensureDailyAggregated stops re-dispatching');
    }

    public function test_daily_aggregate_excludes_bot_sessions(): void
    {
        $date = Carbon::parse('2026-06-17');
        $now  = $date->copy()->setTime(10, 0);

        // One human session and one bot session, each with two page views.
        $this->insertVisit('human-session', '203.0.113.10', $now, isBot: false);
        $this->insertVisit('bot-session', '203.0.113.11', $now, isBot: true);
        $this->insertEvent('human-session', '203.0.113.10', $now);
        $this->insertEvent('human-session', '203.0.113.10', $now);
        $this->insertEvent('bot-session', '203.0.113.11', $now);
        $this->insertEvent('bot-session', '203.0.113.11', $now);

        (new VisitStatisticsService)->aggregateVisitDaily($date);

        $row = DB::table('visit_daily')->first();
        $this->assertSame(2, (int) $row->pv, 'bot page views must not count toward PV');
        $this->assertSame(1, (int) $row->uv, 'bot session must not count toward UV');
        $this->assertSame(1, (int) $row->ip, 'bot IP must not count toward IP');
    }

    public function test_tag_bots_command_marks_google_other_sessions(): void
    {
        // GoogleOther carries no "bot" token, so it slipped every keyword
        // list in the field and inflated production PV/UV by ~18%.
        $now = Carbon::parse('2026-06-18 09:00:00');
        $this->insertVisit('google-other-1', '198.51.100.20', $now, isBot: false, userAgent: 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.8010.52 Mobile Safari/537.36 (compatible; GoogleOther)');
        $this->insertVisit('real-human-1', '198.51.100.21', $now, isBot: false, userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 Version/17.4 Safari/605.1.15');

        $this->artisan('visits:tag-bots')->assertSuccessful();

        $this->assertSame(1, DB::table('visits')->where('session_id', 'google-other-1')->where('is_bot', true)->count(), 'GoogleOther must be tagged as bot');
        $this->assertSame(0, DB::table('visits')->where('session_id', 'real-human-1')->where('is_bot', true)->count(), 'real browser must stay human');
    }

    private function insertVisit(
        string $sessionId,
        string $ip,
        Carbon $at,
        bool $isBot,
        string $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 Version/17.4 Safari/605.1.15'
    ): void {
        DB::table('visits')->insert([
            'session_id'       => $sessionId,
            'ip_address'       => $ip,
            'device_type'      => $isBot ? 'bot' : 'desktop',
            'user_agent'       => $userAgent,
            'is_bot'           => $isBot,
            'first_visited_at' => $at,
            'last_visited_at'  => $at,
            'created_at'       => $at,
            'updated_at'       => $at,
        ]);
    }

    private function insertEvent(string $sessionId, string $ip, Carbon $at): void
    {
        DB::table('visit_events')->insert([
            'session_id' => $sessionId,
            'event_type' => 'page_view',
            'ip_address' => $ip,
            'page_url'   => 'https://example.com/',
            'created_at' => $at,
            'updated_at' => $at,
        ]);
    }

    private function makeRequest(string $userAgent, string $ip): Request
    {
        $request = Request::create('https://example.com/products/foo', 'GET');
        $request->headers->set('User-Agent', $userAgent);
        $request->server->set('REMOTE_ADDR', $ip);

        return $request;
    }
}
