<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use Illuminate\Support\Facades\Log;
use InnoShop\Common\Repositories\ProductRepo;
use InvalidArgumentException;

class ProductUpdateTool extends BaseTool
{
    protected bool $write = true;

    public function name(): string
    {
        return 'product_update';
    }

    public function description(): string
    {
        return '⚠️ WRITE: Update an existing product using PATCH semantics. Supports updating variants (规格) and multi-SKU definitions. Set variants+skus together to replace variant structure. SEO TDK (meta_title/meta_description/meta_keywords) and selling_point MUST be set via the translations parameter.';
    }

    public function inputSchema(): array
    {
        return [
            'type'                 => 'object',
            'additionalProperties' => true,
            'properties'           => [
                'id'           => ['type' => 'integer', 'description' => 'Product ID (required)'],
                'name'         => ['type' => 'string', 'description' => 'New product name (default locale)'],
                'slug'         => ['type' => 'string', 'description' => 'New URL slug'],
                'active'       => ['type' => 'boolean', 'description' => 'Active status'],
                'brand_id'     => ['type' => 'integer', 'description' => 'New brand ID'],
                'images'       => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Product image URLs'],
                'summary'      => ['type' => 'string', 'description' => 'Short description (default locale)'],
                'content'      => ['type' => 'string', 'description' => 'Full description HTML (default locale)'],
                'weight'       => ['type' => 'number', 'description' => 'Product weight'],
                'weight_class' => ['type' => 'string', 'description' => 'Weight unit, e.g. kg, g, lb, oz'],
                'categories'   => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Category IDs to assign'],
                'translations' => self::translationsSchema([
                    'name'             => 'Product name in this locale. Required when adding a new locale.',
                    'summary'          => 'Short description in this locale',
                    'content'          => 'Full description HTML in this locale',
                    'selling_point'    => 'Selling point (卖点) in this locale',
                    'meta_title'       => 'SEO meta title in this locale',
                    'meta_description' => 'SEO meta description in this locale',
                    'meta_keywords'    => 'SEO meta keywords, comma separated, in this locale',
                ], 'Per-locale overrides keyed by locale code (multilingual), e.g. {"en": {"meta_title": "SEO Title"}, "zh-cn": {"name": "产品名", "meta_title": "SEO 标题"}}. Each locale key maps to an entry with the fields below. Adding a new locale requires name. Same-locale entries override top-level fields.'),
                'variants' => [
                    'type'        => 'array',
                    'description' => 'Variant dimensions (规格) to replace existing. Provide with skus to restructure product variants.',
                    'items'       => [
                        'type'       => 'object',
                        'properties' => [
                            'name'   => ['type' => 'string', 'description' => 'Variant dimension name'],
                            'values' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Value names for this dimension'],
                        ],
                    ],
                ],
                'skus' => [
                    'type'        => 'array',
                    'description' => 'SKU definitions to replace existing SKUs. Must provide variants when setting skus.',
                    'items'       => [
                        'type'       => 'object',
                        'properties' => [
                            'code'           => ['type' => 'string', 'description' => 'SKU code'],
                            'price'          => ['type' => 'number', 'description' => 'Sale price'],
                            'origin_price'   => ['type' => 'number', 'description' => 'Original price'],
                            'quantity'       => ['type' => 'integer', 'description' => 'Stock quantity'],
                            'is_default'     => ['type' => 'boolean', 'description' => 'Default SKU'],
                            'variant_values' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Variant value names matching variants order'],
                        ],
                    ],
                ],
            ],
            'required' => ['id'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'products_update';
    }

    public function execute(array $arguments): mixed
    {
        Log::info('ProductUpdateTool::execute', ['arguments' => $arguments]);

        $product = ProductRepo::getInstance()->builder([])->find((int) ($arguments['id'] ?? 0));
        if (! $product) {
            throw new InvalidArgumentException("Product [{$arguments['id']}] not found.");
        }

        $data   = [];
        $locale = locale_code();

        foreach (['slug', 'active', 'brand_id', 'images', 'weight', 'weight_class'] as $key) {
            if (array_key_exists($key, $arguments)) {
                $data[$key] = $arguments[$key];
            }
        }

        // Translation (default locale)
        $hasTranslation = false;
        $translation    = ['locale' => $locale];
        if (array_key_exists('name', $arguments)) {
            $translation['name'] = $arguments['name'];
            $hasTranslation      = true;
        }
        if (array_key_exists('summary', $arguments)) {
            $translation['summary'] = $arguments['summary'];
            $hasTranslation         = true;
        }
        if (array_key_exists('content', $arguments)) {
            $translation['content'] = $arguments['content'];
            $hasTranslation         = true;
        }
        // Keyed by locale so same-locale entries merge; the translations
        // param overrides top-level (default-locale) fields on conflict.
        $translations = [];
        if ($hasTranslation) {
            $translations[$locale] = $translation;
        }
        if ($extra = $arguments['translations'] ?? []) {
            // New locale rows require a name (NOT NULL column); existing locales accept partial updates.
            $existingLocales   = $product->translations()->pluck('locale')->all();
            $translationFields = ['name', 'summary', 'content', 'selling_point', ...self::META_FIELDS];
            foreach ((array) $extra as $loc => $fields) {
                if (! is_array($fields)) {
                    continue;
                }
                $t = array_intersect_key($fields, array_flip($translationFields));
                if ($t && (in_array($loc, $existingLocales) || ! empty($t['name']))) {
                    $translations[$loc] = array_merge($translations[$loc] ?? ['locale' => $loc], $t);
                }
            }
        }
        if ($translations) {
            $data['translations'] = array_values($translations);
        }

        if (array_key_exists('categories', $arguments)) {
            $data['categories'] = array_map('intval', (array) $arguments['categories']);
        }

        // Handle variants + multi-SKU (replaces existing variant/SKU structure)
        $variantDefs = $arguments['variants'] ?? [];
        $inputSkus   = $arguments['skus'] ?? [];

        if (! empty($variantDefs) && ! empty($inputSkus)) {
            $data['variants'] = $this->buildVariablesFormat($variantDefs, $locale);
            $data['skus']     = $this->buildSkusWithVariants($inputSkus, $variantDefs);
        } elseif (! empty($data)) {
            // Single-field updates: only update default SKU price/quantity
            $skuUpdates = [];
            foreach (['price', 'origin_price', 'quantity'] as $key) {
                if (array_key_exists($key, $arguments)) {
                    $skuUpdates[$key] = $arguments[$key];
                }
            }
            if (! empty($skuUpdates)) {
                $defaultSku = $product->skus->where('is_default', true)->first();
                if ($defaultSku) {
                    $defaultSku->update($skuUpdates);
                }
            }
        }

        if (empty($data) && empty($inputSkus)) {
            Log::warning('ProductUpdateTool: No fields to update', [
                'data'      => $data,
                'inputSkus' => $inputSkus,
                'arguments' => $arguments,
            ]);
            throw new InvalidArgumentException('No fields to update.');
        }

        if (! empty($data)) {
            ProductRepo::getInstance()->patch($product, $data);
        }

        $product->refresh()->load('translation', 'translations', 'skus', 'variants.translation', 'variants.values.translation');

        return [
            'id'               => $product->id,
            'name'             => $product->translation->name ?? '',
            'slug'             => $product->slug,
            'url'              => $product->url,
            'images'           => $product->images ?? [],
            'price'            => $product->masterSku?->price ?? 0,
            'active'           => (bool) $product->active,
            'is_multiple'      => $product->isMultiple(),
            'meta_title'       => $product->translation->meta_title ?? '',
            'meta_description' => $product->translation->meta_description ?? '',
            'meta_keywords'    => $product->translation->meta_keywords ?? '',
            'translations'     => $product->translations->map(fn ($t) => [
                'locale'           => $t->locale,
                'name'             => $t->name ?? '',
                'summary'          => $t->summary ?? '',
                'content'          => $t->content ?? '',
                'selling_point'    => $t->selling_point ?? '',
                'meta_title'       => $t->meta_title ?? '',
                'meta_description' => $t->meta_description ?? '',
                'meta_keywords'    => $t->meta_keywords ?? '',
            ])->values()->all(),
            'skus' => $product->skus->map(fn ($sku) => [
                'id'            => $sku->id,
                'code'          => $sku->code,
                'price'         => (float) $sku->price,
                'origin_price'  => (float) ($sku->origin_price ?? 0),
                'quantity'      => (int) $sku->quantity,
                'is_default'    => (bool) $sku->is_default,
                'variant_label' => $sku->variant_label,
            ])->values()->all(),
            'variants' => $product->variants->map(fn ($v) => [
                'id'     => $v->id,
                'name'   => $v->translation->name ?? '',
                'values' => $v->values->map(fn ($val) => [
                    'id'   => $val->id,
                    'name' => $val->translation->name ?? '',
                ])->values()->all(),
            ])->values()->all(),
        ];
    }

    private function buildVariablesFormat(array $variantDefs, string $locale): array
    {
        $variables = [];
        $nextId    = 0;
        foreach ($variantDefs as $v) {
            $name   = $v['name'] ?? '';
            $values = $v['values'] ?? [];
            $entry  = [
                'name'   => is_array($name) ? $name : [$locale => $name],
                'values' => [],
            ];
            foreach ($values as $val) {
                $entry['values'][] = [
                    'id'   => $nextId++,
                    'name' => is_array($val) ? $val : [$locale => $val],
                ];
            }
            $variables[] = $entry;
        }

        return $variables;
    }

    private function buildSkusWithVariants(array $skus, array $variantDefs): array
    {
        $result = [];
        foreach ($skus as $idx => $sku) {
            $variantValues = $sku['variant_values'] ?? [];
            $localIds      = [];
            foreach ($variantValues as $vi => $valName) {
                $varValues = $variantDefs[$vi]['values'] ?? [];
                $found     = false;
                foreach ($varValues as $pi => $pv) {
                    if (is_array($pv) ? in_array($valName, $pv) : $pv === $valName) {
                        $localIds[] = $pi;
                        $found      = true;
                        break;
                    }
                }
                if (! $found) {
                    $localIds[] = 0;
                }
            }
            // Map per-variant local indices to global sequential client IDs
            $globalIds = [];
            $idOffset  = 0;
            foreach ($variantDefs as $vi => $vd) {
                $localIdx    = $localIds[$vi] ?? 0;
                $globalIds[] = $idOffset + $localIdx;
                $idOffset += count($vd['values'] ?? []);
            }
            $entry = [
                'code'              => $sku['code'] ?? '',
                'price'             => (float) ($sku['price'] ?? 0),
                'quantity'          => (int) ($sku['quantity'] ?? 0),
                'is_default'        => $idx === 0 || (bool) ($sku['is_default'] ?? false),
                'position'          => (int) ($sku['position'] ?? $idx),
                'variant_value_ids' => $globalIds,
            ];
            if ($originPrice = $sku['origin_price'] ?? 0) {
                $entry['origin_price'] = (float) $originPrice;
            }
            $result[] = $entry;
        }

        return $result;
    }
}
