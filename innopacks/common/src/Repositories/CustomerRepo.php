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
use Throwable;

class CustomerRepo extends BaseRepo
{
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

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        $keyword = $filters['keyword'] ?? '';
        if ($keyword) {
            $builder->where(function ($query) use ($keyword) {
                $query->where('email', 'like', "%$keyword%")
                    ->orWhere('name', 'like', "%$keyword%");
            });
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
        $item = new Customer($data);
        $item->saveOrFail();

        return $item;
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
            'password'          => '',
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
}
