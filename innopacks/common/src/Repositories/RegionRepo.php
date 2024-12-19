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
use InnoShop\Common\Models\Region;
use Throwable;

class RegionRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'name', 'type' => 'input', 'label' => trans('panel/region.name')],
            ['name' => 'description', 'type' => 'input', 'label' => trans('panel/region.description')],
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
     * @return Builder
     */
    public function getRegions(array $filters = []): Builder
    {
        return $this->builder($filters)->orderBy('position')->orderBy('name');
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Region::query();
        $name    = $filters['name'] ?? '';
        if ($name) {
            $builder->where('name', 'like', "%$name%");
        }

        $description = $filters['description'] ?? '';
        if ($description) {
            $builder->where('description', 'like', "%$description%");
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        return fire_hook_filter('repo.region.builder', $builder);
    }

    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
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
