<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Console\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\Visit\Visit;
use InnoShop\Common\Services\VisitStatisticsService;
use InnoShop\Common\Tests\TestCase;
use Mockery;

class TagBotsTest extends TestCase
{
    use RefreshDatabase;

    private Carbon $date;

    protected function setUp(): void
    {
        parent::setUp();
        // Pin a fixed date so DATE(first_visited_at) is deterministic and
        // bot-pattern queries can match by exact UA without time-of-day drift.
        $this->date = Carbon::parse('2026-07-01');
    }

    public function test_ua_match_sets_is_bot_on_matching_visits(): void
    {
        $this->seedVisit('bot-google', isBot: false, ua: 'Mozilla/5.0 (compatible; Googlebot/2.1)');
        $this->seedVisit('bot-curl', isBot: false, ua: 'curl/8.4.0');
        $this->seedVisit('human-chrome', isBot: false, ua: 'Mozilla/5.0 (Macintosh) AppleWebKit/537.36 Chrome/124.0');

        $exit = Artisan::call('visits:tag-bots');

        $this->assertSame(0, $exit);
        $this->assertTrue($this->isBot('bot-google'), 'Googlebot UA must be tagged');
        $this->assertTrue($this->isBot('bot-curl'), 'curl UA must be tagged');
        $this->assertFalse($this->isBot('human-chrome'), 'Chrome UA must NOT be tagged');
    }

    public function test_ua_match_skips_visits_already_tagged_is_bot(): void
    {
        // Pre-tagged: must remain is_bot=true and must NOT be counted in
        // "UA 关键字命中" because the command's WHERE is_bot=false excludes it.
        $this->seedVisit('bot-pre', isBot: true, ua: 'Mozilla/5.0 (compatible; Googlebot/2.1)');
        $this->seedVisit('bot-new', isBot: false, ua: 'curl/8.4.0');

        $this->artisan('visits:tag-bots')
            ->expectsOutputToContain('UA 关键字命中: 1 条')
            ->assertExitCode(0);

        $this->assertTrue($this->isBot('bot-pre'), 'pre-tagged must stay tagged');
        $this->assertTrue($this->isBot('bot-new'), 'newly matched must be tagged');
    }

    public function test_include_suspicious_flags_zero_pageview_non_browser_ua(): void
    {
        // SyntheticClient/1.0 matches none of botPatterns and none of
        // Chrome/Safari/Firefox/Edg/OPR/MSIE/Trident, so it bypasses UA-pass
        // and falls into the suspicious filter.
        $this->seedVisit('susp-zero', isBot: false, ua: 'SyntheticClient/1.0');
        // No seedEvent() — zero page_view events.

        $this->artisan('visits:tag-bots', ['--include-suspicious' => true])
            ->expectsOutputToContain('已保守标记可疑 (非浏览器 UA): 1 条')
            ->assertExitCode(0);

        $this->assertTrue($this->isBot('susp-zero'), 'zero-pageview + non-browser UA must be flagged');
    }

    public function test_include_suspicious_does_not_flag_visits_with_page_view_events(): void
    {
        // Same non-browser UA as above, but this session DOES have a page_view
        // event — the conservative filter must leave it alone.
        $this->seedVisit('susp-pv', isBot: false, ua: 'SyntheticClient/1.0');
        $this->seedEvent('susp-pv', 'page_view');

        Artisan::call('visits:tag-bots', ['--include-suspicious' => true]);

        $this->assertFalse($this->isBot('susp-pv'), 'page_view events must shield a session from the suspicious filter');
    }

    public function test_dry_run_does_not_persist(): void
    {
        $this->seedVisit('bot-1', isBot: false, ua: 'Mozilla/5.0 (compatible; Googlebot/2.1)');

        $this->artisan('visits:tag-bots', ['--dry-run' => true])
            ->expectsOutputToContain('DRY RUN')
            ->assertExitCode(0);

        $this->assertFalse($this->isBot('bot-1'), 'dry-run must not write is_bot=true');
        $this->assertSame(0, DB::table('visit_daily')->count(), 'dry-run must not trigger re-aggregation');
    }

    public function test_tagging_triggers_reaggregation_of_affected_date(): void
    {
        // Two sessions on the same day. The bot one is freshly tagged by UA,
        // the human one is a real visitor. After the command, visit_daily.pv
        // must reflect only the human page_view (bot excluded by aggregation).
        $this->seedVisit('human-1', isBot: false, ua: 'Mozilla/5.0 (Macintosh) AppleWebKit/537.36 Chrome/124.0');
        $this->seedEvent('human-1', 'page_view');

        $this->seedVisit('bot-1', isBot: false, ua: 'Mozilla/5.0 (compatible; Googlebot/2.1)');
        $this->seedEvent('bot-1', 'page_view');

        Artisan::call('visits:tag-bots');

        $row = DB::table('visit_daily')
            ->whereDate('date', $this->date->toDateString())
            ->first();

        $this->assertNotNull($row, 'visit_daily row must exist for affected date');
        $this->assertSame(1, (int) $row->pv, 'only the human page_view should count after bot exclusion');
        $this->assertSame(1, (int) $row->uv, 'only the human session should count as uv');
    }

    public function test_reaggregation_only_touches_dates_with_freshly_tagged_visits(): void
    {
        // Tag a visit dated 3 days ago. The command must re-aggregate that
        // date only — NOT a hard-coded 8-day window.
        $pastDate = $this->date->copy()->subDays(3);
        $this->seedVisit('bot-old', isBot: false, ua: 'curl/8.4.0', firstVisitedAt: $pastDate);

        $expectedDate = $pastDate->toDateString();

        $mock = Mockery::mock(VisitStatisticsService::class);
        $mock->shouldReceive('aggregateDaily')
            ->once()
            ->with(Mockery::on(function (Carbon $d) use ($expectedDate) {
                return $d->toDateString() === $expectedDate;
            }))
            ->andReturnNull();

        $this->app->instance(VisitStatisticsService::class, $mock);

        Artisan::call('visits:tag-bots');

        // PHPUnit doesn't count Mockery expectations as assertions, so signal
        // that the mock's once() expectation was verified by tearDown.
        $this->addToAssertionCount(1);
    }

    private function seedVisit(
        string $sessionId,
        bool $isBot,
        string $ua = 'Mozilla/5.0',
        ?Carbon $firstVisitedAt = null,
    ): void {
        $first = ($firstVisitedAt ?? $this->date)->copy()->setTime(10, 0, 0);
        DB::table('visits')->insert([
            'session_id'       => $sessionId,
            'ip_address'       => '10.0.0.1',
            'device_type'      => $isBot ? 'bot' : 'desktop',
            'is_bot'           => $isBot,
            'user_agent'       => $ua,
            'first_visited_at' => $first,
            'last_visited_at'  => $first->copy()->addMinutes(5),
            'created_at'       => $first,
            'updated_at'       => $first,
        ]);
    }

    private function seedEvent(string $sessionId, string $eventType): void
    {
        $at = $this->date->copy()->setTime(10, 1, 0);
        DB::table('visit_events')->insert([
            'session_id' => $sessionId,
            'event_type' => $eventType,
            'ip_address' => '10.0.0.1',
            'created_at' => $at,
            'updated_at' => $at,
        ]);
    }

    private function isBot(string $sessionId): bool
    {
        return (bool) DB::table('visits')->where('session_id', $sessionId)->value('is_bot');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
