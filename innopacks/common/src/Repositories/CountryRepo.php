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
use InnoShop\Common\Models\Country;

class CountryRepo extends BaseRepo
{
    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Country::query();
        $builder->orderBy('position')->orderBy('name');

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
