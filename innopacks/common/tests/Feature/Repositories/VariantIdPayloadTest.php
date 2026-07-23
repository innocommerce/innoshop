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
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Tests\TestCase;
use Tests\Traits\CreatesProduct;

/**
 * Validates that the new ID-based SKU payload (sku.variant_value_ids referencing
 * client-generated value ids) is correctly resolved into normalized pivot rows
 * by ProductRepo, mirroring how the rewritten Panel Vue editor submits data.
 *
 * Covers the contract the new Panel Vue depends on: each value in the variants
 * payload carries a stable `id`, and each SKU lists its selected value ids.
 */
class VariantIdPayloadTest extends TestCase
{
    use CreatesProduct;

    public function test_create_with_id_based_payload_writes_correct_pivot_rows(): void
    {
        $payload = $this->idBasedPayload();

        $product = ProductRepo::getInstance()->create($payload);

        // Two variants, two values each
        $this->assertSame(2, DB::table('product_variants')->where('product_id', $product->id)->count());
        $this->assertSame(4, DB::table('product_variant_values')
            ->whereIn('variant_id', function ($q) use ($product) {
                $q->select('id')->from('product_variants')->where('product_id', $product->id);
            })->count());

        // Each SKU points to the right value_ids, NOT positional indexes
        $skus = $product->skus;
        $this->assertSame(2, $skus->count());

        // SKU 1 = red + big → variant_value_ids count = 2
        $sku1Mappings = DB::table('product_sku_variant_values')->where('sku_id', $skus[0]->id)->get();
        $this->assertCount(2, $sku1Mappings);

        // The pivot rows resolve to actual DB rows
        foreach ($sku1Mappings as $mapping) {
            $this->assertNotNull(
                DB::table('product_variant_values')->where('id', $mapping->value_id)->first(),
                'Pivot references a non-existent value_id'
            );
        }
    }

    public function test_value_id_referenced_across_skus_resolves_consistently(): void
    {
        // Re-use the same value_id across two SKUs (a Red+Big SKU and a Red+Small SKU)
        // to verify the pivot rows end up pointing at the same DB row, not duplicates.
        $payload = $this->idBasedPayload();
        $redId   = $payload['variants'][0]['values'][0]['id']; // "Red"
        $bigId   = $payload['variants'][1]['values'][0]['id'];  // "Big"
        $smallId = $payload['variants'][1]['values'][1]['id']; // "Small"

        $payload['skus'] = [
            [
                'code'              => 'SKU-RED-BIG',
                'price'             => 100,
                'quantity'          => 5,
                'image'             => '',
                'variant_value_ids' => [$redId, $bigId],
            ],
            [
                'code'              => 'SKU-RED-SMALL',
                'price'             => 90,
                'quantity'          => 3,
                'image'             => '',
                'variant_value_ids' => [$redId, $smallId],
            ],
        ];

        $product = ProductRepo::getInstance()->create($payload);

        // Both SKUs reference the same DB row for "Red"
        $redDbId = DB::table('product_variant_value_translations')
            ->where('locale', 'en')
            ->where('name', 'Red')
            ->value('value_id');

        $sku1RedValue = DB::table('product_sku_variant_values')
            ->where('sku_id', $product->skus[0]->id)
            ->where('variant_id', function ($q) use ($product) {
                $q->select('id')->from('product_variants')
                    ->where('product_id', $product->id)
                    ->where('position', 0)->limit(1);
            })
            ->value('value_id');

        $sku2RedValue = DB::table('product_sku_variant_values')
            ->where('sku_id', $product->skus[1]->id)
            ->where('variant_id', function ($q) use ($product) {
                $q->select('id')->from('product_variants')
                    ->where('product_id', $product->id)
                    ->where('position', 0)->limit(1);
            })
            ->value('value_id');

        $this->assertSame((int) $redDbId, $sku1RedValue);
        $this->assertSame((int) $redDbId, $sku2RedValue);
    }

    private function idBasedPayload(): array
    {
        return [
            'translations' => [
                ['locale' => 'en', 'name' => 'ID-based Product'],
            ],
            'variants' => [
                [
                    'id'      => 'var_color',
                    'name'    => ['en' => 'Color'],
                    'isImage' => false,
                    'values'  => [
                        ['id' => 'val_red',  'name' => ['en' => 'Red'],  'image' => ''],
                        ['id' => 'val_blue', 'name' => ['en' => 'Blue'], 'image' => ''],
                    ],
                ],
                [
                    'id'      => 'var_size',
                    'name'    => ['en' => 'Size'],
                    'isImage' => false,
                    'values'  => [
                        ['id' => 'val_big',   'name' => ['en' => 'Big'],   'image' => ''],
                        ['id' => 'val_small', 'name' => ['en' => 'Small'], 'image' => ''],
                    ],
                ],
            ],
            'skus' => [
                [
                    'code'              => 'SKU-RED-BIG',
                    'price'             => 100,
                    'origin_price'      => 120,
                    'quantity'          => 5,
                    'image'             => '',
                    'variant_value_ids' => ['val_red', 'val_big'],
                ],
                [
                    'code'              => 'SKU-BLUE-SMALL',
                    'price'             => 90,
                    'origin_price'      => 110,
                    'quantity'          => 3,
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
