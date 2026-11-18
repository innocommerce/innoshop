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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Services\BotBehaviorClassifier;
use InnoShop\Common\Services\EventTrackingService;
use InnoShop\Common\Services\VisitTrackingService;
use Tests\TestCase;

/**
 * Behavioural bot detection — the gap that let the 2026-10-25~27 wave
 * (real browser UA + no session cookie) write ~124k fake sessions into the
 * visits table.
 */
class BotBehaviorClassifierTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['innoshop.visit_bot.enabled' => true]);

        Cache::flush();
    }

    public function test_cookie_less_burst_is_blocked_after_the_limit(): void
    {
        config(['innoshop.visit_bot.max_sessions_per_ip_day' => 3]);

        $this->insertShallowSessions('203.0.113.50', 3);

        $service = app(VisitTrackingService::class);

        // The limit is reached: this cookie-less request is the burst.
        $service->trackVisit($this->makeRequest($this->realBrowserUa(), '203.0.113.50'), 'sess-burst');

        $this->assertSame(3, DB::table('visits')->count(), 'burst sessions must never reach the visits table');

        $row = DB::table('bot_daily')->first();
        $this->assertNotNull($row);
        $this->assertSame('behavior', $row->bot_brand);
        $this->assertSame('suspicious', $row->category);
        $this->assertSame(1, (int) $row->visits);
        $this->assertSame('203.0.113.50', $row->last_ip);
    }

    public function test_cookie_less_requests_below_the_limit_are_still_visitors(): void
    {
        config(['innoshop.visit_bot.max_sessions_per_ip_day' => 8]);

        $this->insertShallowSessions('203.0.113.55', 3);

        app(VisitTrackingService::class)->trackVisit(
            $this->makeRequest($this->realBrowserUa(), '203.0.113.55'),
            'sess-4'
        );

        $this->assertSame(4, DB::table('visits')->count());
        $this->assertSame(0, DB::table('bot_daily')->count());
    }

    public function test_visits_carrying_the_session_cookie_are_never_counted(): void
    {
        // A browser always sends the session cookie back, so a NAT'd office
        // or a mobile CGNAT pool must never trip the burst rule.
        config(['innoshop.visit_bot.max_sessions_per_ip_day' => 2]);

        $this->insertShallowSessions('203.0.113.51', 10);

        $service = app(VisitTrackingService::class);

        for ($i = 1; $i <= 5; $i++) {
            $request = $this->makeRequest($this->realBrowserUa(), '203.0.113.51');
            $request->cookies->set(config('session.cookie'), 'session-'.$i);
            $service->trackVisit($request, 'sess-cookie-'.$i);
        }

        $this->assertSame(15, DB::table('visits')->count());
        $this->assertSame(0, DB::table('bot_daily')->count());
    }

    public function test_ip_with_multi_page_sessions_is_treated_as_human(): void
    {
        // The shared-egress-IP guard: a crawler fetches one page per session,
        // a human opens several. One real browsing session in the sample is
        // enough to spare the whole IP.
        config(['innoshop.visit_bot.max_sessions_per_ip_day' => 3]);

        $this->insertShallowSessions('203.0.113.52', 4);
        $this->insertVisit('sess-human', '203.0.113.52');
        $this->insertEvent('sess-human', '203.0.113.52', 'https://example.com/products/a');
        $this->insertEvent('sess-human', '203.0.113.52', 'https://example.com/products/b');

        app(VisitTrackingService::class)->trackVisit(
            $this->makeRequest($this->realBrowserUa(), '203.0.113.52'),
            'sess-burst'
        );

        $this->assertSame(6, DB::table('visits')->count(), 'an IP carrying a real browser must not be blocked');
        $this->assertSame(0, DB::table('bot_daily')->count());
    }

    public function test_logged_in_customer_is_never_treated_as_a_bot(): void
    {
        config(['innoshop.visit_bot.max_sessions_per_ip_day' => 1]);

        $this->insertShallowSessions('203.0.113.53', 5);

        $customer = new Customer;
        $customer->forceFill(['id' => 4321]);
        $this->actingAs($customer, 'customer');

        app(VisitTrackingService::class)->trackVisit(
            $this->makeRequest($this->realBrowserUa(), '203.0.113.53'),
            'sess-member'
        );

        $this->assertSame(6, DB::table('visits')->count(), 'a signed-in shopper is never a bot');
        $this->assertSame(0, DB::table('bot_daily')->count());
    }

    public function test_behaviour_bots_do_not_produce_events(): void
    {
        // Without event suppression the visit row is dropped but the page
        // view still lands in visit_events, and the panel keeps counting
        // the crawler's pages as PV.
        config(['innoshop.visit_bot.max_sessions_per_ip_day' => 1]);

        $this->insertShallowSessions('203.0.113.60', 1);

        $blocked = $this->makeRequest($this->realBrowserUa(), '203.0.113.60');
        app(VisitTrackingService::class)->trackVisit($blocked, 'sess-blocked');

        $this->assertSame(1, DB::table('visits')->count());

        $event = app(EventTrackingService::class)->track('page_view', [], $blocked);

        $this->assertNull($event, 'behaviour bots must not produce events');
        $this->assertSame(1, DB::table('visit_events')->count(), 'only the pre-existing session keeps its event');
    }

    public function test_verdict_is_memoised_on_the_request(): void
    {
        $request = $this->makeRequest($this->realBrowserUa(), '203.0.113.70');
        $request->attributes->set('inno.bot_behavior_verdict', ['is_bot' => true, 'brand' => 'behavior']);

        $verdict = app(BotBehaviorClassifier::class)->classify($request);

        $this->assertSame('behavior', $verdict['brand'], 'the memoised verdict must be reused, not recomputed');
    }

    public function test_disabled_classifier_never_blocks(): void
    {
        config([
            'innoshop.visit_bot.enabled'                 => false,
            'innoshop.visit_bot.max_sessions_per_ip_day' => 1,
        ]);

        $this->insertShallowSessions('203.0.113.80', 5);

        app(VisitTrackingService::class)->trackVisit(
            $this->makeRequest($this->realBrowserUa(), '203.0.113.80'),
            'sess-6'
        );

        $this->assertSame(6, DB::table('visits')->count());
        $this->assertSame(0, DB::table('bot_daily')->count());
    }

    public function test_request_from_unlisted_host_without_cookie_is_blocked(): void
    {
        // The e-mail-tracking alias vhosts share the main server block, so
        // crawlers walk in through them for free. A cookie-less guest
        // arriving there is not a human.
        config(['innoshop.visit_bot.allowed_hosts' => 'www.scselects.com,scselects.com']);

        $request = $this->makeRequest($this->realBrowserUa(), '203.0.113.90', 'https://em.scselects.com/products/foo');

        app(VisitTrackingService::class)->trackVisit($request, 'sess-alias');

        $this->assertSame(0, DB::table('visits')->count(), 'an alias-host crawler must never reach the visits table');

        $row = DB::table('bot_daily')->first();
        $this->assertNotNull($row);
        $this->assertSame('host', $row->bot_brand);
        $this->assertSame('suspicious', $row->category);
    }

    public function test_request_from_allowed_host_is_tracked(): void
    {
        config(['innoshop.visit_bot.allowed_hosts' => 'www.scselects.com,scselects.com']);

        $request = $this->makeRequest($this->realBrowserUa(), '203.0.113.91', 'https://www.scselects.com/products/foo');

        app(VisitTrackingService::class)->trackVisit($request, 'sess-www');

        $this->assertSame(1, DB::table('visits')->count());
        $this->assertSame(0, DB::table('bot_daily')->count());
    }

    public function test_cookie_back_on_unlisted_host_is_not_blocked(): void
    {
        // A real e-mail clicker lands on the alias host but sends the
        // session cookie back from its second page onwards.
        config(['innoshop.visit_bot.allowed_hosts' => 'www.scselects.com']);

        $request = $this->makeRequest($this->realBrowserUa(), '203.0.113.92', 'https://ei.scselects.com/products/foo');
        $request->cookies->set(config('session.cookie'), 'session-1');

        app(VisitTrackingService::class)->trackVisit($request, 'sess-alias-cookie');

        $this->assertSame(1, DB::table('visits')->count());
        $this->assertSame(0, DB::table('bot_daily')->count());
    }

    public function test_host_rule_is_inactive_without_allowlist(): void
    {
        // Default config: no allowlist configured, rule must stay off so
        // installs that never opted in keep tracking every host.
        $request = $this->makeRequest($this->realBrowserUa(), '203.0.113.93', 'https://em.scselects.com/products/foo');

        app(VisitTrackingService::class)->trackVisit($request, 'sess-no-allowlist');

        $this->assertSame(1, DB::table('visits')->count());
        $this->assertSame(0, DB::table('bot_daily')->count());
    }

    public function test_logged_in_customer_on_unlisted_host_is_tracked(): void
    {
        config(['innoshop.visit_bot.allowed_hosts' => 'www.scselects.com']);

        $customer = new Customer;
        $customer->forceFill(['id' => 4321]);
        $this->actingAs($customer, 'customer');

        $request = $this->makeRequest($this->realBrowserUa(), '203.0.113.94', 'https://em.scselects.com/products/foo');

        app(VisitTrackingService::class)->trackVisit($request, 'sess-member-alias');

        $this->assertSame(1, DB::table('visits')->count(), 'a signed-in shopper is never a bot, on any host');
        $this->assertSame(0, DB::table('bot_daily')->count());
    }

    public function test_stale_ua_first_hit_burst_is_blocked(): void
    {
        // The 2026-10-30 fingerprint: one stale Chrome version, thousands
        // of rotating IPs each hitting once. The per-version counter in the
        // cache is what moves; the per-IP counter never does.
        config([
            'innoshop.visit_bot.ua_burst_threshold'      => 3,
            'innoshop.visit_bot.max_sessions_per_ip_day' => 8,
        ]);

        $service = app(VisitTrackingService::class);

        for ($i = 1; $i <= 4; $i++) {
            $service->trackVisit(
                $this->makeRequest($this->staleBrowserUa(), "203.0.113.10{$i}"),
                'sess-stale-'.$i
            );
        }

        // Two cookie-less first hits pass, the third crosses the threshold,
        // everything after it is blocked too.
        $this->assertSame(2, DB::table('visits')->count(), 'the stale-UA burst must be cut off at the threshold');

        $row = DB::table('bot_daily')->where('bot_brand', 'ua_burst')->first();
        $this->assertNotNull($row);
        $this->assertSame('suspicious', $row->category);
        $this->assertSame(2, (int) $row->visits);
    }

    public function test_current_version_ua_never_trips_the_burst(): void
    {
        config([
            'innoshop.visit_bot.ua_burst_threshold'      => 3,
            'innoshop.visit_bot.max_sessions_per_ip_day' => 100,
        ]);

        $service = app(VisitTrackingService::class);

        for ($i = 1; $i <= 10; $i++) {
            $service->trackVisit(
                $this->makeRequest($this->realBrowserUa(), "203.0.113.20{$i}"),
                'sess-current-'.$i
            );
        }

        $this->assertSame(10, DB::table('visits')->count(), 'current browser versions are never a burst');
        $this->assertSame(0, DB::table('bot_daily')->count());
    }

    public function test_burst_counter_is_per_browser_version(): void
    {
        config([
            'innoshop.visit_bot.ua_burst_threshold'      => 3,
            'innoshop.visit_bot.max_sessions_per_ip_day' => 100,
        ]);

        $service = app(VisitTrackingService::class);

        for ($i = 1; $i <= 2; $i++) {
            $service->trackVisit(
                $this->makeRequest($this->staleBrowserUa('120'), "203.0.113.30{$i}"),
                'sess-v120-'.$i
            );
            $service->trackVisit(
                $this->makeRequest($this->staleBrowserUa('121'), "203.0.113.40{$i}"),
                'sess-v121-'.$i
            );
        }

        $this->assertSame(4, DB::table('visits')->count(), 'different stale versions must count into separate buckets');
        $this->assertSame(0, DB::table('bot_daily')->count());
    }

    public function test_stale_ua_bursts_do_not_produce_events(): void
    {
        config(['innoshop.visit_bot.ua_burst_threshold' => 2]);

        $first = $this->makeRequest($this->staleBrowserUa(), '203.0.113.50');
        app(VisitTrackingService::class)->trackVisit($first, 'sess-first-hit');

        $this->assertSame(1, DB::table('visits')->count());

        $blocked = $this->makeRequest($this->staleBrowserUa(), '203.0.113.51');
        app(VisitTrackingService::class)->trackVisit($blocked, 'sess-blocked-stale');

        $this->assertSame(1, DB::table('visits')->count(), 'the second first-hit crosses the threshold');

        $event = app(EventTrackingService::class)->track('page_view', [], $blocked);

        $this->assertNull($event, 'stale-UA burst bots must not produce events');
        $this->assertSame(0, DB::table('visit_events')->count(), 'no event may reference a session that was never recorded');
    }

    /**
     * Insert n sessions that each saw exactly one page — the shape a
     * cookie-less crawler leaves behind.
     */
    private function insertShallowSessions(string $ip, int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $sessionId = 'shallow-'.$ip.'-'.$i;
            $this->insertVisit($sessionId, $ip);
            $this->insertEvent($sessionId, $ip, 'https://example.com/category/'.$i);
        }
    }

    private function insertVisit(string $sessionId, string $ip): void
    {
        $now = Carbon::now();

        DB::table('visits')->insert([
            'session_id'       => $sessionId,
            'ip_address'       => $ip,
            'device_type'      => 'desktop',
            'user_agent'       => $this->realBrowserUa(),
            'is_bot'           => false,
            'first_visited_at' => $now,
            'last_visited_at'  => $now,
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);
    }

    private function insertEvent(string $sessionId, string $ip, string $url): void
    {
        $now = Carbon::now();

        DB::table('visit_events')->insert([
            'session_id' => $sessionId,
            'event_type' => 'page_view',
            'ip_address' => $ip,
            'page_url'   => $url,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function realBrowserUa(): string
    {
        return 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36';
    }

    private function staleBrowserUa(string $major = '120'): string
    {
        return "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$major}.0.0.0 Safari/537.36";
    }

    private function makeRequest(string $userAgent, string $ip, string $url = 'https://example.com/products/foo'): Request
    {
        $request = Request::create($url, 'GET');
        $request->headers->set('User-Agent', $userAgent);
        $request->server->set('REMOTE_ADDR', $ip);

        return $request;
    }
}
