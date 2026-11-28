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
 * @phpstan-consistent-constructor
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
                $brandName = is_object($brand) && method_exists($brand, 'fallbackName')
                    ? $brand->fallbackName()
                    : (is_object($brand) ? $brand->name : $brand['name'] ?? '');

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
     * Process attribute data. Select-type attributes render as value
     * checkboxes, number-type attributes render as a min/max range facet.
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
        $allRanges           = $request->get('attribute_ranges', []);
        foreach ($attributes as $attribute) {
            try {
                $attributeId     = is_object($attribute) ? $attribute->id : $attribute['id'] ?? null;
                $attributeName   = is_object($attribute) ? $attribute->name : $attribute['name'] ?? '';
                $attributeType   = is_object($attribute) ? ($attribute->type ?? 'select') : ($attribute['type'] ?? 'select');
                $attributeValues = is_object($attribute) ? $attribute->values : $attribute['values'] ?? [];

                if (! $attributeId || ! $attributeName) {
                    continue;
                }

                if ($attributeType === 'number') {
                    $range       = (string) ($allRanges[$attributeId] ?? '');
                    [$min, $max] = array_pad(explode(',', $range), 2, '');

                    $processedAttributes[] = [
                        'id'     => $attributeId,
                        'name'   => $attributeName,
                        'type'   => 'number',
                        'min'    => $min,
                        'max'    => $max,
                        'values' => [],
                    ];

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
                        'type'   => 'select',
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
     * Build the variant dimension facet. Variant rows and values are
     * per-product, so the same display name (e.g. "Size = 8") aggregates
     * every matching value id across products; selecting a value filters by
     * the full id group via the variant_values URL parameter.
     *
     * @param  mixed  $variants  Variants of the category products, with translations loaded
     * @param  Request  $request
     * @return array
     */
    public function processVariants($variants, Request $request): array
    {
        if (! isset($variants) || ! is_countable($variants) || count($variants) === 0) {
            return [];
        }

        $selectedIds = [];
        foreach (explode('|', (string) $request->get('variant_values', '')) as $group) {
            // Each group is a comma-separated list of value ids; reuse
            // parse_int_filters (which splits on '|') by translating commas.
            foreach (parse_int_filters(str_replace(',', '|', $group)) as $id) {
                $selectedIds[$id] = true;
            }
        }

        $dimensions = [];
        foreach ($variants as $variant) {
            $dimName = $variant->translation->name ?? '';
            if ($dimName === '') {
                continue;
            }
            if (! isset($dimensions[$dimName])) {
                $dimensions[$dimName] = [];
            }

            foreach ($variant->values as $value) {
                $valueName = $value->translation->name ?? '';
                if ($valueName === '') {
                    continue;
                }
                if (! isset($dimensions[$dimName][$valueName])) {
                    $dimensions[$dimName][$valueName] = ['ids' => [], 'selected' => false];
                }
                $dimensions[$dimName][$valueName]['ids'][] = $value->id;
                if (! empty($selectedIds[$value->id])) {
                    $dimensions[$dimName][$valueName]['selected'] = true;
                }
            }
        }

        $out = [];
        foreach ($dimensions as $dimName => $values) {
            $valuesOut = [];
            foreach ($values as $valueName => $info) {
                $valuesOut[] = [
                    'name'      => $valueName,
                    'value_ids' => implode(',', array_unique($info['ids'])),
                    'selected'  => $info['selected'],
                ];
            }
            $out[] = [
                'name'   => $dimName,
                'values' => $valuesOut,
            ];
        }

        return $out;
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
