<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Repositories\Customer\GroupRepo;
use InnoShop\Common\Resources\AddressListItem;
use Throwable;

class CustomerRepo extends BaseRepo
{
    public static function getFromList(): array
    {
        $options = Customer::getFromOptions();
        $result  = [];

        foreach ($options as $key => $value) {
            $result[] = [
                'key'   => $key,
                'value' => $value,
            ];
        }

        return $result;
    }

    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        $criteria = [
            ['name' => 'keyword', 'type' => 'input', 'label' => trans('panel/customer.name')],
            ['name' => 'email', 'type' => 'input', 'label' => trans('panel/customer.email')],
            ['name'       => 'customer_group_id', 'label' => trans('panel/customer.group'), 'type' => 'select',
                'options' => GroupRepo::getInstance()->getSimpleList(), 'options_key' => 'id', 'options_label' => 'name',
            ],
            ['name' => 'from', 'type' => 'select', 'label' => trans('panel/customer.from'), 'options' => self::getFromList(), 'options_key' => 'key', 'options_label' => 'value'],
            ['name' => 'locale', 'type' => 'input', 'label' => trans('panel/customer.locale')],
            ['name' => 'created_at', 'type' => 'date_range', 'label' => trans('panel/common.created_at')],
        ];

        return fire_hook_filter('repo.customer.criteria', $criteria);
    }

    /**
     * @param  $filters
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function list($filters = []): LengthAwarePaginator
    {
        return $this->builder($filters)->orderByDesc('id')->paginate();
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Customer::query();

        $email = $filters['email'] ?? '';
        if ($email) {
            $builder->where('email', 'like', "%$email%");
        }

        $customer_group_id = $filters['customer_group_id'] ?? '';
        if ($customer_group_id) {
            $builder->where('customer_group_id', $customer_group_id);
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        $locale = $filters['locale'] ?? '';
        if ($locale) {
            $builder->where('locale', $locale);
        }

        $from = $filters['from'] ?? '';
        if ($from) {
            $builder->where('from', $from);
        }

        $keyword = $filters['keyword'] ?? '';
        if ($keyword) {
            $builder->where(function ($query) use ($keyword) {
                $query->where('email', 'like', "%$keyword%")
                    ->orWhere('name', 'like', "%$keyword%");
            });
        }

        $createdStart = $filters['created_at_start'] ?? '';
        if ($createdStart) {
            $builder->where('created_at', '>', $createdStart);
        }

        $createdEnd = $filters['created_at_end'] ?? '';
        if ($createdEnd) {
            $builder->where('created_at', '<', $createdEnd);
        }

        return fire_hook_filter('repo.customer.builder', $builder);
    }

    /**
     * @param  $data
     * @return Customer
     * @throws Exception|Throwable
     */
    public function create($data): Customer
    {
        $data = $this->handleData($data);
        if (! isset($data['password'])) {
            $data['password'] = '';
        }
        $item = new Customer($data);
        $item->saveOrFail();

        return fire_hook_filter('repo.customer.create', $item);
    }

    /**
     * @param  $item
     * @param  $data
     * @return mixed
     * @throws Exception
     */
    public function update($item, $data): mixed
    {
        $data = $this->handleData($data);

        $item->fill($data);
        $item->saveOrFail();

        fire_hook_filter('repo.customer.update', $item);

        return $item;
    }

    /**
     * Update profile only include avatar, name and email.
     *
     * @param  $item
     * @param  $data
     * @return mixed
     */
    public function updateProfile($item, $data): mixed
    {
        $data = [
            'avatar' => $data['avatar'] ?? '',
            'name'   => $data['name'],
            'email'  => $data['email'],
        ];

        $item->fill($data);
        $item->saveOrFail();

        return $item;
    }

    /**
     * @param  $email
     * @return mixed
     */
    public function findByEmail($email): mixed
    {
        return $this->builder()->where('email', $email)->first();
    }

    /**
     * Update current password
     *
     * @param  Customer  $customer
     * @param  $data
     * @return bool
     * @throws Exception
     */
    public function updatePassword(mixed $customer, $data): bool
    {
        $oldPassword        = $data['old_password'];
        $newPassword        = $data['new_password']              ?? '';
        $newPasswordConfirm = $data['new_password_confirmation'] ?? '';

        if (! $customer->verifyPassword($oldPassword)) {
            throw new Exception('invalid_password');
        } elseif ($newPassword != $newPasswordConfirm) {
            throw new Exception('new_password_must_keep_same');
        }

        return $customer->update(['password' => bcrypt($newPassword)]);
    }

    /**
     * @param  mixed  $customer
     * @param  $newPassword
     * @return mixed
     */
    public function forceUpdatePassword(mixed $customer, $newPassword): mixed
    {
        return $customer->update(['password' => bcrypt($newPassword)]);
    }

    /**
     * @param  Customer  $item
     * @return void
     */
    public function destroy($item): void
    {
        $item->favorites()->delete();
        $item->socials()->delete();
        $item->delete();
    }

    /**
     * @param  array  $requestData
     * @return array
     * @throws Exception
     */
    private function handleData(array $requestData): array
    {
        $data = [
            'email'             => $requestData['email'],
            'name'              => $requestData['name'],
            'customer_group_id' => $requestData['customer_group_id'] ?? 0,
            'address_id'        => $requestData['address_id']        ?? 0,
            'locale'            => $requestData['locale']            ?? locale_code(),
            'active'            => $requestData['active']            ?? true,
            'code'              => $requestData['code']              ?? '',
            'from'              => $requestData['from']              ?? 'pc_web',
        ];

        $avatar = $requestData['avatar'] ?? '';
        if ($avatar) {
            $data['avatar'] = $avatar;
        }

        $password = $requestData['password'] ?? '';
        if ($password) {
            $data['password'] = bcrypt($password);
        }

        return $data;
    }

    /**
     * @param  $keyword
     * @param  int  $limit
     * @return mixed
     */
    public function autocomplete($keyword, int $limit = 10): mixed
    {
        $builder = Customer::query();
        if ($keyword) {
            $builder->where(function ($query) use ($keyword) {
                $query->where('email', 'like', "%$keyword%")
                    ->orWhere('name', 'like', "%$keyword%");
            });
        }

        return $builder->limit($limit)->get();
    }

    /**
     * Get customer list by IDs.
     *
     * @param  mixed  $customerIDs
     * @return mixed
     */
    public function getListByCustomerIDs(mixed $customerIDs): mixed
    {
        if (empty($customerIDs)) {
            return [];
        }
        if (is_string($customerIDs)) {
            $customerIDs = explode(',', $customerIDs);
        }

        return Customer::query()
            ->whereIn('id', $customerIDs)
            ->orderByRaw('FIELD(id, '.implode(',', $customerIDs).')')
            ->get();
    }

    /**
     * Get customer detail data including addresses, transactions, groups and locales.
     *
     * @param  Customer  $customer
     * @param  int  $transactionPerPage
     * @return array
     */
    public function getCustomerDetailData(Customer $customer, int $transactionPerPage = 10): array
    {
        $addresses = AddressListItem::collection($customer->addresses)->jsonSerialize();

        $transactions = $customer->transactions()
            ->orderByDesc('created_at')
            ->paginate($transactionPerPage);

        $data = [
            'customer'     => $customer,
            'addresses'    => $addresses,
            'groups'       => GroupRepo::getInstance()->getSimpleList(),
            'locales'      => locales()->toArray(),
            'transactions' => $transactions,
        ];

        return fire_hook_filter('repo.customer.detail_data', $data, $customer);
    }
}
