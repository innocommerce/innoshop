<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Tests\TestCase;

/**
 * The front page best seller block used to rank the whole catalogue in SQL:
 * MySQL scanned every product row, sorted it in a temporary table and only
 * then kept 8 rows. That cost 19ms while idle and 3~5s once a dozen crawler
 * workers requested the page at the same time, which is what made the front
 * page TTFB swing between 0.3s and 1.9s.
 *
 * The ranking now starts from the order_items table (a handful of rows) and
 * the result is cached per locale, so a crawler burst cannot stampede the
 * database. These tests pin the ranking order, the fill-up behaviour and the
 * cache.
 */
class BestSellerProductsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The array cache store survives between test methods in the same
        // process, and the cache key only depends on locale + limit.
        Cache::flush();
    }

    public function test_products_are_ranked_by_sales_count(): void
    {
        $best   = $this->createProduct('Best Seller');
        $middle = $this->createProduct('Middle');
        $worst  = $this->createProduct('Worst');

        $this->createOrderItems($best, 3);
        $this->createOrderItems($middle, 1);

        $products = ProductRepo::getInstance()->getBestSellerProducts(3);

        $this->assertCount(3, $products);
        $this->assertSame(
            [$best->id, $middle->id, $worst->id],
            $products->pluck('id')->all(),
            'Products must be ordered by their order item count, the ones without sales come last.'
        );
        $this->assertSame(
            [3, 1, 0],
            $products->map(fn ($product) => (int) $product->order_items_count)->all(),
            'Every returned product must carry the order_items_count attribute.'
        );
    }

    public function test_limit_is_respected(): void
    {
        $this->createProduct('First');
        $this->createProduct('Second');
        $this->createProduct('Third');

        $products = ProductRepo::getInstance()->getBestSellerProducts(2);

        $this->assertCount(2, $products);
    }

    public function test_inactive_products_are_skipped(): void
    {
        $active   = $this->createProduct('Active');
        $inactive = $this->createProduct('Inactive', false);

        $this->createOrderItems($inactive, 5);

        $products = ProductRepo::getInstance()->getBestSellerProducts(5);

        $this->assertCount(1, $products);
        $this->assertSame($active->id, $products->first()->id);
    }

    public function test_result_is_cached_per_locale(): void
    {
        $this->createProduct('Cached');

        ProductRepo::getInstance()->getBestSellerProducts(5);

        $cacheKey = 'product_bestseller_'.locale_code().'_5';
        $this->assertTrue(Cache::has($cacheKey), 'The best seller list must be cached.');

        // A product created after the cache was filled does not show up until
        // the cache expires — that is the point of the cache.
        $this->createProduct('Created Later');

        $products = ProductRepo::getInstance()->getBestSellerProducts(5);

        $this->assertCount(1, $products);
    }

    private function createProduct(string $name, bool $active = true): mixed
    {
        return ProductRepo::getInstance()->create([
            'active'       => $active,
            'translations' => [['locale' => 'en', 'name' => $name]],
            'skus'         => [
                [
                    'code'         => 'SKU-'.str($name)->slug(),
                    'price'        => 10,
                    'origin_price' => 12,
                    'quantity'     => 5,
                    'image'        => '',
                ],
            ],
            'categories'  => [],
            'attributes'  => [],
            'related_ids' => [],
        ]);
    }

    private function createOrderItems(mixed $product, int $quantity): void
    {
        for ($i = 0; $i < $quantity; $i++) {
            DB::table('order_items')->insert([
                'order_id'      => 0,
                'product_id'    => $product->id,
                'order_number'  => 'ORDER-'.$product->id.'-'.$i,
                'product_sku'   => 'SKU',
                'variant_label' => '',
                'name'          => $product->translations->first()->name,
                'image'         => '',
                'quantity'      => 1,
                'price'         => 10,
            ]);
        }
    }
}
