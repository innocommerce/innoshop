<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\PanelApiControllers;

use Exception;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Services\ShippingTraceService;
use InnoShop\RestAPI\Requests\ShipmentRequest;

class ShipmentController extends BaseController
{
    /**
     * @param  Order  $order
     * @param  ShipmentRequest  $request
     * @return mixed
     */
    public function store(Order $order, ShipmentRequest $request): mixed
    {
        try {
            $shipment = $order->shipments()->create($request->all());

            return create_json_success($shipment);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Order\Shipment  $shipment
     * @return mixed
     */
    public function destroy(Order\Shipment $shipment): mixed
    {
        try {
            $shipment->delete();

            return delete_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Order\Shipment  $shipment
     * @return mixed
     */
    public function getTraces(Order\Shipment $shipment): mixed
    {
        try {
            $traces = ShippingTraceService::getInstance($shipment)->getTraces();

            return read_json_success($traces);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
