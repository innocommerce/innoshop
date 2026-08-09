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
use InnoShop\Common\Repositories\OrderRepo;
use InnoShop\Common\Services\StateMachineService;
use InvalidArgumentException;

class ShipmentCreateTool extends BaseTool
{
    protected bool $write = true;

    public function name(): string
    {
        return 'shipment_create';
    }

    public function description(): string
    {
        return 'Create a shipment for an order with express carrier info. If the order is in paid status, it will be transitioned to shipped.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'order_number'    => ['type' => 'string', 'description' => 'Order number to ship'],
                'express_code'    => ['type' => 'string', 'description' => 'Express carrier code, e.g. SF, YTO, ZTO'],
                'express_company' => ['type' => 'string', 'description' => 'Express carrier display name, e.g. 顺丰速运'],
                'express_number'  => ['type' => 'string', 'description' => 'Express tracking number'],
                'notify'          => ['type' => 'boolean', 'description' => 'Whether to notify the customer, default true'],
            ],
            'required' => ['order_number', 'express_code', 'express_company', 'express_number'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'shipments_create';
    }

    public function execute(array $arguments): mixed
    {
        $orderNumber    = (string) ($arguments['order_number'] ?? '');
        $expressCode    = (string) ($arguments['express_code'] ?? '');
        $expressCompany = (string) ($arguments['express_company'] ?? '');
        $expressNumber  = (string) ($arguments['express_number'] ?? '');
        $notify         = array_key_exists('notify', $arguments) ? (bool) $arguments['notify'] : true;

        $order = OrderRepo::getInstance()->getOrderByNumber($orderNumber);
        if (! $order) {
            throw new InvalidArgumentException("Order [{$orderNumber}] not found.");
        }

        if ($order->status === 'cancelled') {
            throw new InvalidArgumentException('Cannot ship a cancelled order.');
        }

        $machine = StateMachineService::getInstance($order);

        if ($order->status === 'paid') {
            // Let the state machine create the shipment via its addShipment hook.
            $machine->setShipment([
                'express_code'    => $expressCode,
                'express_company' => $expressCompany,
                'express_number'  => $expressNumber,
            ]);
            $machine->changeStatus(StateMachineService::SHIPPED, "Shipment created: {$expressCompany} {$expressNumber}", $notify);
        } else {
            // Order already shipped/completed — create shipment record directly.
            $shipment = new Shipment([
                'order_id'        => $order->id,
                'express_code'    => $expressCode,
                'express_company' => $expressCompany,
                'express_number'  => $expressNumber,
            ]);
            $shipment->saveOrFail();
        }

        // Fetch the created shipment.
        $shipment = Shipment::query()
            ->where('order_id', $order->id)
            ->where('express_number', $expressNumber)
            ->latest()
            ->first();

        return [
            'shipment_id'     => $shipment?->id,
            'order_number'    => $order->number,
            'express_company' => $expressCompany,
            'express_number'  => $expressNumber,
            'order_status'    => $order->fresh()->status,
        ];
    }
}
