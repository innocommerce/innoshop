<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\PanelApiControllers;

use Illuminate\Http\Request;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Resources\OrderSimple;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Panel - Orders')]
class OrderController extends BaseController
{
    /**
     * @param  Order  $order
     * @param  Request  $request
     * @return mixed
     */
    #[Endpoint('Update order admin note')]
    #[UrlParam('order', type: 'integer', description: 'Order ID')]
    #[BodyParam('admin_note', type: 'string', required: true, description: 'Admin note for the order')]
    public function updateNote(Order $order, Request $request): mixed
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
