<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories\Order;

use InnoShop\Common\Models\Order\Payment;
use InnoShop\Common\Repositories\BaseRepo;

class PaymentRepo extends BaseRepo
{
    /**
     * @param  $orderId
     * @param  $data
     * @return mixed
     * @throws \Throwable
     */
    public function createOrUpdatePayment($orderId, $data): mixed
    {
        $orderId = (int) $orderId;
        if (empty($orderId) || empty($data)) {
            return null;
        }

        $orderPayment = Payment::query()->where('order_id', $orderId)->first();
        if (empty($orderPayment)) {
            $orderPayment = new Payment;
        }

        $paymentData = [
            'order_id'     => $orderId,
            'charge_id'    => $data['charge_id'] ?? '',
            'amount'       => (float) ($data['amount'] ?? 0),
            'handling_fee' => (float) ($data['handling_fee'] ?? 0),
            'paid'         => $data['paid'] ?? false,
            'reference'    => json_encode($data['reference'] ?? ''),
        ];

        $orderPayment->fill($paymentData);
        $orderPayment->saveOrFail();

        return $orderPayment;
    }
}
