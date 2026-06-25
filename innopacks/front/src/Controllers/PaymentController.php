<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Repositories\OrderRepo;

class PaymentController extends Controller
{
    /**
     * Payment success page
     *
     * @param  Request  $request
     * @return View
     */
    public function success(Request $request)
    {
        $order = $this->resolveOrderFromRequest($request);

        return inno_view('payment.success', ['order' => $order]);
    }

    /**
     * Payment fail page
     *
     * @param  Request  $request
     * @return View
     */
    public function fail(Request $request)
    {
        $order = $this->resolveOrderFromRequest($request);

        return inno_view('payment.fail', ['order' => $order]);
    }

    /**
     * Payment cancel page
     *
     * @param  Request  $request
     * @return View
     */
    public function cancel(Request $request)
    {
        $order = $this->resolveOrderFromRequest($request);

        return inno_view('payment.cancel', ['order' => $order]);
    }

    /**
     * Resolve order from request params while enforcing authorization.
     *
     * Supports: order_number (+ optional payment_token), out_trade_no (format: {id}-{timestamp}).
     * An order is only returned when ONE of the following holds:
     *   1. The caller is the order's owning customer (logged-in owner check).
     *   2. The order number is in the session whitelist `guest_payment_order_numbers`
     *      — written by Order::created for the current browser session. This covers
     *      the standard guest checkout flow: place order → redirect to gateway → return.
     *   3. The request carries a payment_token that hash_equals the stored token —
     *      the high-entropy fallback used by cross-session links (e.g. order emails).
     * Otherwise null is returned and the view renders the generic placeholder,
     * preventing unauthenticated order enumeration (CWE-639 / OWASP A01:2025).
     */
    private function resolveOrderFromRequest(Request $request): ?Order
    {
        $order = $this->findOrderByRequestParams($request);
        if (! $order) {
            return null;
        }

        if ($this->callerOwnsOrder($order)
            || $this->inSessionWhitelist($order)
            || $this->validTokenProvided($order, $request)) {
            return $order;
        }

        return null;
    }

    private function findOrderByRequestParams(Request $request): ?Order
    {
        $orderNumber = $request->get('order_number');
        if ($orderNumber) {
            return OrderRepo::getInstance()->builder(['number' => $orderNumber])->first();
        }

        $outTradeNo = $request->get('out_trade_no');
        if ($outTradeNo && str_contains($outTradeNo, '-')) {
            $orderId = (int) explode('-', $outTradeNo)[0];
            if ($orderId) {
                return OrderRepo::getInstance()->builder(['id' => $orderId])->first();
            }
        }

        return null;
    }

    private function callerOwnsOrder(Order $order): bool
    {
        $currentCustomerId = current_customer_id();

        return $currentCustomerId > 0 && (int) $order->customer_id === $currentCustomerId;
    }

    private function inSessionWhitelist(Order $order): bool
    {
        $whitelist = (array) session()->get('guest_payment_order_numbers', []);

        return in_array($order->number, $whitelist, true);
    }

    private function validTokenProvided(Order $order, Request $request): bool
    {
        $storedToken   = (string) ($order->payment_token ?? '');
        $suppliedToken = (string) $request->get('token', '');
        if ($storedToken === '' || $suppliedToken === '') {
            return false;
        }

        return hash_equals($storedToken, $suppliedToken);
    }
}
