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
use Illuminate\Queue\SerializesModels;
use InnoShop\Common\Repositories\SystemTaskRepo;

/**
 * Queue liveness probe. Dispatched by the scheduler every 5 minutes; the
 * heartbeat is written by the worker on execution, so a fresh record proves
 * the whole dispatch → dequeue → execute pipeline is alive.
 */
class QueueHeartbeatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        SystemTaskRepo::touch('queue');
    }
}
