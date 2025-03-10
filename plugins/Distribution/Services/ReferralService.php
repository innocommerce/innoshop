<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Distribution\Services;

use Illuminate\Support\Str;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Repositories\Customer\TransactionRepo;
use InnoShop\Common\Services\BaseService;
use Plugin\Distribution\Models\Commission;
use Plugin\Distribution\Repositories\CommissionRepo;
use Throwable;

class ReferralService extends BaseService
{
    /**
     * @param  $customer
     * @return string
     */
    public function getCustomerRefCode($customer): string
    {
        if (empty($customer->referral_code)) {
            $referralCode = self::generateReferralCode();

            $customer->referral_code = $referralCode;
            $customer->saveOrFail();
        }

        return $customer->referral_code;
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function saveReferralCode(): void
    {
        $customers = Customer::query()->where('referral_code', '')->get();
        foreach ($customers as $customer) {
            $customer->referral_code = self::generateReferralCode();
            $customer->saveOrFail();
        }
    }

    /**
     * @param  $order
     * @return void
     * @throws Throwable
     */
    public function updateReferrer($order): void
    {
        $referrerID = $order->customer->referrer_id ?? 0;
        if (empty($referrerID)) {
            return;
        }
        $referrerCustomer = Customer::query()->find($referrerID);
        if (empty($referrerCustomer)) {
            return;
        }

        $order->referrer_id = $referrerID;
        $order->save();

        CommissionRepo::getInstance()->createCommission($order);
    }

    /**
     * @param  $referralID
     * @return array
     */
    public static function getReport($referralID): array
    {
        $memberTotal      = Customer::query()->where('referrer_id', $referralID)->count();
        $commissionAmount = CommissionRepo::getInstance()->getCommissionTotalByReferral($referralID);
        $orderAmount      = CommissionRepo::getInstance()->getAmountTotalByReferral($referralID);
        $orderTotal       = CommissionRepo::getInstance()->getOrderTotalByReferral($referralID);

        return [
            'member_total'      => $memberTotal,
            'commission_amount' => currency_format($commissionAmount),
            'order_total'       => $orderTotal,
            'order_amount'      => currency_format($orderAmount),
        ];
    }

    /**
     * @return string
     */
    public static function generateReferralCode(): string
    {
        $code = strtolower(Str::random(6));
        if (! Customer::query()->where(['referral_code' => $code])->exists()) {
            return $code;
        }

        return self::generateReferralCode();
    }

    /**
     * @param  $order
     * @return void
     * @throws Throwable
     */
    public static function completeOrder($order): void
    {
        $filters = [
            'order_id'    => $order->id,
            'customer_id' => $order->customer_id,
            'referrer_id' => $order->referrer_id,
        ];
        $commission = CommissionRepo::getInstance()->builder($filters)->first();
        if ($commission) {
            $commission->status = Commission::PAID;
            $commission->save();

            $transaction = [
                'customer_id' => $order->referrer_id,
                'amount'      => $commission->commission_amount,
                'type'        => Customer\Transaction::TYPE_COMMISSION,
                'comment'     => "Order($order->number), Referrer($order->referrer_id)",
            ];
            TransactionRepo::getInstance()->create($transaction);
        }
    }
}
