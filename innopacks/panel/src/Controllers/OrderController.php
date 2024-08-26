<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Repositories\OrderRepo;
use InnoShop\Common\Services\StateMachineService;

class OrderController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'orders' => OrderRepo::getInstance()->list($filters),
        ];

        return inno_view('panel::orders.index', $data);
    }

    /**
     * @param  Order  $order
     * @return mixed
     * @throws Exception
     */
    public function show(Order $order): mixed
    {
        $order->load(['items', 'fees']);

        return $this->form($order);
    }

    /**
     * @param  Order  $order
     * @return mixed
     * @throws Exception
     */
    public function edit(Order $order): mixed
    {
        $order->load(['items', 'fees']);

        return $this->form($order);
    }

    /**
     * @param  Order  $order
     * @return mixed
     * @throws Exception
     */
    public function form(Order $order): mixed
    {
        $data = [
            'order'         => $order,
            'next_statuses' => StateMachineService::getInstance($order)->nextBackendStatuses(),
        ];

        return inno_view('panel::orders.info', $data);
    }

    /**
     * @param  Order  $order
     * @return RedirectResponse
     */
    public function destroy(Order $order): RedirectResponse
    {
        try {
            OrderRepo::getInstance()->destroy($order);

            return back()->with('success', panel_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Request  $request
     * @param  Order  $order
     * @return JsonResponse
     */
    public function changeStatus(Request $request, Order $order): JsonResponse
    {
        $status  = $request->get('status');
        $comment = $request->get('comment');
        try {
            StateMachineService::getInstance($order)->changeStatus($status, $comment, true);

            return json_success(panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
