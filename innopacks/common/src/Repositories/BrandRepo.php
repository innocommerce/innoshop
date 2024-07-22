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
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\Brand;

class BrandRepo extends BaseRepo
{
    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Brand::query();

        $filters = array_merge($this->filters, $filters);

        $name = $filters['name'] ?? '';
        if ($name) {
            $builder->where('name', $name);
        }

        $slug = $filters['slug'] ?? '';
        if ($slug) {
            $builder->where('slug', $slug);
        }

        $first = $filters['first'] ?? '';
        if ($first) {
            $builder->where('first', $first);
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        return fire_hook_filter('repo.brand.builder', $builder);
    }

    /**
     * Create brand.
     *
     * @param  $data
     * @return mixed
     * @throws \Throwable
     */
    public function create($data): mixed
    {
        $item = new Brand;
        $this->createOrUpdate($item, $data);

        return $item;
    }

    /**
     * Update brand.
     *
     * @param  $item
     * @param  $data
     * @return mixed
     * @throws \Throwable
     */
    public function update($item, $data): mixed
    {
        $this->createOrUpdate($item, $data);

        return $item;
    }

    /**
     * Crate or update brand.
     *
     * @param  Brand  $brand
     * @param  $data
     * @return void
     * @throws \Throwable
     */
    private function createOrUpdate(Brand $brand, $data): void
    {
        DB::beginTransaction();

        try {
            $brandData = $this->handleBrandData($data);
            $brand->fill($brandData);
            $brand->saveOrFail();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param  $data
     * @return string[]
     */
    private function handleBrandData($data): array
    {
        return [
            'name'     => $data['name'],
            'slug'     => $data['slug'],
            'first'    => $data['first'],
            'logo'     => $data['logo']     ?? '',
            'position' => $data['position'] ?? 0,
            'active'   => $data['active']   ?? true,
        ];
    }
}
