<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use InnoShop\Common\Models\Address;
use InnoShop\Common\Models\Country;
use InnoShop\Common\Models\State;
use Throwable;

class AddressRepo extends BaseRepo
{
    const ADDRESS_TYPES = [
        'shipping', 'billing', 'store',
    ];

    /**
     * @return array
     */
    public static function getAddressTypes(): array
    {
        $items = [];
        foreach (self::ADDRESS_TYPES as $type) {
            $items[] = [
                'code'  => $type,
                'label' => panel_trans('address.'.$type),
            ];
        }

        return $items;
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder    = Address::query();
        $customerID = $filters['customer_id'] ?? 0;
        if ($customerID) {
            $builder->where('customer_id', $customerID);
        }

        $guestID = $filters['guest_id'] ?? '';
        if (empty($customerID) && $guestID) {
            $builder->where('guest_id', $guestID);
        }

        return fire_hook_filter('repo.address.builder', $builder);
    }

    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function create($data): mixed
    {
        $address = new Address($this->handleData($data));
        $address->saveOrFail();
        $this->checkAndSetDefault($address, $data);

        return $address;
    }

    /**
     * @param  $item
     * @param  $data
     * @return mixed
     */
    public function update($item, $data): mixed
    {
        $item->fill($this->handleData($data));
        $item->saveOrFail();
        $this->checkAndSetDefault($item, $data);

        return $item;
    }

    /**
     * @param  $address
     * @param  $data
     * @return void
     */
    public function checkAndSetDefault($address, $data): void
    {
        $default = $data['default'] ?? false;
        if (! $default) {
            return;
        }
        $this->setDefaultAddress($address);
    }

    /**
     * @param  $address
     * @return void
     */
    public function setDefaultAddress($address): void
    {
        $this->builder(['customer_id' => $address->customer_id])->update(['default' => false]);
        $address->default = true;
        $address->save();
    }

    /**
     * Clear expired guest addresses.
     * @return void
     */
    public function clearExpiredAddresses(): void
    {
        $expiredTime = Carbon::now()->subDay();
        Address::query()->where('customer_id', 0)
            ->where('created_at', '<', $expiredTime)
            ->delete();
    }

    /**
     * @param  $requestData
     * @return array
     */
    private function handleData($requestData): array
    {
        $countryID   = $requestData['country_id']   ?? 0;
        $countryCode = $requestData['country_code'] ?? '';

        $stateID   = $requestData['state_id']   ?? 0;
        $stateCode = $requestData['state_code'] ?? '';

        $countryRow = $stateRow = null;
        if ($countryCode) {
            $countryRow = Country::query()->where('code', $countryCode)->firstOrFail();
        } elseif ($countryID) {
            $countryRow = State::query()->find($countryID);
        }

        if ($stateCode) {
            $filters = [
                'country_id' => $countryRow->id,
                'code'       => $stateCode,
            ];
            $stateRow = StateRepo::getInstance()->builder($filters)->first();
        } elseif ($stateID) {
            $stateRow = State::query()->find($stateID);
        }

        return [
            'customer_id' => $requestData['customer_id'] ?? 0,
            'guest_id'    => $requestData['guest_id']    ?? '',
            'name'        => $requestData['name'],
            'email'       => $requestData['email'] ?? '',
            'phone'       => $requestData['phone'],
            'country_id'  => $requestData['country_id'] ?? ($countryRow->id ?? 0),
            'state_id'    => $stateRow->id              ?? 0,
            'state'       => $requestData['state']      ?? ($stateRow->name ?? ''),
            'city_id'     => $requestData['city_id']    ?? 0,
            'city'        => $requestData['city']       ?? '',
            'zipcode'     => $requestData['zipcode']    ?? '',
            'address_1'   => $requestData['address_1']  ?? '',
            'address_2'   => $requestData['address_2']  ?? '',
            'default'     => $requestData['default']    ?? false,
        ];
    }
}
