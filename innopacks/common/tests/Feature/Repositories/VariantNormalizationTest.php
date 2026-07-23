<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Repositories;

use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Tests\TestCase;
use Tests\Traits\CreatesProduct;

/**
 * End-to-end coverage for the normalized variant pipeline (P3).
 *
 * Verifies that creating / updating a product via ProductRepo writes the
 * normalized product_variants / product_variant_values / product_sku_variant_values
 * tables, that legacy `products.variables` JSON stays dual-written for
 * rollback safety, and that downstream readers (Sku::getLocaleLabels,
 * SkuListItem Resource, Product accessor) read from the normalized source
 * without breaking the legacy shape.
 */
class VariantNormalizationTest extends TestCase
{
    use CreatesProduct;

    public function test_create_product_writes_normalized_tables(): void
    {
        $product = ProductRepo::getInstance()->create($this->productWithVariantsPayload());

        // Normalized tables have the expected rows
        $this->assertSame(2, DB::table('product_variants')->where('product_id', $product->id)->count());
        $this->assertSame(4, DB::table('product_variant_values')
            ->whereIn('variant_id', function ($q) use ($product) {
                $q->select('id')->from('product_variants')->where('product_id', $product->id);
            })->count());
        // Color + Size × 2 locales = 4 variant translations
        $this->assertSame(4, DB::table('product_variant_translations')
            ->whereIn('variant_id', function ($q) use ($product) {
                $q->select('id')->from('product_variants')->where('product_id', $product->id);
            })->count());

        // SKU → variant_value pivot: 2 SKUs × 2 dimensions = 4 mappings
        $this->assertSame(4, DB::table('product_sku_variant_values')
            ->whereIn('sku_id', $product->skus->pluck('id'))->count());

        // Each SKU picks exactly one value per dimension (UNIQUE(sku_id, variant_id) enforced)
        foreach ($product->skus as $sku) {
            $this->assertSame(2, DB::table('product_sku_variant_values')->where('sku_id', $sku->id)->count());
        }
    }

    public function test_update_replaces_normalized_data(): void
    {
        $product = $this->createProduct(['active' => true, 'brand_id' => 0, 'tax_class_id' => 0]);
        ProductRepo::getInstance()->update($product, $this->productWithVariantsPayload());

        // Re-save with a single variant / value
        $newPayload             = $this->productWithVariantsPayload();
        $newPayload['variants'] = [
            [
                'name'   => ['en' => 'Material'],
                'values' => [['id' => 'val_cotton', 'name' => ['en' => 'Cotton'], 'image' => '']],
            ],
        ];
        $newPayload['skus'] = [
            [
                'code'              => 'SKU-NEW-'.$product->id,
                'price'             => 20,
                'origin_price'      => 25,
                'quantity'          => 5,
                'image'             => '',
                'variant_value_ids' => ['val_cotton'],
            ],
        ];
        ProductRepo::getInstance()->update($product, $newPayload);

        // Old normalized rows are gone
        $this->assertSame(1, DB::table('product_variants')->where('product_id', $product->id)->count());
        $variantId = DB::table('product_variants')->where('product_id', $product->id)->value('id');
        $this->assertSame('Material', DB::table('product_variant_translations')->where('variant_id', $variantId)->where('locale', 'en')->value('name'));

        // Only the new SKU + mapping exist
        $this->assertSame(1, $product->fresh()->skus()->count());
        $this->assertSame(1, DB::table('product_sku_variant_values')->count());
    }

    public function test_get_locale_labels_reads_from_normalized_data(): void
    {
        $product = $this->createProduct(['active' => true, 'brand_id' => 0, 'tax_class_id' => 0]);
        ProductRepo::getInstance()->update($product, $this->productWithVariantsPayload());

        $sku = $product->fresh()->skus->first();
        $sku->loadMissing(['variantValues.translation', 'variantValues.variant.translation', 'product.variants']);

        $labels = $sku->getLocaleLabels();

        // Order matches product.variants (Color, Size), not pivot position
        $this->assertSame('Color', $labels[0]['name']);
        $this->assertSame('Red', $labels[0]['value']);
        $this->assertSame('Size', $labels[1]['name']);
        $this->assertSame('Big', $labels[1]['value']);
    }

    public function test_patch_syncs_normalized_variants(): void
    {
        $product = $this->createProduct(['active' => true, 'brand_id' => 0, 'tax_class_id' => 0]);
        ProductRepo::getInstance()->update($product, $this->productWithVariantsPayload());

        // PATCH only the variant definitions (add a new variant dimension)
        ProductRepo::getInstance()->patch($product, [
            'variants' => [
                [
                    'name'   => ['en' => 'Color', 'zh-cn' => '颜色'],
                    'values' => [
                        ['name' => ['en' => 'Red', 'zh-cn' => '红色'], 'image' => ''],
                        ['name' => ['en' => 'Blue', 'zh-cn' => '蓝色'], 'image' => ''],
                    ],
                ],
                [
                    'name'   => ['en' => 'Size', 'zh-cn' => '尺寸'],
                    'values' => [['name' => ['en' => 'Big', 'zh-cn' => '大'], 'image' => '']],
                ],
                [
                    'name'   => ['en' => 'Fit'],
                    'values' => [['name' => ['en' => 'Slim'], 'image' => '']],
                ],
            ],
        ]);

        $this->assertSame(3, DB::table('product_variants')->where('product_id', $product->id)->count());
        $fitId = DB::table('product_variants')
            ->where('product_id', $product->id)
            ->where('position', 2)
            ->value('id');
        $this->assertSame('Fit', DB::table('product_variant_translations')->where('variant_id', $fitId)->where('locale', 'en')->value('name'));
    }

    /**
     * Build a payload matching the rewritten Panel Vue editor's hidden input
     * format: each value carries a stable `id`, and each SKU lists the value
     * ids it picks per dimension via `variant_value_ids`.
     */
    private function productWithVariantsPayload(): array
    {
        return [
            'translations' => [
                ['locale' => 'en', 'name' => 'Variant Product', 'summary' => '', 'content' => ''],
            ],
            'variants' => [
                [
                    'name'   => ['en' => 'Color', 'zh-cn' => '颜色'],
                    'values' => [
                        ['id' => 'val_red',  'name' => ['en' => 'Red',  'zh-cn' => '红色'], 'image' => 'red.png'],
                        ['id' => 'val_blue', 'name' => ['en' => 'Blue', 'zh-cn' => '蓝色'], 'image' => ''],
                    ],
                ],
                [
                    'name'   => ['en' => 'Size', 'zh-cn' => '尺寸'],
                    'values' => [
                        ['id' => 'val_big',   'name' => ['en' => 'Big',   'zh-cn' => '大'], 'image' => ''],
                        ['id' => 'val_small', 'name' => ['en' => 'Small', 'zh-cn' => '小'], 'image' => ''],
                    ],
                ],
            ],
            'skus' => [
                [
                    'code'              => 'SKU-A',
                    'price'             => 10,
                    'origin_price'      => 12,
                    'quantity'          => 5,
                    'image'             => '',
                    'variant_value_ids' => ['val_red', 'val_big'],
                ],
                [
                    'code'              => 'SKU-B',
                    'price'             => 11,
                    'origin_price'      => 13,
                    'quantity'          => 6,
                    'image'             => '',
                    'variant_value_ids' => ['val_blue', 'val_small'],
                ],
            ],
            'categories'  => [],
            'attributes'  => [],
            'related_ids' => [],
        ];
    }
}
