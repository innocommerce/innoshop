<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\TiktokPixel\Services;

use Exception;
use InnoShop\Common\Services\BaseService;

class TiktokPixelService extends BaseService
{
    private string $routeName;

    private string $currencyCode;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $routeName = request()->route()->getName();
        $routeName = str_replace('front.', '', $routeName);
        $routeName = str_replace(locale_code().'.', '', $routeName);

        $this->routeName    = $routeName;
        $this->currencyCode = strtoupper(current_currency_code());
    }

    /**
     * @param  $data
     * @return mixed
     */
    public function renderTags($data): mixed
    {
        $headTag  = $this->renderHeadTag();
        $eventTag = $this->otherEventTag($data);

        return $headTag.$eventTag;
    }

    /**
     * @return string
     */
    public function renderHeadTag(): string
    {
        return view('TiktokPixel::head_tag')->render();
    }

    /**
     * See https://ads.tiktok.com/help/article/supported-standard-events?lang=zh
     * Search, ViewContent, AddToCart, AddToWishlist, InitiateCheckout, PlaceAnOrder, CompletePayment
     *
     * @param  $globalData
     * @return string
     */
    public function otherEventTag($globalData): string
    {
        $tags = $this->getSearchTag($globalData);
        $tags .= $this->getViewContentTag($globalData);
        $tags .= $this->getAddToCartTag();
        $tags .= $this->getAddToWishlistTag();
        $tags .= $this->getInitiateCheckoutTag($globalData);
        $tags .= $this->getPlaceAnOrderTag($globalData);
        $tags .= $this->getCompletePaymentTag($globalData);

        return $tags;
    }

    /**
     * @param  $globalData
     * @return string
     */
    private function getSearchTag($globalData): string
    {
        if ($this->routeName == 'products.index' && request('keyword')) {
            $products = $globalData['products'];
            $data     = [
                'search_string' => request('keyword'),
                'content_ids'   => $products->pluck('id')->toArray(),
                'value'         => $products->first()->masterSku->price ?? 0,
                'currency'      => $this->currencyCode,
            ];

            return view('TiktokPixel::search', $data)->render();
        } else {
            return '';
        }
    }

    /**
     * @param  $globalData
     * @return string
     */
    private function getViewContentTag($globalData): string
    {
        if ($this->routeName == 'products.show' || $this->routeName == 'products.slug_show') {
            $product = $globalData['product'];
            $data    = [
                'content_ids' => [$product->id],
                'value'       => $product->masterSku->price,
                'currency'    => $this->currencyCode,
            ];

            return view('TiktokPixel::view_content', $data)->render();
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    private function getAddToCartTag(): string
    {
        $data = [
            'currency' => $this->currencyCode,
        ];

        return view('TiktokPixel::add_to_cart', $data)->render();
    }

    /**
     * @return string
     */
    private function getAddToWishlistTag(): string
    {
        $data = [
            'currency' => $this->currencyCode,
        ];

        return view('TiktokPixel::add_to_wishlist', $data)->render();
    }

    /**
     * @param  $globalData
     * @return string
     */
    private function getInitiateCheckoutTag($globalData): string
    {
        if (in_array($this->routeName, ['checkout.index', 'multi.checkout.index'])) {
            $totalNumber = $globalData['total_number'];
            $cartList    = $globalData['cart_list'];
            $data        = [
                'content_ids' => collect($cartList)->pluck('id'),
                'currency'    => $this->currencyCode,
                'num_items'   => $totalNumber,
                'value'       => $globalData['amount'],
            ];

            return view('TiktokPixel::init_checkout', $data)->render();
        } else {
            return '';
        }
    }

    /**
     * @param  $globalData
     * @return string
     */
    private function getPlaceAnOrderTag($globalData): string
    {
        if ($this->routeName == 'orders.pay') {
            $order = $globalData['order'];
            $data  = [
                'content_ids' => $order->items->pluck('product_id'),
                'currency'    => strtoupper($order->currency_code),
                'num_items'   => $order->items->sum('quantity'),
                'value'       => round($order->total),
                'event'       => 'PlaceAnOrder',
            ];

            return view('TiktokPixel::pay', $data)->render();
        }

        return '';
    }

    /**
     * @param  $globalData
     * @return string
     */
    private function getCompletePaymentTag($globalData): string
    {
        if ($this->routeName == 'checkout.success') {
            $order = $globalData['order'];
            $data  = [
                'content_ids' => $order->items->pluck('product_id'),
                'currency'    => strtoupper($order->currency_code),
                'num_items'   => $order->items->sum('quantity'),
                'value'       => round($order->total),
                'event'       => 'CompletePayment',
            ];

            return view('TiktokPixel::purchase', $data)->render();
        }

        return '';
    }
}
