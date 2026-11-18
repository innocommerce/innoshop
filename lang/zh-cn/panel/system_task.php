<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'title' => '系统任务',

    'card_scheduler' => '定时任务',
    'scheduler_down' => '调度器未运行（心跳：:heartbeat），定时任务不会自动执行。生产环境请配置 crontab `php artisan schedule:run`，本地开发请运行 `php artisan schedule:work`。',
    'task_name'      => '任务',
    'last_run'       => '上次运行',
    'minutes_ago'    => ':minutes 分钟前',
    'never_run'      => '从未运行',
    'status'         => '状态',
    'status_running' => '运行中',
    'status_stale'   => '未运行',

    'task_scheduler'                   => '调度器心跳（每 5 分钟）',
    'task_queue'                       => '队列心跳（每 5 分钟，worker 执行时写入）',
    'task_visits_aggregate_today'      => '访问统计：今日汇总（每 5 分钟）',
    'task_visits_aggregate_yesterday'  => '访问统计：昨日汇总（每日 00:05）',
    'task_visits_tag_bots_weekly'      => '访问统计：机器人标记（每周一 03:00）',
    'task_visits_backfill_geo_nightly' => '访问地理数据回填（每日 03:30）',

    'card_queue'              => '队列',
    'queue_connection'        => '连接方式',
    'queue_pending'           => '待处理任务',
    'queue_failed'            => '失败任务',
    'queue_sync_warning'      => 'QUEUE_CONNECTION=sync 时任务会在 Web 请求内同步执行。请配置 redis 并启动队列消费进程以获得真正的异步处理。',
    'queue_sync_badge'        => '同步模式',
    'queue_redis_down'        => 'Redis 不可达',
    'queue_redis_down_hint'   => 'Redis 连接失败，队列状态未知。Redis 是队列的存储后端，连接恢复后状态自动更新。',
    'queue_workers_down'      => '消费进程未运行',
    'queue_workers_down_hint' => '队列消费进程（Horizon / queue:work）未运行，待处理任务不会被消费。请检查 supervisord 守护并重启 Horizon；进程停止后此状态最多延迟 15 秒更新。',
    'queue_workers_running'   => '运行中',
    'queue_masters'           => 'Horizon 主进程',
    'queue_view_horizon'      => '打开 Horizon 面板',

    'card_visit_aggregate' => '访问统计聚合',
    'coverage_range'       => '日汇总覆盖范围',
    'coverage_empty'       => '暂无数据',
    'today_ready'          => '今日：已就绪',
    'today_missing'        => '今日：尚未聚合',
    'yesterday_ready'      => '昨日：已就绪',
    'yesterday_missing'    => '昨日：缺失',
    'missing_dates'        => '缺失日期（近 30 天）',
    'missing_days'         => ':count 天',
    'no_missing'           => '无',

    'aggregate_date'      => '日期',
    'aggregate_yesterday' => '聚合昨日',
    'aggregate_range'     => '聚合近 30 天',
    'aggregate_submit'    => '聚合',
    'aggregate_refresh'   => '刷新',

    'guide_title'             => '使用说明',
    'guide_section_scheduler' => '定时任务配置',
    'guide_scheduler_crontab' => '生产环境：crontab 每分钟执行一次调度器',
    'guide_scheduler_local'   => '本地开发：运行常驻调度进程',
    'guide_section_queue'     => '队列配置',
    'guide_queue_connection'  => '.env 连接方式',
    'guide_queue_consumer'    => '启动消费进程（或 Horizon）',
    'guide_section_status'    => '状态说明',
    'guide_status_running'    => '最近一次心跳在预期间隔内',
    'guide_status_stale'      => '心跳已过期，任务可能已停止',
    'guide_status_never'      => '尚无运行记录',
    'guide_current_queue'     => '当前队列连接：',
];
