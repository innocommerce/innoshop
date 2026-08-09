<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use Illuminate\Support\Facades\Log;
use InnoShop\Common\Models\Order\Shipment;
use InvalidArgumentException;

class ShipmentTracesTool extends BaseTool
{
    public function name(): string
    {
        return 'shipment_traces';
    }

    public function description(): string
    {
        return 'Query express tracking traces for a shipment. Calls the registered shipping plugin to fetch latest logistics status from the carrier.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'shipment_id'    => ['type' => 'integer', 'description' => 'Shipment ID to query traces for'],
                'express_code'   => ['type' => 'string', 'description' => 'Express carrier code (e.g. SF, YTO), uses shipment record if omitted'],
                'express_number' => ['type' => 'string', 'description' => 'Express tracking number, uses shipment record if omitted'],
            ],
            'required' => ['shipment_id'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'orders_index';
    }

    public function execute(array $arguments): mixed
    {
        $shipmentId = (int) ($arguments['shipment_id'] ?? 0);
        $shipment   = Shipment::query()->with('order')->find($shipmentId);
        if (! $shipment) {
            throw new InvalidArgumentException("Shipment [{$shipmentId}] not found.");
        }

        $expressCode   = $arguments['express_code'] ?? $shipment->express_code;
        $expressNumber = $arguments['express_number'] ?? $shipment->express_number;

        if (empty($expressCode) || empty($expressNumber)) {
            return [
                'shipment_id'     => $shipment->id,
                'order_number'    => $shipment->order->number ?? '',
                'express_company' => $shipment->express_company,
                'express_number'  => $expressNumber,
                'traces'          => [],
                'note'            => 'No express code or number available for tracking.',
            ];
        }

        $traces = $this->fetchTraces($expressCode, $expressNumber);

        return [
            'shipment_id'     => $shipment->id,
            'order_number'    => $shipment->order->number ?? '',
            'express_company' => $shipment->express_company,
            'express_code'    => $expressCode,
            'express_number'  => $expressNumber,
            'traces'          => $traces,
        ];
    }

    /**
     * Attempt to fetch traces via the shipping plugin hook.
     * Falls back gracefully when no plugin is registered.
     */
    private function fetchTraces(string $code, string $number): array
    {
        try {
            $result = fire_hook_filter('service.shipment.traces', [
                'code'   => $code,
                'number' => $number,
            ]);
            if (is_array($result)) {
                return $result;
            }
        } catch (\Throwable $e) {
            Log::warning("ShipmentTracesTool: failed to fetch traces for {$code}/{$number}: ".$e->getMessage());
        }

        return [];
    }
}
