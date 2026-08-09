<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\OrderReturn;
use InvalidArgumentException;

class OrderReturnDetailTool extends BaseTool
{
    public function name(): string
    {
        return 'order_return_detail';
    }

    public function description(): string
    {
        return 'Get full details of a single order return (RMA) by ID, including items and history.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'id' => ['type' => 'integer', 'description' => 'Order return ID'],
            ],
            'required' => ['id'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'order_returns_index';
    }

    public function execute(array $arguments): mixed
    {
        $rma = OrderReturn::query()->with(['order', 'histories'])->find((int) ($arguments['id'] ?? 0));
        if (! $rma) {
            throw new InvalidArgumentException("Order return [{$arguments['id']}] not found.");
        }

        return [
            'id'            => $rma->id,
            'return_number' => $rma->return_number ?? $rma->number ?? '',
            'order_number'  => $rma->order->number ?? '',
            'customer_name' => $rma->customer_name,
            'email'         => $rma->email ?? $rma->customer_email ?? '',
            'status'        => $rma->status,
            'total'         => $rma->total,
            'reason'        => $rma->reason ?? '',
            'comment'       => $rma->comment ?? '',
            'histories'     => $rma->histories->map(fn ($h) => [
                'status'     => $h->status,
                'comment'    => $h->comment,
                'created_at' => (string) $h->created_at,
            ])->values()->all(),
            'created_at' => (string) $rma->created_at,
            'updated_at' => (string) $rma->updated_at,
        ];
    }
}
