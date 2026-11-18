<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Traits;

use Illuminate\Http\Request;
use InnoShop\Common\Models\Product\Variant;
use InnoShop\Common\Repositories\AttributeRepo;
use InnoShop\Common\Repositories\BrandRepo;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Services\FilterSidebarService;
use InnoShop\Common\Services\RequestFilterParser;
use Throwable;

/**
 * Filter sidebar data processing Trait
 * Encapsulates repetitive filter data processing logic in controllers
 */
trait FilterSidebarTrait
{
    /**
     * Get filter sidebar data
     *
     * @param  Request  $request  HTTP request object
     * @param  int|null  $categoryId  When given, adds the variant dimension facet of the category products
     * @return array Returns processed filter data array
     */
    protected function getFilterSidebarData(Request $request, ?int $categoryId = null): array
    {
        // Get brand data (eager-load translations so fallbackName resolves per locale)
        $brands = BrandRepo::getInstance()->withActive()->all()->load('translations');

        // Prune brand list to brands that have at least one active product
        // matching the current Request filter context (category / keyword /
        // price / attribute / variant / stock). When the lookup yields no
        // brand ids, the current filter context has no branded products —
        // collapse to empty so the view layer can drop the whole brand card
        // (AC6). Silently skip the prune on DB hiccup (B5).
        try {
            $usedBrandIds = $this->getUsedBrandIds($request, $categoryId);
            if (empty($usedBrandIds)) {
                $brands = collect();
            } else {
                $brands = $brands->filter(fn ($brand) => in_array((int) $brand->id, $usedBrandIds, true));
            }
        } catch (Throwable) {
            // B5 fallback — keep the full active-brand list.
        }

        // Get storefront-filterable attribute data
        $attributes = AttributeRepo::getInstance()->getAttributesWithValues(filterableOnly: true);

        // Use FilterSidebarService to process filter data
        $filterSidebarService = FilterSidebarService::getInstance();

        return [
            'brands'        => $filterSidebarService->processBrands($brands, $request),
            'attributes'    => $filterSidebarService->processAttributes($attributes, $request),
            'variants'      => $categoryId ? $filterSidebarService->processVariants($this->getCategoryVariants($categoryId), $request) : [],
            'availability'  => $filterSidebarService->processAvailability($request),
            'price_filters' => $filterSidebarService->getPriceFilters($request),
        ];
    }

    /**
     * Brand ids that have at least one active product matching the current
     * Request filter context. brand_ids is intentionally excluded from the
     * lookup so the URL selection never self-trims the sidebar (B3).
     *
     * Returns an empty array on no-match so the calling site can keep the
     * original list intact — see B5 in the issue body.
     *
     * @param  Request  $request
     * @param  int|null  $categoryId
     * @return array<int>
     */
    protected function getUsedBrandIds(Request $request, ?int $categoryId = null): array
    {
        $filters = (new RequestFilterParser)->extractFilters($request);

        // B3 — see issue body. Self-trim would silently drop every brand
        // outside the current selection and the customer could never
        // discover the others.
        unset($filters['brand_ids']);

        if ($categoryId) {
            $filters['category_id'] = $categoryId;
        }

        return ProductRepo::getInstance()
            ->withActive()
            ->builder($filters)
            ->whereNotNull('brand_id')
            ->distinct()
            ->pluck('brand_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Variant dimensions of all products under the category, used for the
     * cross-product variant facet (variant rows are per-product).
     *
     * @param  int  $categoryId
     * @return mixed
     */
    private function getCategoryVariants(int $categoryId): mixed
    {
        return Variant::query()
            ->whereHas('product.categories', function ($query) use ($categoryId) {
                $query->where('categories.id', $categoryId);
            })
            ->with(['translation', 'values.translation'])
            ->get();
    }
}
