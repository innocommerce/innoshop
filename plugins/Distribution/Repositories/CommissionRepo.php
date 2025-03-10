<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Distribution\Repositories;

use Illuminate\Database\Eloquent\Builder;
use InnoShop\Common\Repositories\BaseRepo;
use Plugin\Coupon\Models\Order;
use Plugin\Distribution\Models\Commission;
use Throwable;

class CommissionRepo extends BaseRepo
{
    protected string $model = Commission::class;

    /**
     * @param  $order
     * @return void
     * @throws Throwable
     */
    public function createCommission($order): void
    {
        $filters = [
            'order_id'    => $order->id,
            'customer_id' => $order->customer_id,
            'referrer_id' => $order->referrer_id,
        ];
        $commission = CommissionRepo::getInstance()->builder($filters)->first();
        if ($commission) {
            return;
        }

        $rate = $this->getCommissionRate();
        if (empty($rate)) {
            return;
        }

        $data = [
            'order_id'          => $order->id,
            'customer_id'       => $order->customer_id,
            'referrer_id'       => $order->referrer_id,
            'commission_amount' => round($order->total * $rate, 2),
            'status'            => 'pending',
        ];
        $commission = new Commission($data);

        $commission->saveOrFail();
    }

    /**
     * @return float
     */
    private function getCommissionRate(): float
    {
        return (float) (plugin_setting('distribution', 'rate', 0) / 100);
    }

    public function builder($filters = []): Builder
    {
        $builder = Commission::query();

        $orderID = $filters['order_id'] ?? 0;
        if ($orderID) {
            $builder->where('order_id', $orderID);
        }

        $customerID = $filters['customer_id'] ?? 0;
        if ($customerID) {
            $builder->where('customer_id', $customerID);
        }

        $referrerID = $filters['referrer_id'] ?? 0;
        if ($referrerID) {
            $builder->where('referrer_id', $referrerID);
        }

        return $builder;
    }

    /**
     * @param  $referralID
     * @return float
     */
    public function getCommissionTotalByReferral($referralID): float
    {
        $commission = Commission::query()->where('referrer_id', $referralID)->sum('commission_amount');

        return round($commission, 2);
    }

    /**
     * @param  $referralID
     * @return int
     */
    public function getOrderTotalByReferral($referralID): int
    {
        return Order::query()->where('referrer_id', $referralID)->count();
    }

    /**
     * @param  $customerID
     * @return float
     */
    public function getAmountTotalByReferral($customerID): float
    {
        $orderAmountTotal = Order::query()->where('referrer_id', $customerID)->sum('total');

        return round($orderAmountTotal, 2);
    }
}
