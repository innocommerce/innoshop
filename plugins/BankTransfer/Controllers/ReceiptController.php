<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\BankTransfer\Controllers;

use Exception;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Models\Order\Payment;
use InnoShop\Common\Repositories\OrderRepo;
use InnoShop\Common\Services\FileSecurityValidator;
use InnoShop\RestAPI\FrontApiControllers\BaseController;

class ReceiptController extends BaseController
{
    /**
     * Upload payment receipt for bank transfer order
     *
     * @param  Request  $request
     * @param  string  $number
     * @return mixed
     */
    public function upload(Request $request, string $number): mixed
    {
        try {
            $order = OrderRepo::getInstance()->builder(['number' => $number])->first();
            if (! $order) {
                return json_fail(__('front/order.not_found'), null, 404);
            }

            // Verify order uses bank transfer payment method
            if ($order->billing_method_code != 'bank_transfer') {
                return json_fail(__('front/order.invalid_payment_method'), null, 400);
            }

            $request->validate([
                'receipt' => 'required|image|max:5120', // Max 5MB
            ]);

            $file = $request->file('receipt');

            // Security validation
            FileSecurityValidator::validateFile($file->getClientOriginalName());

            // Store file
            $filePath = $file->store('payment_receipts', 'upload');
            $realPath = "static/uploads/$filePath";

            // Update or create order payment certificate
            $payment = $order->payments()->first();
            if ($payment) {
                $payment->certificate = $realPath;
                $payment->save();
            } else {
                // Create new payment record if it doesn't exist
                $payment = new Payment([
                    'order_id'     => $order->id,
                    'charge_id'    => $order->number,
                    'amount'       => $order->total,
                    'handling_fee' => 0,
                    'paid'         => false,
                    'reference'    => [],
                    'certificate'  => $realPath,
                ]);
                $payment->saveOrFail();
            }

            return json_success(__('BankTransfer::common.receipt_uploaded'), [
                'url' => asset($realPath),
            ]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
