<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'title' => 'System Tasks',

    'card_scheduler' => 'Scheduled Tasks',
    'scheduler_down' => 'Scheduler is not running (heartbeat: :heartbeat). Scheduled tasks will not execute — configure crontab `php artisan schedule:run` in production, or run `php artisan schedule:work` locally.',
    'task_name'      => 'Task',
    'last_run'       => 'Last Run',
    'minutes_ago'    => ':minutes min ago',
    'never_run'      => 'Never',
    'status'         => 'Status',
    'status_running' => 'Running',
    'status_stale'   => 'Not Running',

    'task_scheduler'                   => 'Scheduler heartbeat (every 5 min)',
    'task_queue'                       => 'Queue heartbeat (every 5 min, written by worker)',
    'task_visits_aggregate_today'      => 'Visit stats: today rollup (every 5 min)',
    'task_visits_aggregate_yesterday'  => 'Visit stats: yesterday rollup (daily 00:05)',
    'task_visits_tag_bots_weekly'      => 'Visit stats: tag bots (weekly Mon 03:00)',
    'task_visits_backfill_geo_nightly' => 'Visit geo backfill (daily 03:30)',

    'card_queue'              => 'Queue',
    'queue_connection'        => 'Connection',
    'queue_pending'           => 'Pending Jobs',
    'queue_failed'            => 'Failed Jobs',
    'queue_sync_warning'      => 'QUEUE_CONNECTION=sync runs jobs inline within web requests. Configure redis and a queue worker for real async processing.',
    'queue_sync_badge'        => 'Sync Mode',
    'queue_redis_down'        => 'Redis Down',
    'queue_redis_down_hint'   => 'Cannot connect to Redis — queue status unknown. Redis is the queue backend; status refreshes automatically once it is reachable again.',
    'queue_workers_down'      => 'Workers Down',
    'queue_workers_down_hint' => 'No queue consumer is running (Horizon / queue:work) — pending jobs will not be processed. Check the supervisord daemon and restart Horizon. This indicator may lag up to 15 seconds behind an actual process death.',
    'queue_workers_running'   => 'Running',
    'queue_masters'           => 'Horizon masters',
    'queue_view_horizon'      => 'Open Horizon Dashboard',

    'card_visit_aggregate' => 'Visit Statistics Aggregation',
    'coverage_range'       => 'Daily Coverage',
    'coverage_empty'       => 'No data',
    'today_ready'          => 'Today: ready',
    'today_missing'        => 'Today: not aggregated yet',
    'yesterday_ready'      => 'Yesterday: ready',
    'yesterday_missing'    => 'Yesterday: missing',
    'missing_dates'        => 'Missing dates (last 30 days)',
    'missing_days'         => ':count days',
    'no_missing'           => 'None',

    'aggregate_date'      => 'Date',
    'aggregate_yesterday' => 'Aggregate Yesterday',
    'aggregate_range'     => 'Aggregate Last 30 Days',
    'aggregate_submit'    => 'Aggregate',
    'aggregate_refresh'   => 'Refresh',

    'guide_title'             => 'Usage Guide',
    'guide_section_scheduler' => 'Scheduled Tasks',
    'guide_scheduler_crontab' => 'Production: run the scheduler every minute via crontab',
    'guide_scheduler_local'   => 'Local development: run the long-running scheduler process',
    'guide_section_queue'     => 'Queue',
    'guide_queue_connection'  => 'Connection in .env',
    'guide_queue_consumer'    => 'Start a consumer process (or Horizon)',
    'guide_section_status'    => 'Status Meaning',
    'guide_status_running'    => 'Last heartbeat within the expected interval',
    'guide_status_stale'      => 'Heartbeat expired, the task may have stopped',
    'guide_status_never'      => 'No run record yet',
    'guide_current_queue'     => 'Current queue connection:',
];
