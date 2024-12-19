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
            ['name' => 'name', 'type' => 'input', 'label' => trans('panel/common.name')],
            ['name' => 'code', 'type' => 'input', 'label' => trans('panel/currency.code')],
            ['name' => 'continent', 'type' => 'input', 'label' => trans('panel/country.continent')],
        ];
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
            'position'   => $requestData['position']   ?? 0,
            'active'     => $requestData['active']     ?? true,
            'created_at' => $requestData['created_at'] ?? now(),
            'updated_at' => $requestData['updated_at'] ?? now(),
        ];
    }
}
