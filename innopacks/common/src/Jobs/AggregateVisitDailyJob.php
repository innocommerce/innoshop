<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Services\VisitStatisticsService;
use Throwable;

/**
 * Aggregate one day of visit statistics into the daily summary tables.
 *
 * Dispatched from the dashboard read path when visit_daily is missing dates,
 * so a scheduler-less environment never blocks a web request with a heavy
 * visit_events scan.
 */
class AggregateVisitDailyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * A heavy day can take seconds; allow more than the default.
     */
    public $timeout = 600;

    public function __construct(public string $date) {}

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [(new WithoutOverlapping("visit-agg-{$this->date}"))->releaseAfter(60)];
    }

    public function handle(): void
    {
        try {
            (new VisitStatisticsService)->aggregateDaily(Carbon::parse($this->date));
        } catch (Throwable $e) {
            Log::warning("AggregateVisitDailyJob failed for {$this->date}: ".$e->getMessage());
        }
    }
}
