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

/**
 * The panel product form submits shipping dimensions (length / width /
 * height / length_class) from the extended-info tab, and the Product model
 * lists them as fillable, but handleProductData() rebuilt the payload with
 * an explicit whitelist that omitted them — so create()/update() silently
 * dropped the values and the form reloaded showing 0.00 / empty unit.
 *
 * These tests pin the write path used by the panel controller
 * (ProductController::store / update), which routes through
 * ProductRepo::create() / update().
 */
class ProductDimensionsTest extends TestCase
{
    public function test_create_persists_dimensions(): void
    {
        $product = ProductRepo::getInstance()->create($this->payload());

        $row = $this->row($product->id);

        $this->assertEquals(120.00, (float) $row->length);
        $this->assertEquals(80.00, (float) $row->width);
        $this->assertEquals(60.00, (float) $row->height);
        $this->assertSame('cm', $row->length_class);
    }

    public function test_update_persists_dimensions(): void
    {
        $product = ProductRepo::getInstance()->create($this->payload());

        $payload                 = $this->payload();
        $payload['length']       = 250.5;
        $payload['width']        = 100.25;
        $payload['height']       = 45;
        $payload['length_class'] = 'mm';

        ProductRepo::getInstance()->update($product, $payload);

        $row = $this->row($product->id);

        $this->assertEquals(250.50, (float) $row->length);
        $this->assertEquals(100.25, (float) $row->width);
        $this->assertEquals(45.00, (float) $row->height);
        $this->assertSame('mm', $row->length_class);
    }

    private function payload(): array
    {
        return [
            'translations' => [['locale' => 'en', 'name' => 'Dimension Product']],
            'length'       => 120,
            'width'        => 80,
            'height'       => 60,
            'length_class' => 'cm',
            'skus'         => [
                [
                    'code'     => 'SKU-DIM-001',
                    'price'    => 10,
                    'quantity' => 5,
                    'image'    => '',
                ],
            ],
            'categories'  => [],
            'attributes'  => [],
            'related_ids' => [],
        ];
    }

    private function row(int $productId): object
    {
        return DB::table('products')->where('id', $productId)->first();
    }
}
