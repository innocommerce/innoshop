<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use InnoShop\Common\Jobs\AggregateVisitDailyJob;
use InnoShop\Common\Models\Visit\VisitDaily;
use InnoShop\Common\Repositories\SystemTaskRepo;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;

class SystemTaskController extends BaseController
{
    /**
     * System task status page: scheduler heartbeats, queue health and
     * visit statistics aggregation coverage.
     *
     * @return mixed
     */
    public function index(): mixed
    {
        $heartbeats  = SystemTaskRepo::allHeartbeats();
        $schedulerAt = $heartbeats['scheduler'];

        $schedulerStale = ! $schedulerAt || $schedulerAt->lt(Carbon::now()->subMinutes(SystemTaskRepo::TASKS['scheduler']));
        $heartbeatLabel = $schedulerAt
            ? trans('panel/system_task.minutes_ago', ['minutes' => (int) abs($schedulerAt->diffInMinutes())])
            : trans('panel/system_task.never_run');

        $tasks = [];
        foreach ($heartbeats as $taskKey => $lastRun) {
            if ($taskKey === 'scheduler') {
                continue;
            }
            $expectedMinutes = SystemTaskRepo::TASKS[$taskKey];
            $tasks[]         = [
                'key'         => $taskKey,
                'name'        => trans('panel/system_task.task_'.str_replace('-', '_', $taskKey)),
                'expected'    => $expectedMinutes,
                'last_run'    => $lastRun?->format('Y-m-d H:i:s'),
                'minutes_ago' => $lastRun ? (int) abs($lastRun->diffInMinutes()) : null,
                'stale'       => ! $lastRun || $lastRun->lt(Carbon::now()->subMinutes($expectedMinutes)),
            ];
        }

        $queueConnection = (string) config('queue.default');
        $queue           = $this->getQueueHealth($queueConnection);

        $coverage = $this->getVisitCoverage();

        return inno_view('panel::system-tasks.index', [
            'scheduler' => [
                'last_run'    => $schedulerAt?->format('Y-m-d H:i:s'),
                'minutes_ago' => $schedulerAt ? (int) abs($schedulerAt->diffInMinutes()) : null,
                'stale'       => $schedulerStale,
                'down_hint'   => trans('panel/system_task.scheduler_down', ['heartbeat' => $heartbeatLabel]),
            ],
            'tasks'    => $tasks,
            'queue'    => $queue,
            'coverage' => $coverage,
        ]);
    }

    /**
     * Dispatch aggregation jobs for a date or a date range.
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function aggregate(Request $request): mixed
    {
        try {
            $date      = $request->input('date');
            $startDate = $request->input('start_date');
            $endDate   = $request->input('end_date');

            if ($date) {
                AggregateVisitDailyJob::dispatch(Carbon::parse($date)->toDateString());
            } else {
                $start   = $startDate ? Carbon::parse($startDate) : Carbon::now()->subDays(29);
                $end     = $endDate ? Carbon::parse($endDate) : Carbon::yesterday();
                $current = $start->copy()->startOfDay();
                while ($current->lte($end)) {
                    AggregateVisitDailyJob::dispatch($current->toDateString());
                    $current->addDay();
                }
            }

            return json_success(trans('panel/analytics.reaggregate_queued'));
        } catch (\Throwable $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Queue health: Redis reachability, worker process liveness and job counts.
     * A dead Horizon master expires its Redis heartbeat within 15 seconds, so
     * `horizon_alive` reflects process death near real-time — the 5-minute
     * queue heartbeat row above only proves the dispatch pipeline afterwards.
     *
     * @param  string  $queueConnection
     * @return array
     */
    private function getQueueHealth(string $queueConnection): array
    {
        $health = [
            'connection'       => $queueConnection,
            'is_sync'          => $queueConnection === 'sync',
            'pending'          => null,
            'failed'           => 0,
            'redis_ok'         => true,
            'horizon_alive'    => null,
            'master_names'     => [],
            'can_view_horizon' => false,
        ];

        $pending = null;
        try {
            $pending = Queue::size();
            if (interface_exists(MasterSupervisorRepository::class)) {
                $masters                 = app(MasterSupervisorRepository::class)->all();
                $health['master_names']  = array_column($masters, 'name');
                $health['horizon_alive'] = $masters !== [];
            }
        } catch (\Throwable) {
            $health['redis_ok'] = false;
        }
        $health['pending']          = $health['redis_ok'] ? $pending : null;
        $health['can_view_horizon'] = Gate::allows('viewHorizon');

        try {
            $health['failed'] = (int) DB::table('failed_jobs')->count();
        } catch (\Throwable) {
            $health['failed'] = 0;
        }

        return $health;
    }

    /**
     * visit_daily coverage over the recent 30-day window.
     *
     * @return array
     */
    private function getVisitCoverage(): array
    {
        $windowStart = Carbon::today()->subDays(30);
        $windowEnd   = Carbon::yesterday();

        $existing = VisitDaily::query()
            ->whereBetween('date', [$windowStart->toDateString(), $windowEnd->toDateString()])
            ->pluck('date')
            ->map(fn ($date) => $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : (string) $date)
            ->flip();

        $missing = [];
        $current = $windowStart->copy();
        while ($current->lte($windowEnd)) {
            $dateStr = $current->toDateString();
            if (! isset($existing[$dateStr])) {
                $missing[] = $dateStr;
            }
            $current->addDay();
        }

        return [
            'daily_min'       => VisitDaily::query()->min('date'),
            'daily_max'       => VisitDaily::query()->max('date'),
            'today_ready'     => VisitDaily::query()->where('date', Carbon::today()->toDateString())->exists(),
            'yesterday_ready' => VisitDaily::query()->where('date', $windowEnd->toDateString())->exists(),
            'yesterday_date'  => $windowEnd->toDateString(),
            'missing_dates'   => $missing,
        ];
    }
}
