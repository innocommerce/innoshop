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
use InnoShop\Common\Models\Category;
use InnoShop\Common\Resources\CategorySimple;
use Throwable;

class CategoryRepo extends BaseRepo
{
    /**
     * @param  array|null  $categoryIDs
     * @return array
     */
    public function getTwoLevelCategories(?array $categoryIDs = []): array
    {
        $filters = ['active' => true, 'parent_id' => 0];
        if ($categoryIDs) {
            $filters['category_ids'] = $categoryIDs;
        }

        $catalogs = $this->builder($filters)
            ->orderBy('position')
            ->get();

        return CategorySimple::collection($catalogs)->jsonSerialize();
    }

    /**
     * @return array
     */
    public function getPerPageItems(): array
    {
        $perPages      = [];
        $configPerPage = system_setting('product_per_page', 15);
        for ($index = 1; $index <= 5; $index++) {
            $perPages[] = $configPerPage * $index;
        }

        return $perPages;
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Category::query()->with([
            'translation',
            'parent.translation',
            'children.translation',
            'children.children.translation',
        ]);

        $slug = $filters['slug'] ?? '';
        if ($slug) {
            $builder->where('slug', $slug);
        }

        $parentSlug = $filters['parent_slug'] ?? '';
        if ($parentSlug) {
            $category = Category::query()->where('slug', $parentSlug)->first();
            if ($category) {
                $filters['parent_id'] = $category->id;
            }
        }

        if (isset($filters['parent_id'])) {
            $parentID = (int) $filters['parent_id'];
            if ($parentID == 0) {
                $builder->where(function (Builder $query) {
                    $query->where('parent_id', 0)->orWhereNull('parent_id');
                });
            } else {
                $builder->where('parent_id', $parentID);
            }
        }

        $excludeIDs = $filters['exclude_ids'] ?? [];
        if ($excludeIDs) {
            $builder->whereNotIn('id', $excludeIDs);
        }

        $categoryIDs = $filters['category_ids'] ?? [];
        if ($categoryIDs) {
            $builder->whereIn('id', $categoryIDs);
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        return fire_hook_filter('repo.category.builder', $builder);
    }

    /**
     * Create category.
     *
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function create($data): mixed
    {
        $item = new Category;
        $this->createOrUpdate($item, $data);

        $children = $data['children'] ?? [];
        $this->handleChildren($item, $children);

        return $item;
    }

    /**
     * Update category.
     *
     * @param  $item
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function update($item, $data): mixed
    {
        $this->createOrUpdate($item, $data);

        $children = $data['children'] ?? [];
        $this->handleChildren($item, $children);

        return $item;
    }

    /**
     * Crate or update category.
     *
     * @param  Category  $category
     * @param  $data
     * @return void
     * @throws Throwable
     */
    private function createOrUpdate(Category $category, $data): void
    {
        DB::beginTransaction();

        try {
            $categoryData = $this->handleCategoryData($data);
            $category->fill($categoryData);
            $category->saveOrFail();

            $translations = $this->handleTranslations($data['translations']);
            $category->translations()->delete();
            $category->translations()->createMany($translations);

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
    private function handleCategoryData($data): array
    {
        return [
            'parent_id' => $data['parent_id'] ?? 0,
            'slug'      => $data['slug'],
            'image'     => $data['image']    ?? '',
            'position'  => $data['position'] ?? 0,
            'active'    => $data['active']   ?? true,
        ];
    }

    /**
     * @param  $translations
     * @return array
     */
    private function handleTranslations($translations): array
    {
        $items = [];
        foreach ($translations as $translation) {
            $name    = $translation['name'];
            $items[] = [
                'locale'           => $translation['locale'],
                'name'             => $name,
                'content'          => $translation['content']          ?? $name,
                'meta_title'       => $translation['meta_title']       ?? $name,
                'meta_description' => $translation['meta_description'] ?? $name,
                'meta_keywords'    => $translation['meta_keywords']    ?? $name,
            ];
        }

        return $items;
    }

    /**
     * @param  $item
     * @param  $children
     * @return void
     * @throws Throwable
     */
    private function handleChildren($item, $children): void
    {
        if (empty($children)) {
            return;
        }

        foreach ($children as $childData) {
            $childCategory = new Category;

            $childId = $childData['id'] ?? 0;
            if ($childId) {
                $childCategory = Category::query()->find($childId);
            }

            $childData['parent_id'] = $item->id;
            if ($childCategory->id) {
                $this->update($childCategory, $childData);
            } else {
                $this->create($childData);
            }
        }
    }

    /**
     * @param  $keyword
     * @param  int  $limit
     * @return mixed
     */
    public function autocomplete($keyword, int $limit = 10): mixed
    {
        $builder = Category::query()->with(['translation']);
        if ($keyword) {
            $builder->whereHas('translation', function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            });
        }

        return $builder->limit($limit)->get();
    }

    /**
     * Get product list by IDs.
     *
     * @param  mixed  $categoryIDs
     * @return mixed
     */
    public function getListByCategoryIDs(mixed $categoryIDs): mixed
    {
        if (empty($categoryIDs)) {
            return [];
        }
        if (is_string($categoryIDs)) {
            $categoryIDs = explode(',', $categoryIDs);
        }

        return Category::query()
            ->with(['translation'])
            ->whereIn('id', $categoryIDs)
            ->orderByRaw('FIELD(id, '.implode(',', $categoryIDs).')')
            ->get();
    }

    /**
     * @param  $id
     * @return string
     */
    public function getNameByID($id): string
    {
        return Category::query()->find($id)->description->name ?? '';
    }
}
