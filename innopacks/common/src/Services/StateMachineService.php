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
use InnoShop\Common\Models\Order;
use InnoShop\Common\Models\Order\Shipment;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Repositories\Order\PaymentRepo;
use Throwable;

class StateMachineService
{
    private Order $order;

    private int $orderId;

    private string $comment;

    private bool $notify;

    private array $shipment = [];

    private array $payment = [];

    /**
     * Created, initial status.
     */
    public const CREATED = 'created';

    /**
     * Unpaid
     */
    public const UNPAID = 'unpaid';

    /**
     * Paid
     */
    public const PAID = 'paid';

    /**
     * Shipped
     */
    public const SHIPPED = 'shipped';

    /**
     * Completed
     */
    public const COMPLETED = 'completed';

    /**
     * Cancelled
     */
    public const CANCELLED = 'cancelled';

    public const ORDER_STATUS = [
        self::CREATED,
        self::UNPAID,
        self::PAID,
        self::SHIPPED,
        self::COMPLETED,
        self::CANCELLED,
    ];

    /**
     * Order process by state machine
     */
    public const MACHINES = [
        self::CREATED => [
            self::UNPAID => ['updateStatus', 'addHistory', 'notifyNewOrder'],
        ],
        self::UNPAID => [
            self::PAID      => ['updateStatus', 'addHistory', 'updateSales', 'subStock', 'notifyUpdateOrder'],
            self::CANCELLED => ['updateStatus', 'addHistory', 'notifyUpdateOrder'],
        ],
        self::PAID => [
            self::CANCELLED => ['updateStatus', 'addHistory', 'notifyUpdateOrder'],
            self::SHIPPED   => ['updateStatus', 'addHistory', 'addShipment', 'notifyUpdateOrder'],
            self::COMPLETED => ['updateStatus', 'addHistory', 'notifyUpdateOrder'],
        ],
        self::SHIPPED => [
            self::COMPLETED => ['updateStatus', 'addHistory', 'notifyUpdateOrder'],
        ],
    ];

    /**
     * @param  Order  $order
     */
    public function __construct(Order $order)
    {
        $this->order   = $order;
        $this->orderId = $order->id;
    }

    /**
     * @param  $order
     * @return self
     */
    public static function getInstance($order): self
    {
        return new self($order);
    }

    /**
     * Set order comment.
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
     * Set order notify or not.
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
     * Set order shipment.
     *
     * @param  array  $shipment
     * @return $this
     */
    public function setShipment(array $shipment = []): self
    {
        $this->shipment = $shipment;

        return $this;
    }

    /**
     * Set order payment.
     *
     * @param  array  $payment
     * @return $this
     */
    public function setPayment(array $payment = []): self
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * Get all order statuses.
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
                'name'   => panel_trans("order.$status"),
            ];
        }

        return fire_hook_filter('service.state_machine.all_statuses', $result);
    }

    /**
     * Get all valid statuses, from paid to complete.
     *
     * @return string[]
     */
    public static function getValidStatuses(): array
    {
        return [
            self::PAID,
            self::SHIPPED,
            self::COMPLETED,
        ];
    }

    /**
     * Retrieve the possible states that the current order can transition to.
     *
     * @return array
     * @throws Exception
     */
    public function nextBackendStatuses(): array
    {
        $machines = $this->getMachines();

        $currentStatusCode = $this->order->status;
        $nextStatus        = $machines[$currentStatusCode] ?? [];

        if (empty($nextStatus)) {
            return [];
        }
        $nextStatusCodes = array_keys($nextStatus);
        $result          = [];
        foreach ($nextStatusCodes as $status) {
            $result[] = [
                'status' => $status,
                'name'   => trans("panel/order.{$status}"),
            ];
        }

        return $result;
    }

    /**
     * External method invocation to modify the order status and process others.
     *
     * @param  $status
     * @param  string|null  $comment
     * @param  bool  $notify
     * @throws Exception
     */
    public function changeStatus($status, ?string $comment = '', bool $notify = false): void
    {
        $order         = $this->order;
        $oldStatusCode = $order->status;
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
            $data = ['order' => $order, 'status' => $status, 'comment' => $comment, 'notify' => $notify];
            fire_hook_action('service.state_machine.change_status.after', $data);

            if (! $order->shipping_method_code && $status == self::PAID) {
                $this->changeStatus(self::COMPLETED, $comment, $notify);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if the current order can be transitioned to a specific status.
     *
     * @param  $statusCode
     * @throws Exception
     */
    private function validStatusCode($statusCode): void
    {
        $orderId           = $this->orderId;
        $orderNumber       = $this->order->number;
        $currentStatusCode = $this->order->status;

        $nextStatusCodes = collect($this->nextBackendStatuses())->pluck('status')->toArray();
        if (! in_array($statusCode, $nextStatusCodes)) {
            throw new Exception("Order {$orderId}({$orderNumber}) is {$currentStatusCode}, cannot be changed to $statusCode");
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
            'order'    => $this->order,
            'machines' => self::MACHINES,
        ];

        $data = fire_hook_filter('service.state_machine.machines', $data);

        return $data['machines'] ?? [];
    }

    /**
     * Retrieve the events that need to be triggered based on the current order status,
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
     * Update order status.
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     * @throws Throwable
     */
    private function updateStatus($oldCode, $newCode): void
    {
        $this->order->status = $newCode;
        $this->order->saveOrFail();
    }

    /**
     * Update the sales volume of the order products.
     *
     * @return void
     */
    private function updateSales(): void
    {
        $this->order->loadMissing([
            'items',
        ]);
        $orderItems = $this->order->items;
        foreach ($orderItems as $orderItem) {
            Product::query()->where('id', $orderItem->product_id)
                ->increment('sales', $orderItem->quantity);
        }
    }

    /**
     * Add an order modification record.
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     * @throws Throwable
     */
    private function addHistory($oldCode, $newCode): void
    {
        $history = new Order\History([
            'order_id' => $this->orderId,
            'status'   => $newCode,
            'notify'   => (int) $this->notify,
            'comment'  => (string) $this->comment,
        ]);
        $history->saveOrFail();
    }

    /**
     * Deduct the inventory of the corresponding products for the order.
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     */
    private function subStock($oldCode, $newCode): void
    {
        $this->order->loadMissing([
            'items.productSku',
        ]);
        $orderItems = $this->order->items;
        foreach ($orderItems as $orderItem) {
            $productSku = $orderItem->productSku;
            if (empty($productSku)) {
                continue;
            }
            $productSku->decrement('quantity', $orderItem->quantity);
        }
    }

    /**
     * Add logistics information to the order.
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     * @throws Throwable
     */
    private function addShipment($oldCode, $newCode): void
    {
        $shipment       = $this->shipment;
        $expressCode    = $shipment['express_code']    ?? '';
        $expressCompany = $shipment['express_company'] ?? '';
        $expressNumber  = $shipment['express_number']  ?? '';
        if ($expressCode && $expressCompany && $expressNumber) {
            $orderShipment = new Order\Shipment([
                'order_id'        => $this->orderId,
                'express_code'    => $expressCode,
                'express_company' => $expressCompany,
                'express_number'  => $expressNumber,
            ]);
            $orderShipment->saveOrFail();
        }
    }

    /**
     * Add payment information to the order.
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     * @throws Throwable
     */
    private function addPayment($oldCode, $newCode): void
    {
        if (empty($this->payment)) {
            return;
        }
        PaymentRepo::getInstance()->createOrUpdatePayment($this->orderId, $this->payment);
    }

    /**
     * Send a new order notification.
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
        $this->order->notifyNewOrder();
    }

    /**
     * Send an order status update notification.
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
        $this->order->notifyUpdateOrder($oldCode);
    }
}
