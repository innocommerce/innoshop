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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Services\VisitStatisticsService;
use Tests\TestCase;

class VisitStatisticsBotFilterTest extends TestCase
{
    use RefreshDatabase;

    private Carbon $date;

    protected function setUp(): void
    {
        parent::setUp();
        $this->date = Carbon::parse('2026-06-15');
    }

    public function test_conversion_funnel_excludes_bot_sessions(): void
    {
        $this->seedVisit('human-1', false);
        $this->seedVisit('bot-1', true);

        $this->seedEvent('human-1', 'product_view');
        $this->seedEvent('bot-1', 'product_view');
        $this->seedEvent('bot-1', 'home_view');

        (new VisitStatisticsService)->aggregateConversionDaily($this->date);

        // sqlite stores the date cast as 'YYYY-MM-DD 00:00:00', so avoid
        // exact-string matching on the date column here
        $row = DB::table('conversion_daily')->first();

        $this->assertSame(1, (int) $row->product_views, 'bot product_view must not be counted');
        $this->assertSame(0, (int) $row->home_views, 'bot home_view must not be counted');
    }

    public function test_average_duration_excludes_bot_sessions(): void
    {
        $this->seedVisit('human-1', false);
        $this->seedVisit('bot-1', true);

        // Human: 2 events 120s apart. Bot: 2 events in the same second (0s).
        $at = $this->date->copy()->setTime(10, 0, 0);
        $this->seedEvent('human-1', 'page_view', $at);
        $this->seedEvent('human-1', 'page_view', $at->copy()->addSeconds(120));
        $this->seedEvent('bot-1', 'page_view', $at);
        $this->seedEvent('bot-1', 'page_view', $at);

        (new VisitStatisticsService)->aggregateVisitDaily($this->date);

        $row = DB::table('visit_daily')->first();

        $this->assertSame(120, (int) $row->avg_duration, '0s bot session must not drag the average down');
    }

    private function seedVisit(string $sessionId, bool $isBot): void
    {
        DB::table('visits')->insert([
            'session_id'       => $sessionId,
            'ip_address'       => $isBot ? '10.0.0.1' : '10.0.0.2',
            'device_type'      => $isBot ? 'bot' : 'desktop',
            'is_bot'           => $isBot,
            'first_visited_at' => $this->date->copy()->setTime(10, 0, 0),
            'last_visited_at'  => $this->date->copy()->setTime(10, 5, 0),
            'created_at'       => $this->date->copy()->setTime(10, 0, 0),
            'updated_at'       => $this->date->copy()->setTime(10, 0, 0),
        ]);
    }

    private function seedEvent(string $sessionId, string $eventType, ?Carbon $at = null): void
    {
        $at = $at ?? $this->date->copy()->setTime(10, 0, 0);
        DB::table('visit_events')->insert([
            'session_id' => $sessionId,
            'event_type' => $eventType,
            'ip_address' => '10.0.0.1',
            'created_at' => $at,
            'updated_at' => $at,
        ]);
    }
}
