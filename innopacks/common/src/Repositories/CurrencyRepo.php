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
use Illuminate\Support\Collection;
use InnoShop\Common\Models\Currency;

class CurrencyRepo extends BaseRepo
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
     * @return Collection
     */
    public function enabledList(): mixed
    {
        return $this->withActive()->builder()->get();
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Currency::query();

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

        return fire_hook_filter('repo.currency.builder', $builder);
    }

    /**
     * @param  $data
     * @return Currency
     * @throws \Exception|\Throwable
     */
    public function create($data): Currency
    {
        $data = $this->handleData($data);
        $item = new Currency($data);
        $item->saveOrFail();

        return $item;
    }

    /**
     * @param  $item
     * @param  $data
     * @return mixed
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
        $item->delete();
    }

    /**
     * @param  array  $requestData
     * @return array
     */
    private function handleData(array $requestData): array
    {
        return [
            'name'          => $requestData['name'],
            'code'          => $requestData['code']          ?? '',
            'symbol_left'   => $requestData['symbol_left']   ?? '',
            'symbol_right'  => $requestData['symbol_right']  ?? '',
            'decimal_place' => $requestData['decimal_place'] ?? 0,
            'value'         => $requestData['value']         ?? 1,
            'active'        => $requestData['active']        ?? true,
        ];
    }
}
