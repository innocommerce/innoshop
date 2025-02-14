<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     XING GUI YU <xingguiyu@foxmail.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Coupon\Services;

use InnoShop\Common\Services\Fee\BaseService;
use Plugin\Coupon\Repositories\CouponRepo;
use Throwable;

class CouponFee extends BaseService
{
    /**
     * 添加优惠券费用
     * @throws Throwable
     */
    public function addFee(): void
    {
        $couponRepo    = new CouponRepo;
        $couponService = new CouponService($couponRepo);
        $couponCode    = $couponService->getCouponCode(); // 从session里获取优惠券代码
        //        $couponService->forgetCouponCodeInSession();
        //        $couponCode = 'WDSHGGFJXOBJKWSL';
        if (! $couponCode) {
            return; // 如果没有优惠券代码，则不进行处理
        }

        $coupon = CouponRepo::getInstance()->findByCode($couponCode);

        if (! $coupon || ! $coupon->isValid()) {
            return; // 如果优惠券无效或不存在，则不添加费用
        }

        $discountValue = $this->calculateDiscount($coupon); // 计算优惠额

        if ($discountValue <= 0) {
            return; // 如果优惠额为零或负数，则不添加费用
        }

        $couponFee = [
            'code'         => 'coupon',
            'title'        => 'Coupon Discount - '.$coupon->code,
            'total'        => -$discountValue, // 应为负值表示折扣
            'reference'    => $coupon->code,
            'total_format' => currency_format(-$discountValue),
        ];

        $this->checkoutService->addFeeList($couponFee);
    }

    /**
     * 根据优惠券类型计算折扣额
     * @param  $coupon
     * @return float
     * @throws \Exception
     */
    private function calculateDiscount($coupon): float
    {

        $totalAmount = $this->checkoutService->getTotal(); // 获取订单总额

        switch ($coupon->type) {
            case 'percentage':
                $percentage = min($coupon->value, 100);
                return $totalAmount * ($percentage / 100); //折扣禁止大于100
            case 'fixed':
                return min($coupon->value, $totalAmount);
        }

        return 0.0;
    }
}
