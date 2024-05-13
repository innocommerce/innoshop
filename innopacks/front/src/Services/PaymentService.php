<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Services;

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
     * @throws \Exception
     */
    public function pay(): mixed
    {
        $paymentCode = Str::studly($this->billingMethodCode);
        $viewPath    = "$paymentCode::payment";

        if (! view()->exists($viewPath)) {
            throw new \Exception("找不到支付方式 {$paymentCode} 模板 {$viewPath}");
        }

        $paymentData = [
            'order'           => $this->order,
            'payment_setting' => plugin_setting($paymentCode),
        ];

        $paymentData = fire_hook_filter('service.payment.pay.data', $paymentData);
        $paymentView = view($viewPath, $paymentData)->render();

        return view('orders.pay', ['order' => $this->order, 'payment_view' => $paymentView]);
    }
}
