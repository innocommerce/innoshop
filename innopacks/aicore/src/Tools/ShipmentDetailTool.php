<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\Order\Shipment;
use InvalidArgumentException;

class ShipmentDetailTool extends BaseTool
{
    public function name(): string
    {
        return 'shipment_detail';
    }

    public function description(): string
    {
        return 'Get full details of a single shipment by ID, including order info and express tracking number.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'id' => ['type' => 'integer', 'description' => 'Shipment ID'],
            ],
            'required' => ['id'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'orders_index';
    }

    public function execute(array $arguments): mixed
    {
        $shipment = Shipment::query()->with('order')->find((int) ($arguments['id'] ?? 0));
        if (! $shipment) {
            throw new InvalidArgumentException("Shipment [{$arguments['id']}] not found.");
        }

        $order = $shipment->order;

        return [
            'id'               => $shipment->id,
            'order_id'         => $shipment->order_id,
            'order_number'     => $order->number ?? '',
            'customer_name'    => $order->shipping_customer_name ?? '',
            'express_code'     => $shipment->express_code,
            'express_company'  => $shipment->express_company,
            'express_number'   => $shipment->express_number,
            'shipping_address' => trim(implode(' ', array_filter([
                $order->shipping_address_1 ?? '',
                $order->shipping_city ?? '',
                $order->shipping_state ?? '',
                $order->shipping_country ?? '',
            ]))),
            'order_status' => $order->status ?? '',
            'created_at'   => (string) $shipment->created_at,
            'updated_at'   => (string) $shipment->updated_at,
        ];
    }
}
