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
 * Filter sidebar service class
 * Responsible for preparing display data for the frontend filter sidebar
 */
class FilterSidebarService
{
    private RequestFilterParser $filterParser;

    public function __construct()
    {
        $this->filterParser = new RequestFilterParser;
    }

    /**
     * Get service instance
     *
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * Process brand data
     *
     * @param  mixed  $brands
     * @param  Request  $request
     * @return array
     */
    public function processBrands($brands, Request $request): array
    {
        if (! isset($brands) || ! is_countable($brands) || count($brands) === 0) {
            return [];
        }

        $selectedBrands  = $this->filterParser->ensureArray($request->get('brands'));
        $processedBrands = [];

        foreach ($brands as $brand) {
            try {
                $brandId   = is_object($brand) ? $brand->id : $brand['id'] ?? null;
                $brandName = is_object($brand) ? $brand->name : $brand['name'] ?? '';

                if ($brandId && $brandName) {
                    $processedBrands[] = [
                        'id'       => $brandId,
                        'name'     => $brandName,
                        'selected' => in_array($brandId, $selectedBrands),
                    ];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $processedBrands;
    }

    /**
     * Process attribute data
     *
     * @param  mixed  $attributes
     * @param  Request  $request
     * @return array
     */
    public function processAttributes($attributes, Request $request): array
    {
        if (! isset($attributes) || ! is_countable($attributes) || count($attributes) === 0) {
            return [];
        }

        $processedAttributes = [];
        $allAttributes       = $request->get('attributes', []);
        foreach ($attributes as $attribute) {
            try {
                $attributeId     = is_object($attribute) ? $attribute->id : $attribute['id'] ?? null;
                $attributeName   = is_object($attribute) ? $attribute->name : $attribute['name'] ?? '';
                $attributeValues = is_object($attribute) ? $attribute->values : $attribute['values'] ?? [];

                if (! $attributeId || ! $attributeName) {
                    continue;
                }

                $selectedAttributes = $this->filterParser->ensureArray($allAttributes[$attributeId] ?? []);
                $processedValues    = [];

                foreach ($attributeValues as $value) {
                    try {
                        $valueId   = is_object($value) ? $value->id : $value['id'] ?? null;
                        $valueName = is_object($value) ? $value->name : $value['name'] ?? '';

                        if ($valueId && $valueName) {
                            $processedValues[] = [
                                'id'       => $valueId,
                                'name'     => $valueName,
                                'selected' => in_array($valueId, $selectedAttributes),
                            ];
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                if (! empty($processedValues)) {
                    $processedAttributes[] = [
                        'id'     => $attributeId,
                        'name'   => $attributeName,
                        'values' => $processedValues,
                    ];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $processedAttributes;
    }

    /**
     * Process stock status data
     *
     * @param  Request  $request
     * @return array
     */
    public function processAvailability(Request $request): array
    {
        $selectedAvailability = $this->filterParser->ensureArray($request->get('availability'));

        return [
            'in_stock'     => in_array('in_stock', $selectedAvailability),
            'out_of_stock' => in_array('out_of_stock', $selectedAvailability),
        ];
    }

    /**
     * Get price filter data
     *
     * @param  Request  $request
     * @return array
     */
    public function getPriceFilters(Request $request): array
    {
        return [
            'min_price' => $request->get('min_price', ''),
            'max_price' => $request->get('max_price', ''),
        ];
    }
}
