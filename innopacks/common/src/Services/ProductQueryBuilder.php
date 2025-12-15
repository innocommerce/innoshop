<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InnoShop\Common\Models\Category;
use InnoShop\Common\Repositories\CategoryRepo;

/**
 * Product query builder
 * Responsible for building Eloquent queries based on filter conditions
 */
class ProductQueryBuilder
{
    /**
     * Apply category filters
     *
     * @param  Builder  $builder
     * @param  array  $filters
     * @return Builder
     */
    public function applyCategoryFilters(Builder $builder, array $filters): Builder
    {
        // Collect all category filter conditions
        $typeCategoryIds     = [];
        $specificCategoryIds = [];

        // Parent slug filter (for filtering by parent category like 'plugins' or 'themes')
        $parentSlug = $filters['parent_slug'] ?? '';
        if ($parentSlug) {
            $parentCategory = Category::query()->where('slug', $parentSlug)->first();
            if ($parentCategory) {
                // Get all child categories under the parent category
                $childCategories = CategoryRepo::getInstance()->builder(['parent_id' => $parentCategory->id])->get();
                $typeCategoryIds = array_merge($typeCategoryIds, $childCategories->pluck('id')->toArray());
                // Include the parent category itself
                $typeCategoryIds[] = $parentCategory->id;
            }
        }

        // Single category ID filter
        $categoryId = $filters['category_id'] ?? 0;
        if ($categoryId) {
            $specificCategoryIds[] = $categoryId;
        }

        // Category slug filter
        $categorySlug = $filters['category_slug'] ?? '';
        if ($categorySlug) {
            $category = Category::query()->where('slug', $categorySlug)->first();
            if ($category) {
                $categories            = CategoryRepo::getInstance()->builder(['parent_id' => $category->id])->get();
                $childCategoryIds      = $categories->pluck('id')->toArray();
                $specificCategoryIds   = array_merge($specificCategoryIds, $childCategoryIds);
                $specificCategoryIds[] = $category->id;
            }
        }

        // Multiple category IDs filter (from filters array)
        $filterCategoryIds = $filters['category_ids'] ?? [];
        if ($filterCategoryIds instanceof Collection) {
            $filterCategoryIds = $filterCategoryIds->toArray();
        }
        if ($filterCategoryIds) {
            $specificCategoryIds = array_merge($specificCategoryIds, $filterCategoryIds);
        }

        // Apply filters: if both type and specific categories exist, use intersection (AND)
        // Otherwise, use union (OR)
        if (! empty($typeCategoryIds) && ! empty($specificCategoryIds)) {
            // Intersection: product must belong to both type categories AND specific categories
            $intersectionIds = array_intersect($typeCategoryIds, $specificCategoryIds);
            if (! empty($intersectionIds)) {
                $builder->whereHas('categories', function (Builder $query) use ($intersectionIds) {
                    $query->whereIn('category_id', $intersectionIds);
                });
            } else {
                // No intersection, return empty result
                $builder->whereRaw('1 = 0');
            }
        } elseif (! empty($typeCategoryIds)) {
            // Only type filter: product must belong to any of the type categories
            $builder->whereHas('categories', function (Builder $query) use ($typeCategoryIds) {
                $query->whereIn('category_id', $typeCategoryIds);
            });
        } elseif (! empty($specificCategoryIds)) {
            // Only specific filter: product must belong to any of the specific categories
            $specificCategoryIds = array_unique($specificCategoryIds);
            $builder->whereHas('categories', function (Builder $query) use ($specificCategoryIds) {
                $query->whereIn('category_id', $specificCategoryIds);
            });
        }

        return $builder;
    }

    /**
     * Apply attribute filters (Optimized version)
     * Logic: Attribute groups are intersected (AND), attribute values within the same group are unioned (OR)
     *
     * Example: If user selects Color=Red,Blue AND Size=Large,Medium
     * Result: Products that are (Red OR Blue) AND (Large OR Medium)
     *
     * Performance optimization: Uses a single subquery with GROUP BY and HAVING
     * instead of multiple whereHas calls for better performance with large datasets
     *
     * @param  Builder  $builder
     * @param  array  $filters
     * @return Builder
     */
    public function applyAttributeFilters(Builder $builder, array $filters): Builder
    {
        // Attribute filter (attr format: attr=1:1,2,3|5:6,7)
        // Format explanation: attribute_id:value_id1,value_id2|attribute_id2:value_id3,value_id4
        $attr = $filters['attr'] ?? [];
        if ($attr) {
            $attributes = parse_attr_filters($attr);

            if (count($attributes) === 1) {
                // Single attribute group - use simple whereHas for better performance
                $attribute   = $attributes[0];
                $attributeId = $attribute['attr'];
                $valueIds    = $attribute['value'];

                $builder->whereHas('productAttributes', function ($query) use ($attributeId, $valueIds) {
                    $query->where('attribute_id', $attributeId)
                        ->whereIn('attribute_value_id', $valueIds);
                });
            } else {
                // Multiple attribute groups - use optimized subquery approach
                $builder->whereIn('id', function ($subQuery) use ($attributes) {
                    $subQuery->select('product_id')
                        ->from('product_attributes')
                        ->where(function ($whereQuery) use ($attributes) {
                            foreach ($attributes as $attribute) {
                                $attributeId = $attribute['attr'];
                                $valueIds    = $attribute['value'];

                                $whereQuery->orWhere(function ($orQuery) use ($attributeId, $valueIds) {
                                    $orQuery->where('attribute_id', $attributeId)
                                        ->whereIn('attribute_value_id', $valueIds);
                                });
                            }
                        })
                        ->groupBy('product_id')
                        ->havingRaw('COUNT(DISTINCT attribute_id) = ?', [count($attributes)]);
                });
            }
        }

        // Attribute value ID filter (legacy support)
        $attributeValueIds = parse_int_filters($filters['attribute_value_ids'] ?? []);
        if ($attributeValueIds) {
            $builder->whereHas('productAttributes', function (Builder $query) use ($attributeValueIds) {
                $query->whereIn('attribute_value_id', $attributeValueIds);
            });
        }

        return $builder;
    }

    /**
     * Apply brand filters
     *
     * @param  Builder  $builder
     * @param  array  $filters
     * @return Builder
     */
    public function applyBrandFilters(Builder $builder, array $filters): Builder
    {
        // Single brand ID filter
        $brandID = $filters['brand_id'] ?? 0;
        if ($brandID) {
            $builder->where('brand_id', $brandID);
        }

        // Multiple brand IDs filter
        $brandIds = $filters['brand_ids'] ?? [];
        if ($brandIds) {
            if (is_string($brandIds)) {
                $brandIds = explode(',', $brandIds);
            }
            $builder->whereIn('brand_id', $brandIds);
        }

        return $builder;
    }

    /**
     * Apply price filters
     *
     * @param  Builder  $builder
     * @param  array  $filters
     * @return Builder
     */
    public function applyPriceFilters(Builder $builder, array $filters): Builder
    {
        $priceStart = $filters['price_start'] ?? '';
        if ($priceStart) {
            $builder->whereHas('masterSku', function (Builder $query) use ($priceStart) {
                $query->where('price', '>', $priceStart);
            });
        }

        $priceEnd = $filters['price_end'] ?? '';
        if ($priceEnd) {
            $builder->whereHas('masterSku', function (Builder $query) use ($priceEnd) {
                $query->where('price', '<', $priceEnd);
            });
        }

        return $builder;
    }

    /**
     * Apply stock filters
     *
     * @param  Builder  $builder
     * @param  array  $filters
     * @return Builder
     */
    public function applyStockFilters(Builder $builder, array $filters): Builder
    {
        $stockStatus = $filters['stock_status'] ?? '';
        if ($stockStatus === 'in_stock') {
            $builder->whereHas('masterSku', function (Builder $query) {
                $query->where('quantity', '>', 0);
            });
        } elseif ($stockStatus === 'out_of_stock') {
            $builder->whereHas('masterSku', function (Builder $query) {
                $query->where('quantity', '<=', 0);
            });
        }

        return $builder;
    }

    /**
     * Apply search filters
     *
     * @param  Builder  $builder
     * @param  array  $filters
     * @return Builder
     */
    public function applySearchFilters(Builder $builder, array $filters): Builder
    {
        // Support both 'keyword' and 'search' parameters
        $keyword = $filters['keyword'] ?? $filters['search'] ?? '';
        if ($keyword) {
            $builder->whereHas('translation', function (Builder $query) use ($keyword) {
                $query->where('name', 'like', "%$keyword%");
            })->orWhereHas('skus', function (Builder $query) use ($keyword) {
                $query->where('code', 'like', "%$keyword%");
            });
        }

        return $builder;
    }

    /**
     * Apply SKU filters
     *
     * @param  Builder  $builder
     * @param  array  $filters
     * @return Builder
     */
    public function applySkuFilters(Builder $builder, array $filters): Builder
    {
        $skuCode = $filters['sku_code'] ?? '';
        if ($skuCode) {
            $builder->whereHas('skus', function (Builder $query) use ($skuCode) {
                $query->where('code', 'like', "%$skuCode%");
            });
        }

        $skuId = $filters['sku_id'] ?? '';
        if ($skuId) {
            $builder->whereHas('skus', function (Builder $query) use ($skuId) {
                $query->where('id', $skuId);
            });
        }

        return $builder;
    }

    /**
     * Apply basic filters
     *
     * @param  Builder  $builder
     * @param  array  $filters
     * @return Builder
     */
    public function applyBasicFilters(Builder $builder, array $filters): Builder
    {
        $slug = $filters['slug'] ?? '';
        if ($slug) {
            $builder->where('slug', $slug);
        }

        $productIDs = $filters['product_ids'] ?? [];
        if ($productIDs) {
            $builder->whereIn('products.id', $productIDs);
        }

        if (isset($filters['active'])) {
            $builder->where('products.active', (bool) $filters['active']);
        }

        return $builder;
    }

    /**
     * Apply date filters
     *
     * @param  Builder  $builder
     * @param  array  $filters
     * @return Builder
     */
    public function applyDateFilters(Builder $builder, array $filters): Builder
    {
        $createdStart = $filters['created_at_start'] ?? '';
        if ($createdStart) {
            $builder->where('created_at', '>', $createdStart);
        }

        $createdEnd = $filters['created_at_end'] ?? '';
        if ($createdEnd) {
            $builder->where('created_at', '<', $createdEnd);
        }

        return $builder;
    }
}
