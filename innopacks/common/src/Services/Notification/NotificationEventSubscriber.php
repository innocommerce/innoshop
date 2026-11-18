<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\Notification;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Laravel\Horizon\Events\LongWaitDetected;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotificationEventSubscriber
{
    /**
     * Register system event listeners that trigger notifications.
     */
    public static function register(): void
    {
        Queue::failing(function (JobFailed $event) {
            try {
                $jobName   = $event->job->resolveName();
                $queue     = $event->job->getQueue();
                $attempts  = $event->job->attempts();
                $exception = $event->exception;

                // Jobs may declare SuppressesFailureNotification when their
                // failures are already surfaced elsewhere (e.g. a per-item
                // ledger with a panel view and retry action); otherwise a bulk
                // job would push one channel message per failed unit.
                if (is_a($jobName, SuppressesFailureNotification::class, true)) {
                    return;
                }

                $content = "> **Job:** {$jobName}\n"
                         ."> **Queue:** {$queue}\n"
                         ."> **Attempts:** {$attempts}\n"
                         .'> **Error:** '.$exception->getMessage()."\n"
                         .'> **File:** '.$exception->getFile().':'.$exception->getLine();

                NotificationManager::getInstance()->notify('Queue Job Failed', $content, 'error');
            } catch (\Throwable $e) {
                Log::warning('Failed to send queue failure notification: '.$e->getMessage());
            }
        });

        if (class_exists(LongWaitDetected::class)) {
            app('events')->listen(LongWaitDetected::class, function ($event) {
                try {
                    $content = "> **Connection:** {$event->connection}\n"
                             ."> **Queue:** {$event->queue}\n"
                             ."> **Wait:** {$event->seconds}s";

                    NotificationManager::getInstance()->notify('Queue Long Wait Detected', $content, 'warning');
                } catch (\Throwable $e) {
                    Log::warning('Failed to send long wait notification: '.$e->getMessage());
                }
            });
        }
    }

    /**
     * Send notification for an exception. Called from bootstrap/app.php reportable callback.
     */
    public static function notifyException(\Throwable $e): void
    {
        if ($e instanceof NotFoundHttpException) {
            return;
        }
        if ($e instanceof HttpException && $e->getStatusCode() < 500) {
            return;
        }

        // Queue job attempts are reported once per retry by the worker; the
        // single authoritative alert for a job is the Queue::failing event
        // fired after the last attempt. Without this guard every transient
        // retry would push its own message to the notification channels.
        foreach ($e->getTrace() as $frame) {
            $class = $frame['class'] ?? '';
            if (str_starts_with($class, 'Illuminate\Queue\\')
                || str_starts_with($class, 'Illuminate\Bus\\')) {
                return;
            }
        }

        try {
            $content = '> **Exception:** '.get_class($e)."\n"
                     .'> **Message:** '.$e->getMessage()."\n"
                     .'> **File:** '.$e->getFile().':'.$e->getLine()."\n"
                     .'> **URL:** '.request()->fullUrl();

            NotificationManager::getInstance()->notify('应用异常', $content, 'error');
        } catch (\Throwable $notifyException) {
            Log::warning('Failed to send exception notification: '.$notifyException->getMessage());
        }
    }
}
