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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use InnoShop\Common\Models\Country;

class CountryRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'name', 'type' => 'input', 'label' => trans('common/base.name')],
            ['name' => 'code', 'type' => 'input', 'label' => trans('panel/currency.code')],
            ['name' => 'continent', 'type' => 'input', 'label' => trans('panel/country.continent')],
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
            ['value' => 'name', 'label' => trans('common/base.name')],
            ['value' => 'code', 'label' => trans('panel/currency.code')],
            ['value' => 'continent', 'label' => trans('panel/country.continent')],
        ];

        return fire_hook_filter('common.repo.country.search_field_options', $options);
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

        return fire_hook_filter('common.repo.country.filter_button_options', $filters);
    }

    /**
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->builder($filters)->orderByDesc('id')->paginate();
    }

    /**
     * @param  array  $filters
     * @return Collection
     */
    public function getCountries(array $filters = []): Collection
    {
        return $this->withActive()->builder($filters)->orderBy('position')->orderBy('name')->get();
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Country::query();
        $filters = array_merge($this->filters, $filters);

        $name = $filters['name'] ?? '';
        if ($name) {
            $builder->where('name', 'like', "%$name%");
        }

        $code = $filters['code'] ?? '';
        if ($code) {
            $builder->where('code', 'like', "%$code%");
        }

        $continent = $filters['continent'] ?? '';
        if ($continent) {
            $builder->where('continent', 'like', "%$continent%");
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        // Handle new search filters (keyword + search_field)
        $keyword     = $filters['keyword'] ?? '';
        $searchField = $filters['search_field'] ?? '';
        if ($keyword && $searchField) {
            $builder->where($searchField, 'like', "%{$keyword}%");
        } elseif ($keyword) {
            $builder->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%")
                    ->orWhere('continent', 'like', "%{$keyword}%");
            });
        }

        return fire_hook_filter('repo.country.builder', $builder);
    }

    /**
     * @param  $data
     * @return mixed
     */
    public function create($data): mixed
    {
        $data = $this->handleData($data);

        return Country::query()->create($data);
    }

    /**
     * @param  $items
     * @return void
     */
    public function createMany($items): void
    {
        $countries = [];
        foreach ($items as $item) {
            $countries[] = $this->handleData($item);
        }
        Country::query()->insert($countries);
    }

    /**
     * @param  $requestData
     * @return array
     */
    public function handleData($requestData): array
    {
        return [
            'name'       => $requestData['name'],
            'code'       => $requestData['code'],
            'continent'  => $requestData['continent'],
            'position'   => $requestData['position'] ?? 0,
            'active'     => $requestData['active'] ?? true,
            'created_at' => $requestData['created_at'] ?? now(),
            'updated_at' => $requestData['updated_at'] ?? now(),
        ];
    }
}
