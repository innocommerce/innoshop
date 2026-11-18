<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Feature;

use Illuminate\Support\Facades\Route;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Tests\TestCase;
use Tests\Traits\CreatesProduct;

/**
 * A product without any SKU row has no master SKU. The product page used to
 * crash with "Call to a member function loadMissing() on null"; it must answer
 * 404 instead, because such a product cannot be priced or ordered.
 */
class ProductWithoutSkuTest extends TestCase
{
    use CreatesProduct;

    public function test_product_page_answers_404_when_the_product_has_no_sku(): void
    {
        $product = $this->createProduct();

        $this->assertSame(0, $product->skus()->count());

        $this->get($this->productUrl($product->id))->assertNotFound();
    }

    public function test_product_page_still_renders_for_a_product_with_a_sku(): void
    {
        $product = ProductRepo::getInstance()->create([
            'translations' => [['locale' => 'en', 'name' => 'Product With Sku']],
            'skus'         => [
                [
                    'code'     => 'SKU-FRONT-001',
                    'price'    => 10,
                    'quantity' => 5,
                    'image'    => '',
                ],
            ],
            'categories'  => [],
            'attributes'  => [],
            'related_ids' => [],
            'active'      => true,
        ]);

        $this->get($this->productUrl($product->id))->assertOk();
    }

    /**
     * The storefront routes are registered with a locale prefix
     * (`en.front.products.show`) once the locales exist, and without one
     * (`front.products.show`) when the routes are loaded before the locale
     * table is seeded — accept both.
     */
    private function productUrl(int $productId): string
    {
        foreach (['front.products.show', front_locale_code().'.front.products.show'] as $name) {
            if (Route::has($name)) {
                return route($name, $productId);
            }
        }

        $this->fail('The front product route is not registered.');
    }
}
