<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use Illuminate\Support\Str;
use InnoShop\Common\Repositories\ProductRepo;
use InvalidArgumentException;

class ProductCreateTool extends BaseTool
{
    protected bool $write = true;

    public function name(): string
    {
        return 'product_create';
    }

    public function description(): string
    {
        return '⚠️ WRITE: Create a new product with optional multi-variant SKUs (规格 like color-size combos). Supports variants, multiple SKUs with different prices/stock per combo, brand, categories, images. SEO TDK (meta_title/meta_description/meta_keywords) and selling_point MUST be set via the translations parameter.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'name'         => ['type' => 'string', 'description' => 'Product name (default locale)'],
                'slug'         => ['type' => 'string', 'description' => 'URL slug, auto-generated from name if empty'],
                'price'        => ['type' => 'number', 'description' => 'Default sale price (used when no SKUs specified)'],
                'origin_price' => ['type' => 'number', 'description' => 'Original / compare-at price'],
                'quantity'     => ['type' => 'integer', 'description' => 'Default stock quantity (used when no SKUs specified)'],
                'sku_code'     => ['type' => 'string', 'description' => 'Default SKU code, auto-generated if empty (used when no SKUs specified)'],
                'active'       => ['type' => 'boolean', 'description' => 'Active status, default true'],
                'brand_id'     => ['type' => 'integer', 'description' => 'Brand ID'],
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
                ], 'Additional locale translations (multilingual), e.g. {"zh-cn": {"name": "产品名", "meta_title": "SEO 标题"}}. Each locale key maps to an entry with the fields below. Entries for the default locale merge over top-level fields (no duplicate row).'),
                'variants' => [
                    'type'        => 'array',
                    'description' => 'Variant dimensions (规格) like Color, Size. Each dimension has a name and list of value names.',
                    'items'       => [
                        'type'       => 'object',
                        'properties' => [
                            'name'   => ['type' => 'string', 'description' => 'Variant dimension name: string for single locale, or {"en":"Color","zh-cn":"颜色"} for multi-locale'],
                            'values' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Value names: strings or [{"en":"Black","zh-cn":"黑色"}, ...] for multi-locale'],
                        ],
                    ],
                ],
                'skus' => [
                    'type'        => 'array',
                    'description' => 'SKU definitions with variant mappings. Each SKU specifies which variant values it combines.',
                    'items'       => [
                        'type'       => 'object',
                        'properties' => [
                            'code'           => ['type' => 'string', 'description' => 'SKU code, auto-generated if empty'],
                            'price'          => ['type' => 'number', 'description' => 'This SKU sale price'],
                            'origin_price'   => ['type' => 'number', 'description' => 'This SKU original price'],
                            'quantity'       => ['type' => 'integer', 'description' => 'This SKU stock quantity'],
                            'is_default'     => ['type' => 'boolean', 'description' => 'Default SKU, first SKU is default if omitted'],
                            'variant_values' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Variant value names matching the variants order. e.g. ["黑色", "M"] means Color=黑色, Size=M'],
                        ],
                    ],
                ],
            ],
            'required' => ['name'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'products_create';
    }

    public function execute(array $arguments): mixed
    {
        $name   = (string) ($arguments['name'] ?? '');
        $active = array_key_exists('active', $arguments) ? (bool) $arguments['active'] : true;

        if ($name === '') {
            throw new InvalidArgumentException('Product name is required.');
        }

        $locale = locale_code();
        $data   = [
            'slug'         => (string) ($arguments['slug'] ?? Str::slug($name)),
            'active'       => $active,
            'translations' => [],
            'skus'         => [],
        ];

        // Basic fields
        if ($brandId = $arguments['brand_id'] ?? 0) {
            $data['brand_id'] = (int) $brandId;
        }
        if ($images = $arguments['images'] ?? []) {
            $data['images'] = (array) $images;
        }
        if (isset($arguments['weight'])) {
            $data['weight'] = (float) $arguments['weight'];
        }
        if ($weightClass = $arguments['weight_class'] ?? '') {
            $data['weight_class'] = $weightClass;
        }
        if ($categories = $arguments['categories'] ?? []) {
            $data['categories'] = array_map('intval', (array) $categories);
        }

        // Translation (default locale)
        $translation = ['locale' => $locale, 'name' => $name];
        if ($summary = $arguments['summary'] ?? '') {
            $translation['summary'] = $summary;
        }
        if ($content = $arguments['content'] ?? '') {
            $translation['content'] = $content;
        }
        // Keyed by locale: an entry for the default locale in `translations`
        // merges over (overrides) the top-level fields instead of duplicating the row.
        $byLocale = [$locale => $translation];

        if ($extra = $arguments['translations'] ?? []) {
            $translationFields = ['name', 'summary', 'content', 'selling_point', ...self::META_FIELDS];
            foreach ((array) $extra as $loc => $fields) {
                if (! is_array($fields)) {
                    continue;
                }
                $t = array_intersect_key($fields, array_flip($translationFields));
                // New locale rows require a name; the default-locale entry already has one.
                if ($t && ($loc === $locale || ! empty($t['name']))) {
                    $byLocale[$loc] = array_merge($byLocale[$loc] ?? ['locale' => $loc], $t);
                }
            }
        }
        $data['translations'] = array_values($byLocale);

        // Variants + SKUs
        $variantDefs = $arguments['variants'] ?? [];
        $inputSkus   = $arguments['skus'] ?? [];

        if (! empty($variantDefs) && ! empty($inputSkus)) {
            // Multi-variant product
            $data['variables'] = $this->buildVariablesFormat($variantDefs, $locale);
            $data['skus']      = $this->buildSkusWithVariants($inputSkus, $variantDefs);
        } else {
            // Single SKU product
            $price = (float) ($arguments['price'] ?? 0);
            $sku   = [
                'code'       => (string) ($arguments['sku_code'] ?? Str::upper(Str::random(8))),
                'price'      => $price,
                'quantity'   => (int) ($arguments['quantity'] ?? 0),
                'is_default' => true,
                'position'   => 0,
            ];
            if ($originPrice = $arguments['origin_price'] ?? 0) {
                $sku['origin_price'] = (float) $originPrice;
            }
            $data['skus'][] = $sku;
        }

        $product = ProductRepo::getInstance()->create($data);

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

    /**
     * Convert AI-friendly variant format to repo variables format.
     * Input:  [{"name":"颜色","values":["黑","白"]},{"name":"尺寸","values":["M","L"]}]
     * Output: [{"name":{"zh-cn":"颜色"},"values":[{"name":{"zh-cn":"黑"}},{"name":{"zh-cn":"白"}}]},...]
     */
    /**
     * Convert AI-friendly variant format to repo variables format.
     * name supports: string (single locale) or object {"en":"Color","zh-cn":"颜色"}
     * values supports: [string, ...] or [{"en":"Black","zh-cn":"黑色"}, ...]
     * Each value gets a client-side id for SKU variant_value_ids mapping.
     */
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

    /**
     * Convert AI-friendly SKUs to repo format with positional variant indices.
     * Input:  [{"variant_values":["黑","M"],"price":99,"quantity":50}, ...]
     * Output: [{"variants":[0,0],"price":99,"quantity":50}, ...]
     */
    private function buildSkusWithVariants(array $skus, array $variantDefs): array
    {
        $result = [];
        foreach ($skus as $idx => $sku) {
            $variantValues = $sku['variant_values'] ?? [];
            $positions     = [];
            $clientIds     = [];
            foreach ($variantValues as $vi => $valName) {
                $varValues = $variantDefs[$vi]['values'] ?? [];
                $found     = false;
                foreach ($varValues as $pi => $pv) {
                    if (is_array($pv) ? in_array($valName, $pv) : $pv === $valName) {
                        $clientIds[] = $pi; // Value index within this variant = client ID
                        $found       = true;
                        break;
                    }
                }
                if (! $found) {
                    $clientIds[] = 0;
                }
            }
            // Map per-variant value indices to global sequential client IDs
            $globalIds = [];
            $idOffset  = 0;
            foreach ($variantDefs as $vi => $vd) {
                $localIdx    = $clientIds[$vi] ?? 0;
                $globalIds[] = $idOffset + $localIdx;
                $idOffset += count($vd['values'] ?? []);
            }
            $entry = [
                'code'              => $sku['code'] ?? ('SKU-'.Str::upper(Str::random(6))),
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
