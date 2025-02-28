<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InquiryQuote\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Repositories\Order\FeeRepo;
use InnoShop\Common\Repositories\Order\HistoryRepo;
use InnoShop\Common\Repositories\Order\ItemRepo;
use InnoShop\Common\Repositories\OrderRepo;
use InnoShop\Common\Services\StateMachineService;
use Plugin\InquiryQuote\Models\InquiryQuote;
use Throwable;

class PlaceOrderService
{
    private InquiryQuote $quote;

    /**
     * @param  InquiryQuote  $quote
     */
    public function __construct(InquiryQuote $quote)
    {
        $this->quote = $quote;
    }

    /**
     * @param  InquiryQuote  $quote
     * @return self
     */
    public static function getInstance(InquiryQuote $quote): self
    {
        return new self($quote);
    }

    /**
     * @return void
     * @throws Throwable
     */
    /**
     * Confirm checkout and place order.
     *
     * @return mixed
     * @throws Exception|Throwable
     */
    public function confirm(): mixed
    {
        $order = $this->quote->order;
        if ($order) {
            throw new Exception("该询价单已经生成过订单 - {$order->number}");
        }

        DB::beginTransaction();

        try {
            $checkoutData = $this->getCheckoutData();

            $order = OrderRepo::getInstance()->create($checkoutData);

            ItemRepo::getInstance()->createItems($order, $this->getCartList());
            FeeRepo::getInstance()->createItems($order, $this->getFeeList());
            HistoryRepo::getInstance()->initItem($order);

            StateMachineService::getInstance($order)->changeStatus(StateMachineService::UNPAID);

            $this->quote->order_id = $order->id;
            $this->quote->save();
            StateService::getInstance($this->quote)->changeStatus(StateService::COMPLETED);

            DB::commit();

            return $order;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getCheckoutData(): array
    {
        $quote = $this->quote;

        $parseShipping  = explode('.', $quote->shipping_method_code);
        $shippingPlugin = plugin($parseShipping[0]);

        $billingCode   = 'l_offline';
        $billingPlugin = plugin($billingCode);

        return [
            'id'                   => $quote->id,
            'customer_id'          => $quote->customer_id,
            'guest_id'             => 0,
            'shipping_address_id'  => $quote->shipping_address_id,
            'shipping_method_code' => $quote->shipping_method_code,
            'shipping_method_name' => $shippingPlugin ? $shippingPlugin->getLocaleName() : '',
            'billing_address_id'   => $quote->billing_address_id ?: $quote->shipping_address_id,
            'billing_method_code'  => $billingCode,
            'billing_method_name'  => $billingPlugin ? $billingPlugin->getLocaleName() : '',
            'reference'            => '',
            'comment'              => $quote->comment,
            'total'                => $quote->total,
        ];
    }

    /**
     * @return array
     */
    private function getCartList(): array
    {
        $cartList = [];
        foreach ($this->quote->items as $item) {
            $product    = $item->product;
            $cartList[] = [
                'sku_code'     => $item['sku_code'],
                'product_name' => $product->translation->name,
                'image'        => $product->image->path,
                'quantity'     => $item['quantity'],
                'price'        => $item['inquiry_price'],
            ];
        }

        return $cartList;
    }

    /**
     * @return array
     */
    private function getFeeList(): array
    {
        $feeList = [];
        foreach ($this->quote->fees as $item) {
            $feeList[] = [
                'code'      => $item['code'],
                'total'     => $item['inquiry_amount'],
                'title'     => $item['label'],
                'reference' => $item['reference'] ?? '',
            ];
        }

        return $feeList;
    }
}
