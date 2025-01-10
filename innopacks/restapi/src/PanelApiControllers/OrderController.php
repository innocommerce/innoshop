<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\PanelApiControllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Resources\OrderSimple;

class OrderController extends BaseController
{
    /**
     * @param  Order  $order
     * @param  Request  $request
     * @return JsonResponse
     */
    public function updateNote(Order $order, Request $request): JsonResponse
    {
        try {
            $adminNote = $request->get('admin_note');
            $order->update([
                'admin_note' => $adminNote,
            ]);

            return update_json_success(new OrderSimple($order));
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }

    }
}
