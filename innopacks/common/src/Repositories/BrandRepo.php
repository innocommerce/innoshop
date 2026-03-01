<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\Brand;
use Throwable;

class BrandRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'name', 'type' => 'input', 'label' => trans('panel/brand.name')],
            ['name' => 'first', 'type' => 'input', 'label' => trans('panel/brand.first')],
            ['name' => 'slug', 'type' => 'input', 'label' => trans('panel/common.slug')],
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
            ['value' => 'name', 'label' => trans('panel/brand.name')],
            ['value' => 'slug', 'label' => trans('panel/common.slug')],
        ];

        return fire_hook_filter('common.repo.brand.search_field_options', $options);
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

        return fire_hook_filter('common.repo.brand.filter_button_options', $filters);
    }

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

        // Handle new search filters (keyword + search_field)
        $keyword     = $filters['keyword'] ?? '';
        $searchField = $filters['search_field'] ?? '';
        if ($keyword && $searchField) {
            $builder->where($searchField, 'like', "%{$keyword}%");
        } elseif ($keyword) {
            $builder->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%");
            });
        }

        return fire_hook_filter('repo.brand.builder', $builder);
    }

    /**
     * Create brand.
     *
     * @param  $data
     * @return mixed
     * @throws Throwable
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
     * @throws Throwable
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
     * @throws Throwable
     */
    private function createOrUpdate(Brand $brand, $data): void
    {
        DB::beginTransaction();

        try {
            $brandData = $this->handleBrandData($data);
            $brand->fill($brandData);
            $brand->saveOrFail();

            DB::commit();
        } catch (Exception $e) {
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
            'logo'     => $data['logo'] ?? '',
            'position' => $data['position'] ?? 0,
            'active'   => $data['active'] ?? true,
        ];
    }

    /**
     * @param  $keyword
     * @param  int  $limit
     * @return mixed
     */
    public function autocomplete($keyword, int $limit = 10): mixed
    {
        $builder = Brand::query();
        if ($keyword) {
            $builder->where('name', 'like', "%{$keyword}%");
        }

        return $builder->limit($limit)->get();
    }

    /**
     * Get brand list by IDs.
     *
     * @param  mixed  $BrandIDs
     * @return mixed
     */
    public function getListByBrandIDs(mixed $BrandIDs): mixed
    {
        if (empty($BrandIDs)) {
            return [];
        }
        if (is_string($BrandIDs)) {
            $BrandIDs = explode(',', $BrandIDs);
        }

        return Brand::query()
            ->whereIn('id', $BrandIDs)
            ->orderByRaw('FIELD(id, '.implode(',', $BrandIDs).')')
            ->get();
    }

    /**
     * @param  $id
     * @return string
     */
    public function getNameByID($id): string
    {
        return Brand::query()->find($id)->name ?? '';
    }
}
