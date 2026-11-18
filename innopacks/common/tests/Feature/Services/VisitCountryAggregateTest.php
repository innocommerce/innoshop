<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Repositories\VisitRepo;
use InnoShop\Common\Services\VisitStatisticsService;
use InnoShop\Common\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class VisitCountryAggregateTest extends TestCase
{
    private Carbon $date;

    protected function setUp(): void
    {
        parent::setUp();

        $this->date = Carbon::parse('2026-06-15');
    }

    private function seedVisit(string $sessionId, string $ip, string $countryCode, string $countryName): void
    {
        DB::table('visits')->insert([
            'session_id'       => $sessionId,
            'ip_address'       => $ip,
            'country_code'     => $countryCode,
            'country_name'     => $countryName,
            'device_type'      => 'desktop',
            'is_bot'           => false,
            'first_visited_at' => $this->date->copy()->setTime(10, 0, 0),
            'last_visited_at'  => $this->date->copy()->setTime(10, 5, 0),
            'created_at'       => $this->date->copy()->setTime(10, 0, 0),
            'updated_at'       => $this->date->copy()->setTime(10, 0, 0),
        ]);
    }

    #[Test]
    public function test_country_aggregation_sums_and_excludes_unknown(): void
    {
        $this->seedVisit('s1', '1.1.1.1', 'US', 'United States');
        $this->seedVisit('s2', '1.1.1.2', 'US', 'United States');
        $this->seedVisit('s3', '2.2.2.2', 'CN', 'China');
        $this->seedVisit('s4', '3.3.3.3', '', '');

        (new VisitStatisticsService)->aggregateCountryDaily($this->date);

        $rows = (new VisitRepo)->getVisitsByCountry([
            'start_date' => $this->date->toDateString(),
            'end_date'   => $this->date->toDateString(),
        ]);

        $byCode = array_column($rows, null, 'country_code');

        $this->assertCount(2, $rows, 'unknown-geo visits must not enter country aggregation');
        $this->assertArrayNotHasKey('', $byCode);

        $this->assertSame(2, (int) $byCode['US']['visits']);
        $this->assertSame(2, (int) $byCode['US']['unique_visitors']);
        $this->assertSame(1, (int) $byCode['CN']['visits']);
        $this->assertSame(1, (int) $byCode['CN']['unique_visitors']);
    }

    #[Test]
    public function test_unknown_country_lang_key_exists(): void
    {
        $this->assertSame('Unknown', __('panel/visit.unknown'));
    }
}
