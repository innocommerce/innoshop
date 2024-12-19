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
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'name', 'type' => 'input', 'label' => trans('panel/currency.name')],
            ['name' => 'code', 'type' => 'input', 'label' => trans('panel/currency.code')],
            ['name' => 'symbol_left', 'type' => 'input', 'label' => trans('panel/currency.symbol_left')],
            ['name' => 'symbol_right', 'type' => 'input', 'label' => trans('panel/currency.symbol_right')],
            ['name' => 'decimal_place', 'type' => 'input', 'label' => trans('panel/currency.decimal_place')],
            ['name' => 'value', 'type' => 'input', 'label' => trans('panel/currency.value')],
        ];
    }

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
     * @return array
     */
    public function asOptions(): array
    {
        $currencies = [];
        foreach ($this->enabledList() as $item) {
            $currencies[] = [
                'value' => $item->code,
                'label' => $item->name,
            ];
        }

        return $currencies;
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Currency::query();

        $name = $filters['name'] ?? '';
        if ($name) {
            $builder->where('name', 'like', "%$name%");
        }

        $code = $filters['code'] ?? '';
        if ($code) {
            $builder->where('code', 'like', "%$code%");
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        $keyword = $filters['keyword'] ?? '';
        if ($keyword) {
            $builder->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%$keyword%")
                    ->orWhere('code', 'like', "%$keyword%");
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
