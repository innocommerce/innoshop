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
            $orderPayment = new Payment();
        }

        $paymentData = [
            'order_id' => $orderId,
        ];

        if (isset($data['transaction_id'])) {
            $paymentData['transaction_id'] = $data['transaction_id'];
        }
        if (isset($data['request'])) {
            $paymentData['request'] = json_encode($data['request']);
        }
        if (isset($data['response'])) {
            $paymentData['response'] = json_encode($data['response']);
        }
        if (isset($data['callback'])) {
            $paymentData['callback'] = json_encode($data['callback']);
        }
        if (isset($data['receipt'])) {
            $paymentData['receipt'] = $data['receipt'];
        }

        $orderPayment->fill($paymentData);
        $orderPayment->saveOrFail();

        return $orderPayment;
    }
}
