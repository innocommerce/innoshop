<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Models\UrlRedirect;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Services\UrlRedirectService;
use InnoShop\Common\Tests\TestCase;

/**
 * Core SEO redirect mechanism (factory issue #157).
 *
 * Demo content gets indexed by Google on staging sites; once the real
 * catalog replaces it (renamed slugs, deleted rows), the old URLs 404.
 * The slug-change recorder turns every rename into a permanent 301 rule
 * so link equity survives; manual rules cover the "deleted content"
 * case via `redirects:add` until the panel UI plugin exists.
 */
class UrlRedirectServiceTest extends TestCase
{
    public function test_slug_change_records_redirect(): void
    {
        $product = $this->makeProduct('demo-product');
        $product->update(['slug' => 'real-product']);

        $row = UrlRedirect::query()->where('old_path', 'product-demo-product')->first();
        $this->assertNotNull($row, 'Renaming a slug must record a redirect rule.');
        $this->assertSame('product-real-product', $row->new_path);
        $this->assertSame('product', $row->entity_type);
        $this->assertSame($product->id, $row->entity_id);
    }

    public function test_second_rename_keeps_single_hop_for_every_historical_path(): void
    {
        $product = $this->makeProduct('slug-a');
        $product->update(['slug' => 'slug-b']);
        $product->update(['slug' => 'slug-c']);

        $rowA = UrlRedirect::query()->where('old_path', 'product-slug-a')->first();
        $rowB = UrlRedirect::query()->where('old_path', 'product-slug-b')->first();

        $this->assertNotNull($rowA);
        $this->assertNotNull($rowB);
        $this->assertSame('product-slug-c', $rowA->new_path, 'Earliest old path must point straight at the current slug — single 301, no intermediate hop.');
        $this->assertSame('product-slug-c', $rowB->new_path);
    }

    public function test_slug_reverted_back_deletes_rule_from_live_path(): void
    {
        $product = $this->makeProduct('slug-a');
        $product->update(['slug' => 'slug-b']);
        $this->assertSame(1, UrlRedirect::query()->count());

        // Slug goes back home: product-a is live again, so it must serve 200
        // (no rule FROM it), while product-b — which Google also saw — 301s home.
        $product->update(['slug' => 'slug-a']);

        $this->assertNull(UrlRedirect::query()->where('old_path', 'product-slug-a')->first(), 'A live path must never carry a redirect rule pointing away from itself.');
        $rowB = UrlRedirect::query()->where('old_path', 'product-slug-b')->first();
        $this->assertNotNull($rowB);
        $this->assertSame('product-slug-a', $rowB->new_path);
    }

    public function test_manual_loop_is_rejected(): void
    {
        UrlRedirectService::upsert('path-x', 'path-y');

        // A manual rule that would send visitors ping-ponging x → y → x must be refused.
        $this->assertFalse(UrlRedirectService::upsert('path-y', 'path-x'));
        $this->assertNull(UrlRedirect::query()->where('old_path', 'path-y')->first());
        $this->assertSame('path-y', UrlRedirect::query()->where('old_path', 'path-x')->value('new_path'), 'The first rule must stay intact after a rejected loop.');
    }

    public function test_manual_rules_accept_internal_path_and_external_url(): void
    {
        UrlRedirectService::upsert('deleted-demo-product', 'category-real-products');
        UrlRedirectService::upsert('old-page', 'https://example.com/new-page');

        $this->assertSame('category-real-products', UrlRedirect::query()->where('old_path', 'deleted-demo-product')->value('new_path'));
        $this->assertSame('https://example.com/new-page', UrlRedirect::query()->where('old_path', 'old-page')->value('new_path'));
    }

    public function test_resolve_reapplies_locale_and_keeps_external_url(): void
    {
        UrlRedirectService::upsert('demo-a', 'real-a');
        UrlRedirectService::upsert('demo-home', '/');
        UrlRedirectService::upsert('demo-ext', 'https://example.com/x');

        $this->assertSame('en/real-a', UrlRedirectService::resolve('en/demo-a'), 'Locale context from the request must survive the redirect.');
        $this->assertSame('real-a', UrlRedirectService::resolve('demo-a'));
        $this->assertSame('en', UrlRedirectService::resolve('en/demo-home'), 'Homepage rule under a locale prefix redirects to the localized home.');
        $this->assertSame('/', UrlRedirectService::resolve('demo-home'));
        $this->assertSame('https://example.com/x', UrlRedirectService::resolve('en/demo-ext'), 'External targets are never locale-prefixed.');
    }

    public function test_resolve_counts_hits(): void
    {
        UrlRedirectService::upsert('demo-hits', 'real-hits');

        UrlRedirectService::resolve('demo-hits');
        UrlRedirectService::resolve('demo-hits');

        $this->assertSame(2, UrlRedirect::query()->where('old_path', 'demo-hits')->value('hits'));
    }

    public function test_duplicate_old_path_updates_existing_row(): void
    {
        UrlRedirectService::upsert('same-old', 'first-new');
        UrlRedirectService::upsert('same-old', 'second-new');

        $this->assertSame(1, UrlRedirect::query()->where('old_path', 'same-old')->count());
        $this->assertSame('second-new', UrlRedirect::query()->where('old_path', 'same-old')->value('new_path'));
    }

    public function test_no_rule_when_slug_unchanged_or_cleared(): void
    {
        $product = $this->makeProduct('stable-slug');

        $product->update(['slug' => 'stable-slug']);
        $this->assertSame(0, UrlRedirect::query()->count(), 'Re-saving an unchanged slug must not create a rule.');

        $product->update(['slug' => '']);
        $this->assertSame(0, UrlRedirect::query()->count(), 'Clearing a slug must not create a rule.');
    }

    public function test_console_command_creates_manual_rule(): void
    {
        Artisan::call('redirects:add', ['from' => 'demo-gone', 'to' => 'category-survivors']);

        $this->assertSame('category-survivors', UrlRedirect::query()->where('old_path', 'demo-gone')->value('new_path'));
    }

    /**
     * @param  string  $slug
     * @return Product
     */
    private function makeProduct(string $slug): Product
    {
        return ProductRepo::getInstance()->create([
            'slug'         => $slug,
            'translations' => [['locale' => 'en', 'name' => 'Redirect Test Product']],
            'skus'         => [
                [
                    'code'         => 'SKU-REDIRECT-'.Str::upper(Str::random(6)),
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
}
