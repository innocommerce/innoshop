<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories\Category;

use Exception;
use InnoShop\Common\Models\Category;

class TreeRepo
{
    private static array $children = [];

    private static array $categories = [];

    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * @param  int  $parentId
     * @return array
     * @throws Exception
     */
    public function getCategoryTree(int $parentId = 0): array
    {
        $categoryIDs = $this->getChildrenIds($parentId);
        $categories  = $this->getFlattenCategories($categoryIDs);
        foreach ($categories as $index => $category) {
            $categories[$index]['children'] = $this->getCategoryTree($category['id']);
        }

        return $categories;
    }

    /**
     * @param  int  $parentId
     * @return array|mixed
     */
    private function getChildrenIds(int $parentId = 0): mixed
    {
        $allChildrenIDs = $this->getAllChildrenIds();

        return $allChildrenIDs[$parentId] ?? [];
    }

    /**
     * @return array
     */
    private function getAllChildrenIds(): array
    {
        if (self::$children) {
            return self::$children;
        }
        $categories = Category::query()
            ->select(['id', 'parent_id'])
            ->orderBy('categories.position')
            ->orderBy('categories.parent_id')
            ->where('active', true)
            ->get();

        $result = [];
        foreach ($categories as $category) {
            $result[$category['parent_id']][] = $category['id'];
        }
        self::$children = $result;

        return $result;
    }

    /**
     * @param  array  $categoryIDs
     * @return array
     * @throws Exception
     */
    private function getFlattenCategories(array $categoryIDs = []): array
    {
        $result = [];
        if (empty($categoryIDs)) {
            return $result;
        }

        $allCategories = $this->getAllFlattenCategories();
        foreach ($categoryIDs as $categoryId) {
            if (isset($allCategories[$categoryId])) {
                $result[] = $allCategories[$categoryId];
            }
        }

        return $result;
    }

    /**
     * @return array
     * @throws Exception
     */
    private static function getAllFlattenCategories(): array
    {
        if (self::$categories) {
            return self::$categories;
        }

        $categories = Category::query()
            ->with('translation')
            ->select(['id', 'slug', 'parent_id', 'image', 'active'])
            ->get();

        $result = [];
        foreach ($categories as $category) {
            $result[$category['id']] = [
                'id'     => $category->id,
                'slug'   => $category->slug,
                'name'   => $category->translation->name,
                'url'    => $category->url,
                'image'  => image_resize($category->image, 300, 300),
                'active' => (bool) $category->active,
            ];
        }
        self::$categories = $result;

        return $result;
    }
}
