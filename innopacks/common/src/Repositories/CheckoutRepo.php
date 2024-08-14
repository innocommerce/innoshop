<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Illuminate\Database\Eloquent\Builder;
use InnoShop\Common\Models\Checkout;

class CheckoutRepo extends BaseRepo
{
    /**
     * @param  $requestData
     * @return array
     */
    private function handleData($requestData): array
    {
        return [
            'customer_id'          => $requestData['customer_id']          ?? 0,
            'guest_id'             => $requestData['guest_id']             ?? '',
            'shipping_address_id'  => $requestData['shipping_address_id']  ?? 0,
            'shipping_method_code' => $requestData['shipping_method_code'] ?? '',
            'billing_address_id'   => $requestData['billing_address_id']   ?? 0,
            'billing_method_code'  => $requestData['billing_method_code']  ?? '',
            'reference'            => $requestData['reference']            ?? [],
        ];
    }

    /**
     * Get filter builder.
     *
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Checkout::query()->with([
            'customer',
            'shippingAddress',
            'billingAddress',
        ]);

        $customerID = $filters['customer_id'] ?? 0;
        if ($customerID) {
            $builder->where('customer_id', $customerID);
        }

        $guestID = $filters['guest_id'] ?? 0;
        if (empty($customerID) && $guestID) {
            $builder->where('guest_id', $guestID);
        }

        return fire_hook_filter('repo.checkout.builder', $builder);
    }

    /**
     * @param  $data
     * @return mixed
     * @throws \Throwable
     */
    public function create($data): mixed
    {
        $checkout = new Checkout($this->handleData($data));
        $checkout->saveOrFail();

        return $checkout;
    }

    /**
     * @param  mixed  $item
     * @param  $data
     * @return mixed
     */
    public function update(mixed $item, $data): mixed
    {
        if (isset($data['shipping_address_id'])) {
            $item->shipping_address_id = $data['shipping_address_id'];
        }

        if (isset($data['shipping_method_code'])) {
            $item->shipping_method_code = $data['shipping_method_code'];
        }

        if (isset($data['billing_address_id'])) {
            $item->billing_address_id = $data['billing_address_id'];
        }

        if (isset($data['billing_method_code'])) {
            $item->billing_method_code = $data['billing_method_code'];
        }

        $item->save();

        return $item;
    }
}
