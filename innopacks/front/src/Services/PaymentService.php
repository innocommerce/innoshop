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

    /**
     * Pay for API.
     * @return array
     * @throws Exception
     */
    public function apiPay(): array
    {
        $order = $this->order;

        $orderPaymentCode = $this->billingMethodCode;
        $paymentData      = [
            'order'           => $order,
            'payment_setting' => plugin_setting($orderPaymentCode),
            'params'          => null,
        ];

        $hookName    = "service.payment.api.$orderPaymentCode.data";
        $paymentData = fire_hook_filter($hookName, $paymentData);

        $paramError = $paymentData['error'] ?? '';
        if ($paramError) {
            throw new Exception($paramError);
        }

        $params = $paymentData['params'] ?? [];
        if (empty($params)) {
            throw new Exception("Empty payment params for {$orderPaymentCode}, please add filter hook: $hookName");
        }

        return [
            'order_id'            => $order->id,
            'order_number'        => $order->number,
            'billing_method_code' => $order->billing_method_code,
            'billing_method_name' => $order->billing_method_name,
            'billing_params'      => $params,
        ];
    }
}
