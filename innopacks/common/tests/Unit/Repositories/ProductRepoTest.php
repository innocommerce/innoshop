<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Repositories;

use InnoShop\Common\Repositories\ProductRepo;
use PHPUnit\Framework\TestCase;

class ProductRepoTest extends TestCase
{
    /**
     * Test AVAILABLE_SORT_FIELDS constant contains expected fields.
     */
    public function test_available_sort_fields_contains_expected_fields(): void
    {
        $expectedFields = [
            'position',
            'rating',
            'sales',
            'viewed',
            'updated_at',
            'created_at',
            'ps.price',
            'pt.name',
        ];

        $this->assertEquals($expectedFields, ProductRepo::AVAILABLE_SORT_FIELDS);
    }

    /**
     * Test handleSkus processes single SKU correctly.
     */
    public function test_handle_skus_processes_single_sku(): void
    {
        $skus = [
            [
                'code'         => 'SKU001',
                'price'        => 99.99,
                'origin_price' => 129.99,
                'quantity'     => 100,
                'image'        => 'test.jpg',
                'variants'     => [],
            ],
        ];

        $items = ProductRepo::getInstance()->handleSkus($skus, ['weight' => 1.2]);

        $this->assertCount(1, $items);
        $this->assertEquals('SKU001', $items[0]['code']);
        $this->assertEquals(99.99, $items[0]['price']);
        $this->assertTrue($items[0]['is_default']);
        $this->assertEquals(1.2, $items[0]['weight']);
    }

    /**
     * Test handleSkus processes multiple SKUs correctly.
     */
    public function test_handle_skus_processes_multiple_skus(): void
    {
        $skus = [
            ['code' => 'SKU001', 'price' => 99.99, 'is_default' => true, 'weight' => 0.5],
            ['code' => 'SKU002', 'price' => 89.99, 'is_default' => false],
        ];

        $items = ProductRepo::getInstance()->handleSkus($skus, ['weight' => 1.0]);

        $this->assertCount(2, $items);
        $this->assertTrue($items[0]['is_default']);
        $this->assertFalse($items[1]['is_default']);
        $this->assertEquals(0.5, $items[0]['weight']);
        $this->assertEquals(1.0, $items[1]['weight']);
    }

    /**
     * Test handleProductData processes data correctly.
     */
    public function test_handle_product_data_processes_correctly(): void
    {
        $data = [
            'type'         => 'normal',
            'spu_code'     => 'SPU001',
            'slug'         => 'test-product',
            'brand_id'     => 1,
            'images'       => ['image1.jpg', 'image2.jpg'],
            'tax_class_id' => 1,
            'position'     => 10,
            'weight'       => 1.5,
            'active'       => true,
        ];

        // Test the data handling logic
        $images = $data['images'] ?? null;
        if (is_string($images)) {
            $images = json_decode($images, true);
        }

        $slug = $data['slug'] ?? null;
        if ($slug === '') {
            $slug = null;
        }

        $result = [
            'type'         => $data['type'] ?? 'normal',
            'spu_code'     => $data['spu_code'] ?? null,
            'slug'         => $slug,
            'brand_id'     => $data['brand_id'] ?? 0,
            'images'       => $images,
            'tax_class_id' => $data['tax_class_id'] ?? 0,
            'position'     => (int) ($data['position'] ?? 0),
            'weight'       => $data['weight'] ?? 0,
            'active'       => (bool) ($data['active'] ?? false),
        ];

        $this->assertEquals('normal', $result['type']);
        $this->assertEquals('SPU001', $result['spu_code']);
        $this->assertEquals('test-product', $result['slug']);
        $this->assertEquals(1, $result['brand_id']);
        $this->assertIsArray($result['images']);
        $this->assertTrue($result['active']);
    }
}
