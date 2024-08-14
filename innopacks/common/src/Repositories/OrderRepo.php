<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use InnoShop\Common\Models\Address;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Resources\AddressListItem;
use InnoShop\Common\Services\StateMachineService;

class OrderRepo extends BaseRepo
{
    /**
     * @return array
     */
    public function getFilterStatuses(): array
    {
        $statuses = [
            StateMachineService::UNPAID,
            StateMachineService::PAID,
            StateMachineService::SHIPPED,
            StateMachineService::COMPLETED,
            StateMachineService::CANCELLED,
        ];

        return fire_hook_filter('common.repo.order.statuses', $statuses);
    }

    /**
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $builder = $this->builder($filters)->orderByDesc('id');

        return $builder->paginate();
    }

    /**
     * @return Builder
     */
    public function baseBuilder(): Builder
    {
        return Order::query();
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $relations = [
            'customer',
            'items',
        ];

        $relations = array_merge($this->relations, $relations);
        $builder   = $this->baseBuilder()->with($relations);

        $filters = array_merge($this->filters, $filters);

        $customerID = $filters['customer_id'] ?? 0;
        if ($customerID) {
            $builder->where('customer_id', $customerID);
        }

        $number = $filters['number'] ?? '';
        if ($number) {
            $builder->where('number', $number);
        }

        $email = $filters['email'] ?? '';
        if ($email) {
            $builder->where('email', $email);
        }

        $telephone = $filters['telephone'] ?? '';
        if ($telephone) {
            $builder->where('telephone', $telephone);
        }

        $status = $filters['status'] ?? '';
        if ($status && in_array($status, StateMachineService::ORDER_STATUS)) {
            $builder->where('status', $status);
        }

        $statuses = $filters['statuses'] ?? [];
        if ($statuses) {
            $builder->whereIn('status', $statuses);
        }

        $start = $filters['start'] ?? '';
        if ($start) {
            $builder->where('created_at', '>', $start);
        }

        $end = $filters['end'] ?? '';
        if ($end) {
            $builder->where('created_at', '<', $end);
        }

        return fire_hook_filter('repo.order.builder', $builder);
    }

    /**
     * @param  $data
     * @return mixed
     * @throws \Throwable
     */
    public function create($data): mixed
    {
        $data  = $this->handleData($data);
        $order = new Order($data);
        $order->saveOrFail();

        return $order;
    }

    /**
     * @param  $requestData
     * @return string[]
     */
    private function handleData($requestData): array
    {
        $number = $requestData['number'] ?? '';
        if (empty($number)) {
            $number = $this->generateOrderNumber();
        }

        $customer   = null;
        $customerID = $requestData['customer_id'] ?? 0;
        if ($customerID) {
            $customer = Customer::query()->find($customerID);
        }

        $shippingAddressID = (int) $requestData['shipping_address_id'];
        $billingAddressID  = (int) $requestData['billing_address_id'];
        $shippingAddress   = Address::query()->find($shippingAddressID);
        $billingAddress    = Address::query()->find($billingAddressID);

        $saData = $shippingAddress ? (new AddressListItem($shippingAddress))->jsonSerialize() : [];
        $baData = $billingAddress ? (new AddressListItem($billingAddress))->jsonSerialize() : [];

        return [
            'number'                 => $number,
            'customer_id'            => $customer->id                ?? 0,
            'customer_group_id'      => $customer->customer_group_id ?? 0,
            'shipping_address_id'    => $shippingAddressID,
            'billing_address_id'     => $billingAddressID,
            'customer_name'          => $customer->name                      ?? ($shippingAddress->name ?? ''),
            'email'                  => $customer->email                     ?? ($shippingAddress->email ?? ''),
            'calling_code'           => $customer->calling_code              ?? 0,
            'telephone'              => $customer->telephone                 ?? ($shippingAddress->phone ?? ''),
            'total'                  => $requestData['total']                ?? 0,
            'locale'                 => $requestData['locale']               ?? front_locale_code(),
            'currency_code'          => $requestData['currency_code']        ?? current_currency_code(),
            'currency_value'         => $requestData['currency_value']       ?? current_currency()->value,
            'ip'                     => $requestData['ip']                   ?? request()->ip(),
            'user_agent'             => $requestData['user_agent']           ?? request()->userAgent(),
            'status'                 => $requestData['status']               ?? 'created',
            'shipping_method_code'   => $requestData['shipping_method_code'] ?? '',
            'shipping_method_name'   => $requestData['shipping_quote_name']  ?? '',
            'shipping_customer_name' => $saData['name']                      ?? '',
            'shipping_calling_code'  => $saData['calling_code']              ?? '',
            'shipping_telephone'     => $saData['phone']                     ?? '',
            'shipping_country'       => $saData['country_name']              ?? '',
            'shipping_country_id'    => $saData['country_id']                ?? 0,
            'shipping_state_id'      => $saData['state_id']                  ?? 0,
            'shipping_state'         => $saData['state_name']                ?? '',
            'shipping_city'          => $saData['city']                      ?? '',
            'shipping_address_1'     => $saData['address_1']                 ?? '',
            'shipping_address_2'     => $saData['address_2']                 ?? '',
            'shipping_zipcode'       => $saData['zipcode']                   ?? '',
            'billing_method_code'    => $requestData['billing_method_code']  ?? '',
            'billing_method_name'    => $requestData['billing_method_name']  ?? '',
            'billing_customer_name'  => $baData['name']                      ?? '',
            'billing_calling_code'   => $baData['calling_code']              ?? '',
            'billing_telephone'      => $baData['phone']                     ?? '',
            'billing_country'        => $baData['country_name']              ?? '',
            'billing_country_id'     => $baData['country_id']                ?? 0,
            'billing_state_id'       => $baData['state_id']                  ?? 0,
            'billing_state'          => $baData['state_name']                ?? '',
            'billing_city'           => $baData['city']                      ?? '',
            'billing_address_1'      => $baData['address_1']                 ?? '',
            'billing_address_2'      => $baData['address_2']                 ?? '',
            'billing_zipcode'        => $baData['zipcode']                   ?? '',
        ];
    }

    /**
     * @param  $orderNumber
     * @return mixed
     */
    public function getOrderByNumber($orderNumber): mixed
    {
        return $this->builder(['number' => $orderNumber])->first();
    }

    /**
     * Generate order number.
     *
     * @return string
     */
    private function generateOrderNumber(): string
    {
        $number = date('Ymd').rand(10000, 99999);
        if (! $this->builder(['number' => $number])->exists()) {
            return $number;
        }

        return $this->generateOrderNumber();
    }
}
