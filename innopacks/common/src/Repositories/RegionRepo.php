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
use InnoShop\Common\Models\Region;

class RegionRepo extends BaseRepo
{
    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Region::query();
        $builder->orderBy('position')->orderBy('name');

        return fire_hook_filter('repo.region.builder', $builder);
    }

    /**
     * @param  $data
     * @return mixed
     * @throws \Throwable
     */
    public function create($data): mixed
    {
        $region = new Region($this->handleData($data));
        $region->saveOrFail();

        $region->regionStates()->delete();
        $region->regionStates()->createMany($data['region_states']);

        return $region;
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

        $item->regionStates()->delete();
        $item->regionStates()->createMany($data['region_states']);

        return $item;
    }

    /**
     * @param  $requestData
     * @return array
     */
    public function handleData($requestData): array
    {
        return [
            'name'        => $requestData['name'],
            'description' => $requestData['description'],
            'position'    => $requestData['position'] ?? 0,
            'active'      => $requestData['active']   ?? true,
        ];
    }
}
