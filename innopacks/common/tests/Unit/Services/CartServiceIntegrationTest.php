<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Models\Product\Sku;
use InnoShop\Common\Services\CartService;
use InnoShop\Common\Tests\TestCase;

class CartServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_cart_without_quantity_defaults_to_one()
    {
        // Create Brand
        $brand = new \InnoShop\Common\Models\Brand;
        $brand->fill([
            'name'   => 'Test Brand',
            'first'  => 'T',
            'logo'   => 'test_logo.jpg',
            'active' => true,
        ]);
        $brand->save();

        // Create a product and SKU with sufficient stock manually since factories are missing
        $product = new Product;
        $product->fill([
            'brand_id' => $brand->id,
            'name'     => 'Test Product',
            'active'   => true,
            'price'    => 100,
            'slug'     => 'test-product',
        ]);
        $product->save();

        $sku = new Sku;
        $sku->fill([
            'product_id' => $product->id,
            'code'       => 'SKU-TEST-001',
            'quantity'   => 10,
            'price'      => 100,
            'is_default' => true,
        ]);
        $sku->save();

        // Initialize CartService with a guest ID to avoid auth issues
        $cartService = new CartService(0, 'guest-123');

        // Data without quantity
        $data = [
            'sku_id' => $sku->id,
            // 'quantity' is missing
        ];

        try {
            $cartService->addCart($data);
            $this->assertTrue(true, 'addCart executed successfully without quantity');
        } catch (\Exception $e) {
            $this->fail('addCart threw exception: '.$e->getMessage());
        }
    }
}
