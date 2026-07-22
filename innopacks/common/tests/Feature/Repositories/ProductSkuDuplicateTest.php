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
use InnoShop\Common\Models\Product;
use InnoShop\Common\Models\Product\Sku;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Tests\TestCase;
use ReflectionClass;
use Tests\Traits\CreatesProduct;

/**
 * Guards the user-facing behaviour when a product is saved with an SKU code
 * that already exists. The raw SQLSTATE 1062 / UNIQUE-constraint error must
 * never reach the panel UI; it must be translated into panel/product.error_sku_repeat.
 */
class ProductSkuDuplicateTest extends TestCase
{
    use CreatesProduct;

    /**
     * Repeating an SKU code that already exists surfaces as the translated
     * "SKU repeat" message, not the raw SQLSTATE / constraint error.
     * Regression test for the panel product editor reporting
     * "SQLSTATE[23000]: ... 1062 Duplicate entry '...' for key 'sku_code'".
     */
    public function test_duplicate_sku_code_is_translated_into_friendly_message(): void
    {
        // Seed an SKU code that is already taken on another product.
        $taken = $this->createProduct($this->safeProductAttributes());
        $this->seedSku($taken, 'DUP-CODE');

        $newProduct = $this->createProduct($this->safeProductAttributes());

        try {
            $this->invokeCreateProductSkus($newProduct, $this->skuData('DUP-CODE'));
            $this->fail('Expected an exception for duplicate SKU code.');
        } catch (Exception $e) {
            $this->assertSame(panel_trans('product.error_sku_repeat', ['code' => 'DUP-CODE']), $e->getMessage());
            $this->assertStringNotContainsString('SQLSTATE', $e->getMessage());
            $this->assertStringNotContainsString('1062', $e->getMessage());
        }
    }

    /**
     * A genuinely fresh SKU code must still persist normally — the friendly
     * catch must not swallow legitimate writes.
     */
    public function test_unique_sku_code_is_persisted_normally(): void
    {
        $product = $this->createProduct($this->safeProductAttributes());

        $this->invokeCreateProductSkus($product, $this->skuData('FRESH-CODE'));

        $this->assertDatabaseHas('product_skus', ['code' => 'FRESH-CODE']);
    }

    /**
     * ProductFactory ships brand_id/tax_class_id as null, but both columns are
     * NOT NULL — pass safe sentinel values (0 = none).
     */
    private function safeProductAttributes(): array
    {
        return ['brand_id' => 0, 'tax_class_id' => 0];
    }

    private function seedSku(Product $product, string $code): Sku
    {
        return Sku::create([
            'product_id'   => $product->id,
            'images'       => [],
            'variants'     => [],
            'code'         => $code,
            'model'        => $code,
            'price'        => 10,
            'origin_price' => 0,
            'quantity'     => 5,
            'is_default'   => true,
            'position'     => 0,
        ]);
    }

    private function skuData(string $code): array
    {
        return [[
            'images'       => [''],
            'variants'     => [],
            'code'         => $code,
            'model'        => $code,
            'price'        => 10,
            'origin_price' => 0,
            'quantity'     => 5,
            'is_default'   => true,
            'position'     => 0,
        ]];
    }

    private function invokeCreateProductSkus(Product $product, array $skus): void
    {
        $repo   = ProductRepo::getInstance();
        $method = (new ReflectionClass($repo))->getMethod('createProductSkus');
        $method->setAccessible(true);
        $method->invoke($repo, $product, $skus);
    }
}
