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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

        // Number range filters (format: attribute_ranges=12:5.5,6.5|15:1,
        // min/max are both optional: 12:,6.5 means <= 6.5, 12:5.5, means >= 5.5)
        $attributeRanges = $filters['attribute_ranges'] ?? '';
        if ($attributeRanges) {
            foreach ($this->parseRangeFilters($attributeRanges) as [$attributeId, $min, $max]) {
                $builder->whereHas('productAttributes', function (Builder $query) use ($attributeId, $min, $max) {
                    $query->where('attribute_id', $attributeId);
                    if ($min !== null) {
                        $query->where('value_number', '>=', $min);
                    }
                    if ($max !== null) {
                        $query->where('value_number', '<=', $max);
                    }
                });
            }
        }

        // Variant value filters (format: variant_values=1,9|4,8)
        // Each group carries the variant value ids of ONE logical dimension
        // (e.g. Size) aggregated across products, since variant rows are
        // per-product. Groups are AND-ed, values within a group OR-ed: the
        // product qualifies when any of its SKUs carries a group value.
        $variantGroups = $filters['variant_values'] ?? '';
        if ($variantGroups) {
            foreach (explode('|', $variantGroups) as $group) {
                $valueIds = parse_int_filters($group);
                if (empty($valueIds)) {
                    continue;
                }
                $builder->whereHas('skus', function (Builder $query) use ($valueIds) {
                    $query->whereHas('variantValues', function (Builder $valueQuery) use ($valueIds) {
                        $valueQuery->whereIn('product_variant_values.id', $valueIds);
                    });
                });
            }
        }

        return $builder;
    }

    /**
     * Parse number range filter segments into [attributeId, min, max] triples.
     * Malformed segments are skipped instead of throwing, so a bad URL
     * degrades to fewer filters rather than a broken listing page.
     *
     * @param  string  $ranges
     * @return array
     */
    private function parseRangeFilters(string $ranges): array
    {
        $out = [];
        foreach (explode('|', $ranges) as $segment) {
            $parts = explode(':', $segment);
            if (count($parts) !== 2) {
                continue;
            }
            $attributeId = (int) $parts[0];
            if ($attributeId <= 0) {
                continue;
            }
            [$min, $max] = array_pad(explode(',', $parts[1]), 2, '');
            $out[]       = [
                $attributeId,
                ($min !== '' && is_numeric($min)) ? $min : null,
                ($max !== '' && is_numeric($max)) ? $max : null,
            ];
        }

        return $out;
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
        $keyword     = $filters['keyword'] ?? $filters['search'] ?? '';
        $searchField = $filters['search_field'] ?? '';

        if (! $keyword) {
            return $builder;
        }

        if ($searchField === 'name') {
            $builder->whereHas('translations', fn (Builder $query) => self::applyTranslationNameMatch($query, $keyword));
        } elseif ($searchField === 'sku_code') {
            $builder->whereHas('skus', function (Builder $query) use ($keyword) {
                $query->where('code', 'like', "%$keyword%");
            });
        } elseif ($searchField === 'spu_code') {
            $builder->where('spu_code', 'like', "%$keyword%");
        } else {
            // Search all fields. The name leg resolves through the cached
            // FULLTEXT id list when available; the small sku/spu tables stay
            // on LIKE.
            $nameProductIds = $this->cachedNameMatchProductIds($keyword);
            $builder->where(function ($query) use ($keyword, $nameProductIds) {
                if ($nameProductIds !== null) {
                    $query->whereIn('products.id', $nameProductIds);
                } else {
                    $query->whereHas('translations', fn (Builder $q) => self::applyTranslationNameMatch($q, $keyword));
                }
                $query->orWhereHas('skus', function (Builder $q) use ($keyword) {
                    $q->where('code', 'like', "%$keyword%");
                })->orWhere('spu_code', 'like', "%$keyword%");
            });
        }

        return $builder;
    }

    /**
     * Match keyword(s) against product_translations.name inside a whereHas
     * closure. Uses the ngram FULLTEXT index (ft_pt_name) in phrase mode —
     * quoted phrases OR-combine, matching a LIKE '%kw%' OR chain — and falls
     * back to LIKE when the driver lacks ngram (SQLite test connections) or
     * no keyword forms an ngram token. Keywords shorter than 2 chars cannot
     * be tokenized and are dropped when other keywords qualify (ngram has no
     * prefix search); callers that need them route them through a separate
     * LIKE pass (see BeltFrontService::matchedProductIds).
     *
     * @param  Builder  $query
     * @param  array|string  $keywords
     * @return void
     */
    public static function applyTranslationNameMatch(Builder $query, array|string $keywords): void
    {
        $keywords = array_values((array) $keywords);

        $phrases = [];
        foreach ($keywords as $keyword) {
            $term = self::fullTextTerm($keyword);
            if (mb_strlen($term) >= 2) {
                $phrases[] = '"'.$term.'"';
            }
        }

        if ($phrases !== [] && $query->getModel()->getConnection()->getDriverName() === 'mysql') {
            $query->whereRaw('MATCH (name) AGAINST (? IN BOOLEAN MODE)', [implode(' ', $phrases)]);

            return;
        }

        $query->where(function (Builder $q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $q->orWhere('name', 'like', "%{$keyword}%");
            }
        });
    }

    /**
     * Cached product IDs whose translated name matches the keyword, for the
     * front "search all fields" branch. The fulltext lookup is cheap, but
     * repeat searches resolve free instead of re-probing 136K translation
     * rows. TTL bounds staleness: renamed products become searchable within
     * an hour. An empty result means "nothing matches" (whereIn([])
     * short-circuits) — distinct from the caller's null = "no fulltext"
     * fallback.
     *
     * @param  string  $keyword
     * @return array|null Null when the keyword cannot use FULLTEXT — caller falls back to LIKE
     */
    private function cachedNameMatchProductIds(string $keyword): ?array
    {
        $term = self::fullTextTerm($keyword);
        if (mb_strlen($term) < 2 || DB::connection()->getDriverName() !== 'mysql') {
            return null;
        }

        return Cache::remember('product_search_name_'.md5($term), 3600, function () use ($term) {
            return DB::table('product_translations')
                ->whereRaw('MATCH (name) AGAINST (? IN BOOLEAN MODE)', ['"'.$term.'"'])
                ->pluck('product_id')
                ->all();
        });
    }

    /**
     * Sanitize a keyword for BOOLEAN MODE: operators like - ~ * + would flip
     * or widen the match, and double quotes are reserved for the phrase
     * wrapping. Whitespace survives — phrase search keeps multi-word
     * semantics, matching the old LIKE '%kw%' substring behavior.
     *
     * @param  string  $keyword
     * @return string
     */
    public static function fullTextTerm(string $keyword): string
    {
        return trim((string) preg_replace('/[+\-<>()~*"@]/u', ' ', $keyword));
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

        if (isset($filters['active']) && $filters['active'] !== '') {
            $builder->where('products.active', (bool) $filters['active']);
        }

        $type = $filters['type'] ?? '';
        if ($type) {
            $builder->where('products.type', $type);
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
        // Support date_filter parameter
        $dateFilter = $filters['date_filter'] ?? '';
        if ($dateFilter && $dateFilter !== 'all') {
            $now = now();
            switch ($dateFilter) {
                case 'today':
                    $builder->whereDate('products.created_at', $now->toDateString());
                    break;
                case 'this_week':
                    $builder->whereBetween('products.created_at', [
                        $now->startOfWeek()->toDateTimeString(),
                        $now->endOfWeek()->toDateTimeString(),
                    ]);
                    break;
                case 'this_month':
                    $builder->whereBetween('products.created_at', [
                        $now->startOfMonth()->toDateTimeString(),
                        $now->endOfMonth()->toDateTimeString(),
                    ]);
                    break;
                case 'custom':
                    $startDate = $filters['start_date'] ?? '';
                    $endDate   = $filters['end_date'] ?? '';
                    if ($startDate) {
                        $builder->whereDate('products.created_at', '>=', $startDate);
                    }
                    if ($endDate) {
                        $builder->whereDate('products.created_at', '<=', $endDate);
                    }
                    break;
            }
        }

        // Support legacy date range parameters
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
