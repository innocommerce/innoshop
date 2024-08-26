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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\OrderReturn;
use InnoShop\Common\Repositories\CatalogRepo;
use InnoShop\Common\Repositories\OrderReturnRepo;
use InnoShop\Common\Resources\CatalogSimple;

class OrderReturnController extends BaseController
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
            'order_returns' => OrderReturnRepo::getInstance()->list($filters),
        ];

        return inno_view('panel::order_returns.index', $data);
    }

    /**
     * OrderReturn creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new OrderReturn);
    }

    /**
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $data        = $request->all();
            $orderReturn = OrderReturnRepo::getInstance()->create($data);

            return redirect(panel_route('order_returns.index'))
                ->with('instance', $orderReturn)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  OrderReturn  $order_return
     * @return mixed
     * @throws Exception
     */
    public function edit(OrderReturn $order_return): mixed
    {
        return $this->form($order_return);
    }

    /**
     * @param  $order_return
     * @return mixed
     * @throws Exception
     */
    public function form($order_return): mixed
    {
        $catalogs = CatalogSimple::collection(CatalogRepo::getInstance()->all(['active' => 1]))->jsonSerialize();
        $data     = [
            'order_return' => $order_return,
            'catalogs'     => $catalogs,
        ];

        return inno_view('panel::order_returns.form', $data);
    }

    /**
     * @param  Request  $request
     * @param  OrderReturn  $order_return
     * @return RedirectResponse
     */
    public function update(Request $request, OrderReturn $order_return): RedirectResponse
    {
        try {
            $data        = $request->all();
            $orderReturn = OrderReturnRepo::getInstance()->update($order_return, $data);

            return redirect(panel_route('order_returns.index'))
                ->with('instance', $orderReturn)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  OrderReturn  $order_return
     * @return RedirectResponse
     */
    public function destroy(OrderReturn $order_return): RedirectResponse
    {
        try {
            OrderReturnRepo::getInstance()->destroy($order_return);

            return back()->with('success', panel_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
