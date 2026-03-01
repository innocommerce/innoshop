<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use InnoShop\Common\Services\VisitStatisticsService;

class AggregateVisitStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visits:aggregate
                            {--date= : The date to aggregate (Y-m-d format, default: yesterday)}
                            {--backfill : Backfill all historical data}
                            {--from= : Start date for backfill (Y-m-d format)}
                            {--to= : End date for backfill (Y-m-d format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate daily visit and conversion statistics';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $service = new VisitStatisticsService;

        if ($this->option('backfill')) {
            return $this->handleBackfill($service);
        }

        $date = $this->option('date');

        if ($date) {
            try {
                $carbonDate = Carbon::parse($date);
            } catch (\Exception $e) {
                $this->error("Invalid date format: {$date}. Use Y-m-d format.");

                return Command::FAILURE;
            }
        } else {
            $carbonDate = Carbon::yesterday();
        }

        $this->info("Aggregating statistics for: {$carbonDate->toDateString()}");

        try {
            $service->aggregateDaily($carbonDate);
            $this->info('Statistics aggregated successfully.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to aggregate statistics: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    /**
     * Handle backfill mode.
     *
     * @param  VisitStatisticsService  $service
     * @return int
     */
    protected function handleBackfill(VisitStatisticsService $service): int
    {
        $from = $this->option('from');
        $to   = $this->option('to');

        if (! $from || ! $to) {
            $this->error('Both --from and --to dates are required for backfill.');

            return Command::FAILURE;
        }

        try {
            $startDate = Carbon::parse($from);
            $endDate   = Carbon::parse($to);
        } catch (\Exception $e) {
            $this->error('Invalid date format. Use Y-m-d format.');

            return Command::FAILURE;
        }

        if ($startDate->gt($endDate)) {
            $this->error('Start date must be before end date.');

            return Command::FAILURE;
        }

        $days = $startDate->diffInDays($endDate) + 1;
        $this->info("Backfilling statistics from {$startDate->toDateString()} to {$endDate->toDateString()} ({$days} days)");

        if (! $this->confirm("This will process {$days} days. Continue?")) {
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($days);
        $bar->start();

        $currentDate  = $startDate->copy();
        $successCount = 0;
        $errorCount   = 0;

        while ($currentDate->lte($endDate)) {
            try {
                $service->aggregateDaily($currentDate);
                $successCount++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->warn("Failed to aggregate {$currentDate->toDateString()}: {$e->getMessage()}");
                $errorCount++;
            }

            $currentDate->addDay();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Backfill complete: {$successCount} days successful, {$errorCount} days failed.");

        return Command::SUCCESS;
    }
}
