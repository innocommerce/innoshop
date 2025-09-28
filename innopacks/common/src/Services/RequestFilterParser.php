<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Illuminate\Http\Request;

/**
 * Request filter parameter parser
 * Responsible for extracting and parsing various filter parameters from HTTP requests
 */
class RequestFilterParser
{
    /**
     * Extract and process filter parameters from request
     *
     * @param  Request  $request
     * @param  array  $defaultFilters
     * @return array
     */
    public function extractFilters(Request $request, array $defaultFilters = []): array
    {
        $filters = array_merge($defaultFilters, [
            'keyword'  => $request->get('keyword'),
            'sort'     => $request->get('sort', 'position'),
            'order'    => $request->get('order', 'asc'),
            'per_page' => $request->get('per_page', 15),
        ]);

        // Price filters
        $filters = $this->addPriceFilters($request, $filters);

        // Brand filters
        $filters = $this->addBrandFilters($request, $filters);

        // Attribute filters
        $filters = $this->addAttributeFilters($request, $filters);

        // Stock status filters
        $filters = $this->addAvailabilityFilters($request, $filters);

        return $filters;
    }

    /**
     * Add price filter parameters
     *
     * @param  Request  $request
     * @param  array  $filters
     * @return array
     */
    private function addPriceFilters(Request $request, array $filters): array
    {
        $minPrice = $request->get('min_price');
        $maxPrice = $request->get('max_price');

        if ($minPrice) {
            $filters['price_start'] = $minPrice;
        }
        if ($maxPrice) {
            $filters['price_end'] = $maxPrice;
        }

        return $filters;
    }

    /**
     * Add brand filter parameters
     *
     * @param  Request  $request
     * @param  array  $filters
     * @return array
     */
    private function addBrandFilters(Request $request, array $filters): array
    {
        $brands = $request->get('brands');
        if ($brands) {
            $brandIds = is_string($brands) ? explode(',', $brands) : $brands;
            if (! empty($brandIds)) {
                $filters['brand_ids'] = $brandIds;
            }
        }

        return $filters;
    }

    /**
     * Add attribute filter parameters
     *
     * @param  Request  $request
     * @param  array  $filters
     * @return array
     */
    private function addAttributeFilters(Request $request, array $filters): array
    {
        $attributes = $request->get('attributes');
        if ($attributes && is_array($attributes)) {
            $attrFilters = [];
            foreach ($attributes as $attributeId => $values) {
                $valueIds = is_string($values) ? explode(',', $values) : $values;
                if (! empty($valueIds)) {
                    $attrFilters[] = $attributeId.':'.implode(',', $valueIds);
                }
            }
            if (! empty($attrFilters)) {
                $filters['attr'] = implode('|', $attrFilters);
            }
        }

        return $filters;
    }

    /**
     * Add stock status filter parameters
     *
     * @param  Request  $request
     * @param  array  $filters
     * @return array
     */
    private function addAvailabilityFilters(Request $request, array $filters): array
    {
        $availability = $request->get('availability');
        if ($availability) {
            $availabilityValues = is_string($availability) ? explode(',', $availability) : $availability;
            if (! empty($availabilityValues)) {
                if (in_array('out_of_stock', $availabilityValues) && ! in_array('in_stock', $availabilityValues)) {
                    $filters['stock_status'] = 'out_of_stock';
                } elseif (in_array('in_stock', $availabilityValues) && ! in_array('out_of_stock', $availabilityValues)) {
                    $filters['stock_status'] = 'in_stock';
                }
            }
        }

        return $filters;
    }

    /**
     * Ensure value is in array format
     *
     * @param  mixed  $value
     * @return array
     */
    public function ensureArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value) && ! empty($value)) {
            return explode(',', $value);
        }

        return [];
    }
}
