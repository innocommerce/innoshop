<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://github.com/qxsclass
 * @author     XING GUI YU <xingguiyu@foxmail.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Coupon;

use Plugin\Coupon\Repositories\CouponRepo;
use Plugin\Coupon\Services\CouponFee;
use Plugin\Coupon\Services\CouponService;

class Boot
{
    private $coupon;

    private $orderId;

    private $couponCode;

    private $repo;

    private $user;

    public function __construct()
    {
        $this->repo = new CouponRepo;
    }

    public function init(): void
    {
        listen_hook_filter('component.sidebar.plugin.routes', function ($data) {
            $data[] = [
                'route' => 'coupons.index',
                'title' => __('Coupon::common.coupon'),
            ];

            return $data;
        });

        //        listen_hook_action('checkout.confirm.after',function ($data) {
        //            dd($data);
        //        });
        listen_hook_filter('service.checkout.fee.methods', function ($classes) {
            $classes[] = CouponFee::class;

            return $classes;
        });
        listen_blade_insert('checkout.confirm.before', function ($data) {
            //             dd($data);
            $this->user = $data['customer'];

            $code                = session()->get('coupon_code');
            $data['coupon_code'] = $code;

            return view('Coupon::front.coupon', $data);
        });

        listen_hook_filter('service.state_machine.machines', function ($data) {
            $order         = $data['order'];
            $this->orderId = $order->id;
            foreach ($order->fees as $fee) {
                if ($fee->code == 'coupon') {
                    $this->couponCode = $fee->reference;
                }
            }
            $this->user = $order->customer;
            //            dd($data);
            $data['machines']['created']['unpaid'][] = function () {
                $couponService = new CouponService($this->repo);
                $couponService->redeemCoupon($this->couponCode, $this->orderId, $this->user->id);
            };

            //            session()->forget('coupon_code');
            return $data;
        });

    }
}
