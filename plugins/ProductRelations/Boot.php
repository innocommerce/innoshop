<?php

namespace Plugin\ProductRelations;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Models\Product\Category;
use InnoShop\Common\Repositories\ProductRepo;

class Boot
{
    public function init(): void
    {

        listen_hook_filter('front.product.rendershow', function ($data) {
            $product   = $data['product'];
            $productId = $product->id;
            if (empty($product->relations) || $product->relations->count() == 0) {
                $data['related'] = $this->getRelatedProducts($productId);
            }

            return $data;
        }, 2000199);
    }

    private function getRelatedProducts($productId)
    {
        $plugin      = plugin_setting('product_relations');
        $cacheStatus = 1;
        if (isset($plugin['cache_status']) && empty($plugin['cache_status'])) {
            $cacheStatus = $plugin['cache_status'];
        }
        $key = 'product_relations_show_'.$productId;
        if ($cacheStatus == 1) {
            $relations = Cache::get($key);
        } else {
            $relations = null;
        }
        if (empty($relations)) {
            $relations_type = 1;
            if (isset($plugin['relations_type']) && ! empty($plugin['relations_type'])) {
                $relations_type = $plugin['relations_type'];
            }
            $categoriesIDs = Category::query()->where('product_id', $productId)->get(['category_id'])->pluck('category_id')->toArray();

            if (empty($categoriesIDs)) {//没有对应的分类，则随机
                if ($relations_type == 1 || $relations_type == 3) {
                    $relationIds = Category::query()->where('product_id', '!=', $productId)->get(['product_id'])->pluck('product_id')->toArray();
                } else {
                    $relationIds = [];
                }
            } else {
                if ($relations_type == 1) {//全局随机
                    $relationIds = Category::query()->where('product_id', '!=', $productId)->get(['product_id'])->pluck('product_id')->toArray();
                } else {
                    //找出最底级的分类（无子级的分类留下，有子级的排除）
                    $allPcs = Category::query()->where('product_id', '!=', $productId)->whereIn('category_id', $categoriesIDs)->pluck('category_id')->toArray();

                    $allPcs     = array_unique($allPcs);
                    $categories = \InnoShop\Common\Models\Category::query()->whereIn('id', $allPcs)->get([
                        'id',
                        'parent_id',
                    ]);
                    $parentIDs = [];
                    foreach ($categories as $category) {
                        $tmpCategories[$category->id] = $category;
                        $this->getParentIDs($parentIDs, $category);
                    }
                    $parentIDs = array_unique($parentIDs);

                    $categories = \InnoShop\Common\Models\Category::query()->whereIn('id', $parentIDs)->get([
                        'id',
                        'parent_id',
                    ]);
                    $tmpCategories = [];
                    foreach ($categories as $category) {
                        $tmpCategories[$category->id] = $category;
                    }
                    $unsetID = [];
                    foreach ($categories as $category) {
                        $parent_id = $category->parent_id;
                        if (isset($tmpCategories[$parent_id])) {//父级存在，要把父级清除
                            $unsetID[] = $parent_id;
                        }
                    }
                    foreach ($unsetID as $id) {
                        unset($tmpCategories[$id]);
                    }

                    $categIds = [];
                    foreach ($tmpCategories as $category_id => $category) {
                        $categIds[] = $category_id;
                    }
                    //分类
                    $relationIds = Category::query()->where('product_id', '!=', $productId)->whereIn('category_id', $categIds)->get(['product_id'])->pluck('product_id')->toArray();
                }
            }
            if (! empty($relationIds)) {
                $limit = 4;
                if (isset($plugin['relations_limit']) && ! empty($plugin['relations_limit'])) {
                    $limit = $plugin['relations_limit'];
                }

                $relationIds = Product::query()->whereIn('id', $relationIds)->limit($limit)->inRandomOrder()->get(['id'])->pluck('id')->toArray();

                $relations = Product::query()->where('active', true)->whereIn('id', $relationIds)->get(); //ProductRepo::getProductsByIds($relationIds)->jsonSerialize();
                if ($cacheStatus == 1) {
                    Cache::put($key, $relations, Carbon::now()->addMinute());
                }
            } else {
                $relations = [];
            }
        }

        return $relations;
    }

    private function randomSelect($array, $number)
    {
        $keys = array_rand($array, $number);

        if (! is_array($keys)) {
            $keys = [$keys];
        }

        $result = [];

        foreach ($keys as $key) {
            $result[] = $array[$key];
        }

        return $result;
    }

    private function getParentIDs(&$parentIDs, $findCategory)
    {
        $parentIDs[] = $findCategory->id;
        if ($findCategory->parent_id != 0) {
            $findCategory = \InnoShop\Common\Models\Category::query()->where('id', $findCategory->parent_id)->first([
                'id',
                'parent_id',
            ]);
            $this->getParentIDs($parentIDs, $findCategory);
        }
    }
}
