<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Repositories;

use Exception;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Tests\TestCase;

/**
 * A product without any SKU row cannot be priced, added to the cart or
 * ordered: every storefront path resolves the product through its master
 * SKU. A SKU-less product therefore used to crash the product page with
 * "Call to a member function loadMissing() on null".
 *
 * This test guards the write side of that invariant:
 *  - creating a product without SKUs is rejected;
 *  - an update that carries no SKU payload keeps the existing SKUs
 *    (it used to delete them);
 *  - an update / patch that explicitly sends an empty SKU list is rejected
 *    instead of wiping the SKUs.
 */
class ProductSkuInvariantTest extends TestCase
{
    public function test_create_without_skus_is_rejected(): void
    {
        $before = DB::table('products')->count();

        try {
            ProductRepo::getInstance()->create($this->payloadWithoutSkus());
            $this->fail('Expected an exception when creating a product without SKUs.');
        } catch (Exception $e) {
            $this->assertSame('A product must have at least one SKU.', $e->getMessage());
        }

        $this->assertSame($before, DB::table('products')->count(), 'A rejected product must not be persisted.');
        $this->assertSame(0, DB::table('product_skus')->count());
    }

    public function test_update_without_sku_payload_keeps_existing_skus(): void
    {
        $product = ProductRepo::getInstance()->create($this->payload());
        $skuId   = $product->skus->first()->id;

        $payload = $this->payload();
        unset($payload['skus']);
        $payload['translations'] = [['locale' => 'en', 'name' => 'Renamed Product']];

        ProductRepo::getInstance()->update($product, $payload);

        $this->assertTrue(
            DB::table('product_skus')->where('id', $skuId)->exists(),
            'An update without a SKU payload must not delete the existing SKUs.'
        );
        $this->assertSame(1, DB::table('product_skus')->where('product_id', $product->id)->count());
        $this->assertSame(
            'Renamed Product',
            DB::table('product_translations')->where('product_id', $product->id)->where('locale', 'en')->value('name')
        );
    }

    public function test_update_with_empty_skus_is_rejected_and_keeps_existing_skus(): void
    {
        $product = ProductRepo::getInstance()->create($this->payload());

        $payload         = $this->payload();
        $payload['skus'] = [];

        try {
            ProductRepo::getInstance()->update($product, $payload);
            $this->fail('Expected an exception when updating a product with an empty SKU list.');
        } catch (Exception $e) {
            $this->assertSame('A product must have at least one SKU.', $e->getMessage());
        }

        $this->assertSame(1, DB::table('product_skus')->where('product_id', $product->id)->count());
    }

    public function test_patch_with_empty_skus_is_rejected_and_keeps_existing_skus(): void
    {
        $product = ProductRepo::getInstance()->create($this->payload());

        try {
            ProductRepo::getInstance()->patch($product, ['skus' => []]);
            $this->fail('Expected an exception when patching a product with an empty SKU list.');
        } catch (Exception $e) {
            $this->assertSame('A product must have at least one SKU.', $e->getMessage());
        }

        $this->assertSame(1, DB::table('product_skus')->where('product_id', $product->id)->count());
    }

    public function test_update_with_skus_replaces_the_sku_set(): void
    {
        $product = ProductRepo::getInstance()->create($this->payload());
        $oldSku  = $product->skus->first()->id;

        $payload         = $this->payload();
        $payload['skus'] = [
            [
                'code'         => 'SKU-INV-002',
                'price'        => 20,
                'origin_price' => 24,
                'quantity'     => 8,
                'image'        => '',
            ],
        ];

        ProductRepo::getInstance()->update($product, $payload);

        $this->assertFalse(DB::table('product_skus')->where('id', $oldSku)->exists());
        $this->assertSame(1, DB::table('product_skus')->where('product_id', $product->id)->count());
        $this->assertSame(
            'SKU-INV-002',
            DB::table('product_skus')->where('product_id', $product->id)->value('code')
        );
    }

    private function payload(): array
    {
        return [
            'translations' => [['locale' => 'en', 'name' => 'SKU Invariant Product']],
            'skus'         => [
                [
                    'code'         => 'SKU-INV-001',
                    'price'        => 10,
                    'origin_price' => 12,
                    'quantity'     => 5,
                    'image'        => '',
                ],
            ],
            'categories'  => [],
            'attributes'  => [],
            'related_ids' => [],
        ];
    }

    private function payloadWithoutSkus(): array
    {
        $payload = $this->payload();
        unset($payload['skus']);

        return $payload;
    }
}
