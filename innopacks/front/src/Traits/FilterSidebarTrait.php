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
use InnoShop\Common\Services\FilterSidebarService;

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
