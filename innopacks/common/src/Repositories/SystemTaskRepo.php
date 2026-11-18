<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use InnoShop\Common\Models\SystemTaskHeartbeat;

class SystemTaskRepo
{
    /**
     * Known heartbeat keys and their expected maximum interval in minutes.
     * A heartbeat older than the interval means the corresponding task is
     * not running on schedule (usually the scheduler itself is down).
     */
    public const TASKS = [
        'scheduler'                   => 11,
        'queue'                       => 11,
        'visits-aggregate-today'      => 11,
        'visits-aggregate-yesterday'  => 24 * 60 + 60,
        'visits-tag-bots-weekly'      => 7 * 24 * 60 + 240,
        'visits-backfill-geo-nightly' => 24 * 60 + 240,
        'geolite2-download-weekly'    => 7 * 24 * 60 + 240,
    ];

    /**
     * Record a heartbeat for a task (upsert).
     */
    public static function touch(string $taskKey): void
    {
        SystemTaskHeartbeat::query()->updateOrCreate(
            ['task_key' => $taskKey],
            ['ran_at' => Carbon::now()]
        );
    }

    /**
     * Last heartbeat of a task, null when it never ran.
     */
    public static function lastRunAt(string $taskKey): ?Carbon
    {
        $ranAt = SystemTaskHeartbeat::query()->where('task_key', $taskKey)->value('ran_at');

        return $ranAt ? Carbon::parse($ranAt) : null;
    }

    /**
     * Heartbeats of all known tasks, keyed by task key.
     *
     * @return array<string, ?CarbonInterface>
     */
    public static function allHeartbeats(): array
    {
        $rows = SystemTaskHeartbeat::query()
            ->whereIn('task_key', array_keys(self::TASKS))
            ->pluck('ran_at', 'task_key');

        $heartbeats = [];
        foreach (self::TASKS as $taskKey => $expectedMinutes) {
            $ranAt = $rows[$taskKey] ?? null;
            if ($ranAt) {
                $ranAt = $ranAt instanceof CarbonInterface ? $ranAt : Carbon::parse($ranAt);
            }
            $heartbeats[$taskKey] = $ranAt;
        }

        return $heartbeats;
    }
}
