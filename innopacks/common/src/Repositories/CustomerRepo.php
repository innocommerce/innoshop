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
use InnoShop\Common\Models\Customer;

class CustomerRepo extends BaseRepo
{
    /**
     * @param  $filters
     * @return LengthAwarePaginator
     * @throws \Exception
     */
    public function list($filters = []): LengthAwarePaginator
    {
        return $this->builder($filters)->paginate();
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

        return $builder;
    }

    /**
     * @param  $data
     * @return Customer
     * @throws \Exception|\Throwable
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
     * @throws \Exception
     */
    public function update($item, $data): mixed
    {
        $data = $this->handleData($data);

        $item->fill($data);
        $item->saveOrFail();

        return $item;
    }

    /**
     * @param  $item
     * @return void
     */
    public function destroy($item): void
    {
        $item->translations()->delete();
        $item->delete();
    }

    /**
     * @param  array  $requestData
     * @return array
     * @throws \Exception
     */
    private function handleData(array $requestData): array
    {
        $data = [
            'email'             => $requestData['email'],
            'name'              => $requestData['name'],
            'avatar'            => $requestData['avatar']            ?? '',
            'customer_group_id' => $requestData['customer_group_id'] ?? 0,
            'address_id'        => $requestData['address_id']        ?? 0,
            'locale'            => $requestData['locale']            ?? locale_code(),
            'active'            => $requestData['active']            ?? true,
            'code'              => $requestData['code']              ?? '',
            'from'              => $requestData['from']              ?? 'web',
        ];

        if (isset($requestData['password'])) {
            $data['password'] = bcrypt($requestData['password']);
        }

        return $data;
    }
}
