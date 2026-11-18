<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Feature\Traits;

use Illuminate\Http\Request;
use InnoShop\Common\Models\Brand;
use InnoShop\Common\Models\Category;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Models\Product\Sku;
use InnoShop\Front\Traits\FilterSidebarTrait;
use Tests\TestCase;

/**
 * Exposes the protected getFilterSidebarData() from FilterSidebarTrait so
 * tests can drive it directly without standing up an entire HTTP controller
 * + front-route + view-render stack. The trait's contract is: given a
 * Request, return brands / attributes / variants / availability / price
 * arrays for the sidebar; this stub forwards the call to the trait method.
 */
class FilterSidebarTraitProbe
{
    use FilterSidebarTrait;

    public function callGetFilterSidebarData(Request $request, ?int $categoryId = null): array
    {
        return $this->getFilterSidebarData($request, $categoryId);
    }
}

/**
 * RED tests for issue #18 — FilterSidebarTrait must prune the brand list
 * to brands that have at least one active product matching the current
 * Request filter context (category / keyword / price / attribute / variant /
 * stock). brand_ids must NOT self-trim.
 *
 * Acceptance criteria: see Gitea issue
 * https://innoshop.work/demao/scselects.com/issues/18
 */
class FilterSidebarTraitTest extends TestCase
{
    private FilterSidebarTraitProbe $probe;

    protected function setUp(): void
    {
        parent::setUp();
        $this->probe = new FilterSidebarTraitProbe;
    }

    /**
     * Build a Brand row, with translations for fallbackName().
     */
    private function makeBrand(string $slug, bool $active = true): Brand
    {
        $brand = Brand::query()->create([
            'slug'     => $slug,
            'name'     => $slug,
            'first'    => strtoupper($slug[0]),
            'logo'     => 'brands/'.$slug.'.png',
            'position' => 0,
            'active'   => $active,
        ]);
        $brand->translations()->create([
            'locale' => 'en',
            'name'   => $slug.' '.uniqid(),
        ]);

        return $brand->fresh();
    }

    /**
     * Build a Category row.
     */
    private function makeCategory(string $slug): Category
    {
        return Category::query()->create([
            'slug'   => $slug,
            'active' => true,
        ]);
    }

    /**
     * Build an active Product linked to a brand + category, plus a default
     * SKU carrying the price — applyPriceFilters reads masterSku.price
     * (not products.price), so without a SKU the price filter would
     * exclude every product in the fixture.
     *
     * @param  float|null  $price  Master-SKU price. Null = skip SKU creation.
     */
    private function makeProduct(
        ?Brand $brand,
        ?Category $category,
        float $price = 100.0,
        bool $active = true,
        ?string $name = null
    ): Product {
        $product = Product::query()->create([
            'slug'         => 'prod-'.uniqid(),
            'spu_code'     => 'SPU-'.uniqid(),
            'brand_id'     => $brand?->id,
            'active'       => $active,
            'price'        => $price,
            'type'         => Product::TYPE_NORMAL,
            'is_virtual'   => false,
            'position'     => 0,
            'sales'        => 0,
            'viewed'       => 0,
            'minimum'      => 1,
            'published_at' => now(),
        ]);
        if ($category) {
            $product->categories()->attach($category->id);
        }
        if ($name !== null) {
            $product->translations()->create([
                'locale' => 'en',
                'name'   => $name,
                'slug'   => $product->slug,
            ]);
        }
        // Default master SKU — needed so applyPriceFilters can match.
        Sku::query()->create([
            'product_id'   => $product->id,
            'code'         => 'SKU-'.uniqid(),
            'price'        => $price,
            'origin_price' => $price,
            'quantity'     => 10,
            'is_default'   => true,
            'position'     => 0,
        ]);

        return $product->fresh();
    }

    /**
     * T1 / AC1: No filters → brands list == all active brands with at
     * least one active product (which equals the full active set when
     * every active brand owns a product in this fixture).
     */
    public function test_no_filter_returns_all_active_brands_that_have_products(): void
    {
        $b1 = $this->makeBrand('b-one');
        $b2 = $this->makeBrand('b-two');
        $b3 = $this->makeBrand('b-three', active: false);   // inactive, must be excluded
        $b4 = $this->makeBrand('b-orphan');                 // active but no products — also excluded

        $cat = $this->makeCategory('cat-a');
        $this->makeProduct($b1, $cat);
        $this->makeProduct($b2, $cat);

        $data = $this->probe->callGetFilterSidebarData(new Request);

        $ids = collect($data['brands'])->pluck('id')->all();
        sort($ids);
        $expected = [$b1->id, $b2->id];
        sort($expected);

        $this->assertSame($expected, $ids, 'Only active brands with ≥1 product must appear');
    }

    /**
     * T2 / AC2: Category filter narrows brands to those that have at
     * least one product under that category.
     */
    public function test_category_filter_narrows_brands_to_that_category(): void
    {
        $bA = $this->makeBrand('b-A');
        $bB = $this->makeBrand('b-B');
        $bC = $this->makeBrand('b-C');

        $catA = $this->makeCategory('cat-A');
        $catB = $this->makeCategory('cat-B');
        $catC = $this->makeCategory('cat-C');

        $this->makeProduct($bA, $catA);
        $this->makeProduct($bB, $catB);
        $this->makeProduct($bC, $catC);

        $data = $this->probe->callGetFilterSidebarData(new Request, $catA->id);

        $ids = collect($data['brands'])->pluck('id')->all();
        $this->assertSame([$bA->id], $ids, 'Only brands with products in the target category must appear');
    }

    /**
     * T3 / AC3: Keyword filter narrows brands to those whose product
     * translations match the keyword.
     */
    public function test_keyword_filter_narrows_brands_to_keyword_matches(): void
    {
        $bA = $this->makeBrand('b-A');
        $bB = $this->makeBrand('b-B');
        $bC = $this->makeBrand('b-C');

        $cat = $this->makeCategory('cat');
        $this->makeProduct($bA, $cat, name: 'Stainless Steel Kettle');
        $this->makeProduct($bB, $cat, name: 'Glass Tumbler');
        $this->makeProduct($bC, $cat, name: 'Wooden Cutting Board');

        $request = Request::create('/', 'GET', ['keyword' => 'kettle']);
        $data    = $this->probe->callGetFilterSidebarData($request);

        $ids = collect($data['brands'])->pluck('id')->all();
        $this->assertSame([$bA->id], $ids, 'Only the brand owning the keyword-matching product must appear');
    }

    /**
     * T4 / AC4: Price-range filter narrows brands to those with at least
     * one product in the price range.
     */
    public function test_price_range_filter_narrows_brands(): void
    {
        $bA = $this->makeBrand('b-A');
        $bB = $this->makeBrand('b-B');

        $cat = $this->makeCategory('cat');
        $this->makeProduct($bA, $cat, price: 50.0);   // out of range
        $this->makeProduct($bA, $cat, price: 250.0);  // in range — keeps bA
        $this->makeProduct($bB, $cat, price: 80.0);   // out of range — drops bB

        $request = Request::create('/', 'GET', ['min_price' => '100', 'max_price' => '500']);
        $data    = $this->probe->callGetFilterSidebarData($request);

        $ids = collect($data['brands'])->pluck('id')->all();
        $this->assertSame([$bA->id], $ids, 'Only brands with products in [100,500] must appear');
    }

    /**
     * T5 / AC5 + B3: brand_ids must NOT self-trim. When the URL already
     * selects brand A and B, the rest of the brands that have products
     * under the remaining filters must still show up.
     */
    public function test_brand_ids_filter_does_not_self_trim_others(): void
    {
        $bA = $this->makeBrand('b-A');
        $bB = $this->makeBrand('b-B');
        $bC = $this->makeBrand('b-C');

        $cat = $this->makeCategory('cat');
        $this->makeProduct($bA, $cat);
        $this->makeProduct($bB, $cat);
        $this->makeProduct($bC, $cat);

        // URL selects A and B; under the rest of the filter context, C
        // also has a product, so it must STILL appear in the brand
        // sidebar (otherwise the customer can never discover it).
        $request = Request::create('/', 'GET', ['brands' => $bA->id.','.$bB->id]);
        $data    = $this->probe->callGetFilterSidebarData($request);

        $ids = collect($data['brands'])->pluck('id')->all();
        sort($ids);
        $expected = [$bA->id, $bB->id, $bC->id];
        sort($expected);

        $this->assertSame(
            $expected,
            $ids,
            'brand_ids must be excluded from the "used brands" query; otherwise C disappears once selected out'
        );

        // The two selected brands must be flagged selected.
        $byId = collect($data['brands'])->keyBy('id');
        $this->assertTrue($byId[$bA->id]['selected']);
        $this->assertTrue($byId[$bB->id]['selected']);
        $this->assertFalse($byId[$bC->id]['selected']);
    }

    /**
     * T6 / AC6: When the filter context produces zero products, the
     * brands list must be empty (the sidebar view will skip the brand
     * card entirely).
     */
    public function test_filter_with_no_matching_products_yields_empty_brands(): void
    {
        $bA = $this->makeBrand('b-A');

        $cat = $this->makeCategory('cat');
        $this->makeProduct($bA, $cat, name: 'Toaster');

        // Keyword matches nothing → no products at all
        $request = Request::create('/', 'GET', ['keyword' => 'nonexistent-xyzzy']);
        $data    = $this->probe->callGetFilterSidebarData($request);

        $this->assertSame([], $data['brands'], 'Brands must be empty when no product matches the filter context');
    }

    /**
     * T7 / B5: If the "used brands" lookup throws, the trait must
     * silently fall back to the full active-brand list rather than
     * collapsing the sidebar to nothing (sales impact).
     *
     * Implementation plan: the trait will own a `protected
     * getUsedBrandIds(Request, ?int)` method called from inside
     * `getFilterSidebarData` inside a try/catch; on throw it must keep
     * the full brand list. To inject the throw from a test without
     * monkey-patching the trait source, we drop a subclass that
     * overrides the protected method and always throws.
     */
    public function test_exception_in_used_brands_lookup_falls_back_to_all_active_brands(): void
    {
        $bA  = $this->makeBrand('b-A');
        $cat = $this->makeCategory('cat');
        $this->makeProduct($bA, $cat);

        // Subclass override — protected so it remains overridable.
        $probe = new class extends FilterSidebarTraitProbe
        {
            protected function getUsedBrandIds(Request $request, ?int $categoryId = null): array
            {
                throw new \RuntimeException('simulated DB outage');
            }
        };

        $data = $probe->callGetFilterSidebarData(new Request);

        $ids = collect($data['brands'])->pluck('id')->all();
        $this->assertContains(
            $bA->id,
            $ids,
            'When getUsedBrandIds throws, trait must fall back to all active brands so the sidebar never collapses'
        );
    }
}
