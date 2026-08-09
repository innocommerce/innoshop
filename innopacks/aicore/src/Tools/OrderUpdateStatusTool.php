<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Repositories\OrderRepo;
use InnoShop\Common\Services\StateMachineService;
use InvalidArgumentException;

class OrderUpdateStatusTool extends BaseTool
{
    protected bool $write = true;

    public function name(): string
    {
        return 'order_update_status';
    }

    public function description(): string
    {
        return 'Change the status of an order. Valid statuses: unpaid, paid, shipped, completed, cancelled. The status transition must be valid per the order state machine.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'number' => ['type' => 'string', 'description' => 'Order number'],
                'status' => [
                    'type'        => 'string',
                    'enum'        => ['paid', 'shipped', 'completed', 'cancelled'],
                    'description' => 'Target status: paid, shipped, completed, or cancelled',
                ],
                'comment' => ['type' => 'string', 'description' => 'Optional comment for the status change'],
                'notify'  => ['type' => 'boolean', 'description' => 'Whether to notify the customer, default false'],
            ],
            'required' => ['number', 'status'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'orders_update_status';
    }

    public function execute(array $arguments): mixed
    {
        $number  = (string) ($arguments['number'] ?? '');
        $status  = (string) ($arguments['status'] ?? '');
        $comment = (string) ($arguments['comment'] ?? '');
        $notify  = (bool) ($arguments['notify'] ?? false);

        $order = OrderRepo::getInstance()->getOrderByNumber($number);
        if (! $order) {
            throw new InvalidArgumentException("Order [{$number}] not found.");
        }

        if (! in_array($status, StateMachineService::ORDER_STATUS, true)) {
            throw new InvalidArgumentException("Invalid status [{$status}]. Valid: ".implode(', ', StateMachineService::ORDER_STATUS));
        }

        $machine = StateMachineService::getInstance($order);
        $machine->changeStatus($status, $comment, $notify);

        $order->refresh();

        return [
            'number'       => $order->number,
            'status'       => $order->status,
            'status_label' => $order->status_format,
            'comment'      => $comment,
            'notified'     => $notify,
        ];
    }
}
