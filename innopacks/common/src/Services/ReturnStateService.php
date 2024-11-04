<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\OrderReturn;
use Throwable;

class ReturnStateService
{
    private OrderReturn $orderReturn;

    private int $orderReturnId;

    private string $comment;

    private bool $notify;

    /**
     * Created, initial status.
     */
    public const CREATED = 'created';

    /**
     * Pending
     */
    public const PENDING = 'pending';

    /**
     * Refunded
     */
    public const REFUNDED = 'refunded';

    /**
     * Returned
     */
    public const RETURNED = 'returned';

    /**
     * Cancelled
     */
    public const CANCELLED = 'cancelled';

    public const ORDER_STATUS = [
        self::CREATED,
        self::PENDING,
        self::REFUNDED,
        self::RETURNED,
        self::CANCELLED,
    ];

    /**
     * Order process by state machine
     */
    public const MACHINES = [
        self::CREATED => [
            self::PENDING => ['updateStatus', 'addHistory', 'notifyNewOrder'],
        ],
        self::PENDING => [
            self::REFUNDED  => ['updateStatus', 'addHistory', 'notifyUpdateOrder'],
            self::CANCELLED => ['updateStatus', 'addHistory', 'notifyUpdateOrder'],
        ],
        self::REFUNDED => [
            self::RETURNED => ['updateStatus', 'addHistory', 'notifyUpdateOrder'],
        ],
    ];

    /**
     * @param  OrderReturn  $orderReturn
     */
    public function __construct(OrderReturn $orderReturn)
    {
        $this->orderReturn   = $orderReturn;
        $this->orderReturnId = $orderReturn->id;
    }

    /**
     * @param  $orderReturn
     * @return self
     */
    public static function getInstance($orderReturn): self
    {
        return new self($orderReturn);
    }

    /**
     * Set order return comment.
     *
     * @param  $comment
     * @return $this
     */
    public function setComment($comment): self
    {
        $this->comment = (string) $comment;

        return $this;
    }

    /**
     * Set order return notify or not.
     *
     * @param  $flag
     * @return $this
     */
    public function setNotify($flag): self
    {
        $this->notify = (bool) $flag;

        return $this;
    }

    /**
     * Get all order return statuses.
     *
     * @return array
     * @throws Exception
     */
    public static function getAllStatuses(): array
    {
        $result   = [];
        $statuses = self::ORDER_STATUS;
        foreach ($statuses as $status) {
            if ($status == self::CREATED) {
                continue;
            }
            $result[] = [
                'status' => $status,
                'name'   => trans("common/rma.$status"),
            ];
        }

        return fire_hook_filter('service.return_state.all_statuses', $result);
    }

    /**
     * Get all valid statuses, from paid to complete.
     *
     * @return string[]
     */
    public static function getValidStatuses(): array
    {
        return [
            self::REFUNDED,
            self::RETURNED,
        ];
    }

    /**
     * Retrieve the possible states that the current order return can transition to.
     *
     * @return array
     * @throws Exception
     */
    public function nextBackendStatuses(): array
    {
        $machines = $this->getMachines();

        $currentStatusCode = $this->orderReturn->status;
        $nextStatus        = $machines[$currentStatusCode] ?? [];

        if (empty($nextStatus)) {
            return [];
        }
        $nextStatusCodes = array_keys($nextStatus);
        $result          = [];
        foreach ($nextStatusCodes as $status) {
            $result[] = [
                'status' => $status,
                'name'   => trans("common/rma.{$status}"),
            ];
        }

        return $result;
    }

    /**
     * External method invocation to modify the order return status and process others.
     *
     * @param  $status
     * @param  string|null  $comment
     * @param  bool  $notify
     * @throws Exception
     */
    public function changeStatus($status, ?string $comment = '', bool $notify = false): void
    {
        $orderReturn   = $this->orderReturn;
        $oldStatusCode = $orderReturn->status;
        $newStatusCode = $status;

        $this->setComment($comment)->setNotify($notify);
        $this->validStatusCode($status);

        DB::beginTransaction();
        try {
            $functions = $this->getFunctions($oldStatusCode, $newStatusCode);
            if ($functions) {
                foreach ($functions as $function) {
                    if ($function instanceof \Closure) {
                        $function();

                        continue;
                    }

                    if (! method_exists($this, $function)) {
                        throw new Exception("{$function} not exist in StateMachine!");
                    }
                    $this->{$function}($oldStatusCode, $status);
                }
            }
            $data = ['order' => $orderReturn, 'status' => $status, 'comment' => $comment, 'notify' => $notify];
            fire_hook_action('service.return_state.change_status.after', $data);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if the current order return can be transitioned to a specific status.
     *
     * @param  $statusCode
     * @throws Exception
     */
    private function validStatusCode($statusCode): void
    {
        $orderReturnId     = $this->orderReturnId;
        $orderReturnNumber = $this->orderReturn->number;
        $currentStatusCode = $this->orderReturn->status;

        $nextStatusCodes = collect($this->nextBackendStatuses())->pluck('status')->toArray();
        if (! in_array($statusCode, $nextStatusCodes)) {
            throw new Exception("Order {$orderReturnId}({$orderReturnNumber}) is {$currentStatusCode}, cannot be changed to $statusCode");
        }
    }

    /**
     * Retrieve the state machine process, which can be modified by external plugins through a filter hook.
     *
     * @return mixed
     */
    private function getMachines(): mixed
    {
        $data = [
            'order'    => $this->orderReturn,
            'machines' => self::MACHINES,
        ];

        $data = fire_hook_filter('service.return_state.machines', $data);

        return $data['machines'] ?? [];
    }

    /**
     * Retrieve the events that need to be triggered based on the current order return status,
     * and the status it is about to transition to.
     *
     * @param  $oldStatus
     * @param  $newStatus
     * @return array
     */
    private function getFunctions($oldStatus, $newStatus): array
    {
        $machines = $this->getMachines();

        return $machines[$oldStatus][$newStatus] ?? [];
    }

    /**
     * Update order return status.
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     * @throws Throwable
     */
    private function updateStatus($oldCode, $newCode): void
    {
        $this->orderReturn->status = $newCode;
        $this->orderReturn->saveOrFail();
    }

    /**
     * Add an order return modification record.
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     * @throws Throwable
     */
    private function addHistory($oldCode, $newCode): void
    {
        $history = new OrderReturn\History([
            'order_return_id' => $this->orderReturnId,
            'status'          => $newCode,
            'notify'          => (int) $this->notify,
            'comment'         => $this->comment,
        ]);
        $history->saveOrFail();
    }

    /**
     * Send a new order return notification.
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     */
    private function notifyNewOrder($oldCode, $newCode): void
    {
        if (! $this->notify) {
            return;
        }
        //$this->orderReturn->notifyNewOrder();
    }

    /**
     * Send an order return status update notification.
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     */
    private function notifyUpdateOrder($oldCode, $newCode): void
    {
        if (! $this->notify) {
            return;
        }
        //$this->orderReturn->notifyUpdateOrder($oldCode);
    }
}
