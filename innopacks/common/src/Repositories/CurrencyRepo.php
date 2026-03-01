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
    private static mixed $enabledCurrencies = null;

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
     * Get search field options for data_search component
     *
     * @return array
     */
    public static function getSearchFieldOptions(): array
    {
        $options = [
            ['value' => '', 'label' => trans('panel/common.all_fields')],
            ['value' => 'name', 'label' => trans('panel/currency.name')],
            ['value' => 'code', 'label' => trans('panel/currency.code')],
        ];

        return fire_hook_filter('common.repo.currency.search_field_options', $options);
    }

    /**
     * Get filter button options for data_search component
     *
     * @return array
     */
    public static function getFilterButtonOptions(): array
    {
        $filters = [
            [
                'name'    => 'active',
                'label'   => trans('panel/common.status'),
                'type'    => 'button',
                'options' => [
                    ['value' => '', 'label' => trans('panel/common.all')],
                    ['value' => '1', 'label' => trans('panel/common.active_yes')],
                    ['value' => '0', 'label' => trans('panel/common.active_no')],
                ],
            ],
        ];

        return fire_hook_filter('common.repo.currency.filter_button_options', $filters);
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
        if (self::$enabledCurrencies !== null) {
            return self::$enabledCurrencies;
        }

        return self::$enabledCurrencies = $this->withActive()->builder()->get();
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

        $filters = array_merge($this->filters, $filters);

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

        // Handle new search filters (keyword + search_field)
        $searchKeyword = $filters['keyword'] ?? '';
        $searchField   = $filters['search_field'] ?? '';
        if ($searchKeyword && $searchField) {
            $builder->where($searchField, 'like', "%{$searchKeyword}%");
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
            'code'          => $requestData['code'] ?? '',
            'symbol_left'   => $requestData['symbol_left'] ?? '',
            'symbol_right'  => $requestData['symbol_right'] ?? '',
            'decimal_place' => $requestData['decimal_place'] ?? 0,
            'value'         => $requestData['value'] ?? 1,
            'active'        => $requestData['active'] ?? true,
        ];
    }
}
