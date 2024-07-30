<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Services;

use Exception;
use Illuminate\Support\Str;
use InnoShop\Common\Models\Order;

class PaymentService
{
    protected ?Order $order;

    protected string $billingMethodCode;

    /**
     * @param  Order  $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;

        $this->billingMethodCode = $order->billing_method_code;
    }

    /**
     * @param  Order  $order
     * @return static
     */
    public static function getInstance(Order $order): static
    {
        return new static($order);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function pay(): mixed
    {
        try {
            if ($this->order->status != 'unpaid') {
                throw new Exception("Order status must be unpaid, now is {$this->order->status}!");
            }
            $originCode  = $this->billingMethodCode;
            $paymentCode = Str::studly($originCode);
            $viewPath    = fire_hook_filter("service.payment.pay.$originCode.view", "$paymentCode::payment");

            if (! view()->exists($viewPath)) {
                throw new Exception("Cannot find {$paymentCode} view {$viewPath}");
            }

            $paymentData = [
                'order'           => $this->order,
                'payment_setting' => plugin_setting($paymentCode),
            ];

            $paymentData = fire_hook_filter("service.payment.pay.$originCode.data", $paymentData);
            $viewContent = view($viewPath, $paymentData)->render();

            return view('orders.pay', ['order' => $this->order, 'payment_view' => $viewContent]);
        } catch (Exception $e) {
            return view('orders.pay', ['order' => $this->order, 'error' => $e->getMessage()]);
        }
    }
}
