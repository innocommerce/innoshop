<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Middleware;

use Illuminate\Support\Str;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Services\UrlRedirectService;
use InnoShop\Common\Tests\TestCase;

/**
 * HTTP-level behaviour of the front 404→301 redirect middleware (#157):
 * old URLs collected by Google must land on their successor with one hop,
 * locale context preserved, and everything else must keep its exact
 * previous behaviour (plain 404s, non-GET verbs, untouched panel/api).
 */
class HandleUrlRedirectTest extends TestCase
{
    public function test_old_product_slug_redirects_301(): void
    {
        $product = $this->makeProduct('prod-old-demo');
        $product->update(['slug' => 'prod-new-demo']);

        $response = $this->get('/product-prod-old-demo');

        $this->assertSame(301, $response->status());
        $this->assertSame('/product-prod-new-demo', parse_url($response->headers->get('Location'), PHP_URL_PATH));
    }

    public function test_locale_prefixed_slug_redirects_and_preserves_prefix(): void
    {
        $product = $this->makeProduct('prod-loc-old');
        $product->update(['slug' => 'prod-loc-new']);

        $response = $this->get('/en/product-prod-loc-old');

        $this->assertSame(301, $response->status());
        $this->assertSame('/en/product-prod-loc-new', parse_url($response->headers->get('Location'), PHP_URL_PATH));
    }

    public function test_unmatched_404_keeps_plain_404(): void
    {
        $this->makeProduct('prod-unrelated');

        $response = $this->get('/product-never-existed-at-all');

        $this->assertSame(404, $response->status());
    }

    public function test_post_requests_are_not_redirected(): void
    {
        $product = $this->makeProduct('prod-post-old');
        $product->update(['slug' => 'prod-post-new']);

        $response = $this->post('/product-prod-post-old');

        $this->assertSame(404, $response->status(), 'Only safe methods (GET/HEAD) may be redirected — a 301 for POST would break form endpoints.');
    }

    public function test_manual_rule_sends_deleted_product_to_category(): void
    {
        UrlRedirectService::upsert('deleted-demo-product', 'category-real-stuff');

        $response = $this->get('/deleted-demo-product');

        $this->assertSame(301, $response->status());
        $this->assertSame('/category-real-stuff', parse_url($response->headers->get('Location'), PHP_URL_PATH));
    }

    public function test_external_url_rule_redirects_verbatim(): void
    {
        UrlRedirectService::upsert('legacy-page', 'https://example.com/new-page');

        $response = $this->get('/legacy-page');

        $this->assertSame(301, $response->status());
        $this->assertSame('https://example.com/new-page', $response->headers->get('Location'));
    }

    public function test_article_slug_change_redirects_old_url(): void
    {
        $article = ArticleRepo::getInstance()->create([
            'slug'         => 'demo-article-old',
            'active'       => true,
            'translations' => [['locale' => 'en', 'title' => 'Demo Article']],
        ]);
        $article->update(['slug' => 'demo-article-new']);

        $response = $this->get('/article-demo-article-old');

        $this->assertSame(301, $response->status());
        $this->assertSame('/article-demo-article-new', parse_url($response->headers->get('Location'), PHP_URL_PATH));
    }

    public function test_slug_returned_home_serves_non_redirect(): void
    {
        $product = $this->makeProduct('back-home-a');
        $product->update(['slug' => 'back-home-b']);
        $product->update(['slug' => 'back-home-a']);

        $response = $this->get('/product-back-home-a');

        $this->assertNotContains($response->status(), [301, 302, 307, 308], 'A slug that came back home must never be redirected away.');
    }

    public function test_panel_404_is_never_redirected(): void
    {
        // A rule that WOULD match the panel path must stay dormant there —
        // proves the guard, not just the absence of a matching rule.
        UrlRedirectService::upsert('panel/panel-gone', 'product-trap');

        $response = $this->get('/panel/panel-gone');

        $this->assertSame(404, $response->status(), 'Panel requests must never be touched by the redirect mechanism.');
    }

    public function test_api_404_is_never_redirected(): void
    {
        UrlRedirectService::upsert('api/api-gone', 'product-trap');

        $response = $this->get('/api/api-gone');

        // The API channel answers unknown paths its own way (fallback route →
        // 422 JSON); what matters is that no 301 leaks out of our middleware.
        $this->assertNotContains($response->status(), [301, 302, 307, 308], 'API requests must never be touched by the redirect mechanism.');
    }

    public function test_encoded_non_ascii_slug_redirects(): void
    {
        // InnoShop does not ASCII-normalize slugs, so Chinese slugs reach the
        // middleware percent-encoded on real servers; the lookup must see them
        // decoded to match the raw slug stored in the table.
        $product = $this->makeProduct('天然-old');
        $product->update(['slug' => '天然-new']);

        $response = $this->get('/product-'.rawurlencode('天然-old'));

        $this->assertSame(301, $response->status());
        $location = $response->headers->get('Location');
        // parse_url 在部分 PHP 版本会把多字节路径中的字节替换成下划线
        // （实测 8.3.22 把 然(e7 84 b6) 改写成 e7 5f b6），对原始 UTF-8 的
        // Location 不可用 —— 这里按 "://" 后第一个 / 截取 path，字节安全。
        $pathStart = (int) strpos($location, '/', (int) strpos($location, '//') + 2);
        $this->assertSame('/product-天然-new', substr($location, $pathStart));
    }

    /**
     * @param  string  $slug
     * @return Product
     */
    private function makeProduct(string $slug): Product
    {
        return ProductRepo::getInstance()->create([
            'slug'         => $slug,
            'translations' => [['locale' => 'en', 'name' => 'Redirect HTTP Test Product']],
            'skus'         => [
                [
                    'code'         => 'SKU-HTTP-'.Str::upper(Str::random(6)),
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
