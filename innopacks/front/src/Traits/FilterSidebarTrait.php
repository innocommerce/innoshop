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
     * @return array Returns processed filter data array
     */
    protected function getFilterSidebarData(Request $request): array
    {
        // Get brand data
        $brands = BrandRepo::getInstance()->withActive()->all();

        // Get attribute data
        $attributes = AttributeRepo::getInstance()->getAttributesWithValues();

        // Use FilterSidebarService to process filter data
        $filterSidebarService = FilterSidebarService::getInstance();

        return [
            'brands'        => $filterSidebarService->processBrands($brands, $request),
            'attributes'    => $filterSidebarService->processAttributes($attributes, $request),
            'availability'  => $filterSidebarService->processAvailability($request),
            'price_filters' => $filterSidebarService->getPriceFilters($request),
        ];
    }
}
