<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\Order\History;
use InnoShop\Common\Repositories\OrderRepo;
use InvalidArgumentException;

class OrderNoteTool extends BaseTool
{
    protected bool $write = true;

    public function name(): string
    {
        return 'order_note';
    }

    public function description(): string
    {
        return 'Add an admin note (comment) to an order. This creates an order history record visible in the admin panel.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'number'  => ['type' => 'string', 'description' => 'Order number'],
                'comment' => ['type' => 'string', 'description' => 'Admin note content'],
                'notify'  => ['type' => 'boolean', 'description' => 'Whether to notify the customer, default false'],
            ],
            'required' => ['number', 'comment'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'orders_index';
    }

    public function execute(array $arguments): mixed
    {
        $number  = (string) ($arguments['number'] ?? '');
        $comment = (string) ($arguments['comment'] ?? '');
        $notify  = (bool) ($arguments['notify'] ?? false);

        $order = OrderRepo::getInstance()->getOrderByNumber($number);
        if (! $order) {
            throw new InvalidArgumentException("Order [{$number}] not found.");
        }

        History::query()->create([
            'order_id' => $order->id,
            'status'   => $order->status,
            'comment'  => $comment,
            'notify'   => $notify,
        ]);

        return [
            'order_number' => $order->number,
            'comment'      => $comment,
            'notified'     => $notify,
            'created_at'   => (string) now(),
        ];
    }
}
