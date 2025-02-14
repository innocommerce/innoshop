<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     XING GUI YU <xingguiyu@foxmail.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Coupon\Controllers;

use Illuminate\Http\Request;
use InnoShop\Panel\Controllers\BaseController;
use Plugin\Coupon\Models\Coupon;
use Plugin\Coupon\Services\CouponService;

class CouponController extends BaseController
{
    protected CouponService $couponService;

    public function __construct(CouponService $couponService)
    {
        parent::__construct(); // 调用父类构造函数
        $this->couponService = $couponService;
    }

    /**
     * 显示优惠券列表页面。
     *
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        $status   = $request->input('status');
        $type     = $request->input('type');
        $paginate = $request->input('paginate');

        // 筛选参数
        $coupons = $this->couponService->getCouponsFiltered($status, $type, $paginate);

        return view('Coupon::panel.index', compact('coupons'));
    }

    /**
     * 显示创建优惠券的表单。
     *
     * @return mixed
     */
    public function create(): mixed
    {
        $coupon       = new Coupon;
        $coupon->code = Coupon::findAvailableCode();
        $options      = [
            ['value' => 'percentage', 'label' => __('Coupon::common.discount')],
            ['value' => 'fixed', 'label' => __('Coupon::common.fixed_amount')],
        ];

        return view('Coupon::panel.form', compact(['coupon', 'options']));
    }

    /**
     * 显示编辑优惠券的表单。
     *
     * @param  int  $id  优惠券ID
     * @return mixed
     */
    public function edit($id): mixed
    {
        $coupon  = $this->couponService->findById($id);
        $options = [
            ['value' => 'percentage', 'label' => __('Coupon::common.discount')],
            ['value' => 'fixed', 'label' => __('Coupon::common.fixed_amount')],
        ];

        if (! $coupon) {
            return redirect()->route('coupons.index')->with('error', __('Coupon::common.coupon_not_found'));
        }

        return view('Coupon::panel.form', compact(['coupon', 'options']));
    }

    /**
     * 创建优惠券
     *
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request): mixed
    {

        $this->couponService->createCoupon($request);

        return redirect(panel_route('coupons.index'))->with('success', __('Coupon::common.create_success'));
    }

    public function update(Coupon $coupon, Request $request)
    {
        $coupon = $this->couponService->update($request, $coupon->code);

        return redirect(panel_route('coupons.index'))->with('success', __('Coupon::common.update_success'));
    }

    /**
     * 应用优惠券
     *
     * @param  Request  $request
     * @return mixed
     */
    public function apply(Request $request): mixed
    {
        $code   = $request->input('code');
        $result = $this->couponService->applyCoupon($code);

        return response()->json($result);
    }

    /**
     * 删除指定的优惠券。
     *
     * @param  $id
     * @return mixed
     */
    public function destroy($id): mixed
    {
        $coupon = Coupon::findOrFail($id);  // 查找优惠券，如果找不到则抛出异常
        $coupon->delete();  // 删除优惠券

        return redirect(panel_route('coupons.index'))->with('success', __('Coupon::common.create_success'));
    }
}
