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
use Illuminate\Http\Request;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Repositories\OrderRepo;
use InnoShop\Common\Resources\OrderSimple;
use InnoShop\Common\Services\StateMachineService;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Panel - Orders')]
class OrderController extends BaseController
{
    #[Endpoint('List orders')]
    #[QueryParam('per_page', 'integer', required: false, example: 15)]
    #[QueryParam('status', 'string', required: false)]
    #[QueryParam('number', 'string', required: false)]
    public function index(Request $request): mixed
    {
        try {
            $filters = $request->all();
            $perPage = $request->get('per_page', 15);
            $orders  = OrderRepo::getInstance()->builder($filters)->paginate($perPage);

            return OrderSimple::collection($orders);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Get order detail')]
    #[UrlParam('order', type: 'integer', description: 'Order ID')]
    public function show(Order $order): mixed
    {
        try {
            $order->load(['items', 'shipments', 'orderItems.options']);

            return read_json_success(new OrderSimple($order));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Change order status')]
    #[UrlParam('order', type: 'integer', description: 'Order ID')]
    #[BodyParam('status', type: 'string', required: true, description: 'New status code')]
    #[BodyParam('comment', type: 'string', required: false, description: 'Status change comment')]
    public function changeStatus(Request $request, Order $order): mixed
    {
        try {
            $status  = $request->get('status');
            $comment = $request->get('comment', '');
            StateMachineService::getInstance($order)->changeStatus($status, $comment);

            return update_json_success(new OrderSimple($order));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

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
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }

    }
}
