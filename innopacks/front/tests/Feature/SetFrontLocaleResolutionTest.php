<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Feature;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use InnoShop\Common\Models\Locale;
use InnoShop\Common\Repositories\LocaleRepo;
use InnoShop\Common\Services\CountryLocaleResolver;
use InnoShop\Front\Middleware\SetFrontLocale;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Behaviour tests for the storefront locale resolution.
 *
 * The middleware is invoked directly instead of through the HTTP kernel: the
 * front routes are registered once at boot time and their locale prefix
 * depends on the state of the locales table at that moment, so the URL shape
 * cannot be relied on in a test.
 */
class SetFrontLocaleResolutionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        LocaleRepo::$enabledLocales = null;
        Config::set('inno.system.auto_detect_locale_by_ip', false);
        Config::set('inno.system.front_locale', 'en');

        // The seeded storefront locales are served on prefixed routes.
        $this->registerLocaleRoute('en');
        $this->registerLocaleRoute('zh-cn');
    }

    protected function tearDown(): void
    {
        LocaleRepo::$enabledLocales = null;

        parent::tearDown();
    }

    #[Test]
    public function the_url_locale_prefix_wins_over_the_ip_country(): void
    {
        $this->createLocale('ja');

        $resolver = $this->mockResolver();
        $resolver->shouldNotReceive('resolveFromIp');

        $response = $this->runMiddleware($this->request('/ja/products'));

        $this->assertSame('ja', app()->getLocale());
        $this->assertNotInstanceOf(RedirectResponse::class, $response);
    }

    #[Test]
    public function the_session_locale_wins_over_the_ip_country(): void
    {
        $this->createLocale('ja');
        session(['locale' => 'ja']);

        $resolver = $this->mockResolver();
        $resolver->shouldNotReceive('resolveFromIp');

        $this->runMiddleware($this->request('/'));

        $this->assertSame('ja', app()->getLocale());
    }

    #[Test]
    public function it_uses_the_country_of_the_visitor_ip_when_no_preference_exists(): void
    {
        $this->createLocale('ja');

        $resolver = $this->mockResolver();
        $resolver->shouldReceive('resolveFromIp')->once()->with('8.8.8.8', $this->anything())->andReturn('ja');

        $this->enableIpDetection();

        $this->runMiddleware($this->request('/'));

        $this->assertSame('ja', app()->getLocale());
        $this->assertSame('ja', session('locale'));
    }

    #[Test]
    public function it_ignores_a_locale_that_has_no_storefront_routes(): void
    {
        // 'ja' is enabled in the panel but its routes are not registered yet
        $this->createLocaleWithoutRoute('ja');

        $resolver = $this->mockResolver();
        $resolver->shouldReceive('resolveFromIp')->once()->with('8.8.8.8', $this->anything())->andReturn('ja');

        $this->enableIpDetection();

        $this->runMiddleware($this->request('/'));

        // Serving 'ja' would send the visitor to a 404, so the default is used
        $this->assertSame('en', app()->getLocale());
    }

    #[Test]
    public function it_does_not_redirect_to_a_locale_without_storefront_routes(): void
    {
        $this->createLocaleWithoutRoute('ja');
        session(['locale' => 'ja']);

        $this->enableIpDetection();

        $response = $this->runMiddleware($this->request('/'));

        $this->assertNotInstanceOf(RedirectResponse::class, $response);
    }

    #[Test]
    public function it_does_not_detect_the_ip_country_when_the_feature_is_disabled(): void
    {
        $this->createLocale('ja');

        $resolver = $this->mockResolver();
        $resolver->shouldNotReceive('resolveFromIp');

        $this->runMiddleware($this->request('/', 'GET', ['HTTP_ACCEPT_LANGUAGE' => 'ja-JP,ja;q=0.9']));

        $this->assertSame('ja', app()->getLocale());
    }

    #[Test]
    public function it_falls_back_to_the_browser_language_when_the_ip_yields_no_locale(): void
    {
        $this->createLocale('ja');

        $resolver = $this->mockResolver();
        $resolver->shouldReceive('resolveFromIp')->once()->andReturn(null);

        $this->enableIpDetection();

        $this->runMiddleware($this->request('/', 'GET', ['HTTP_ACCEPT_LANGUAGE' => 'ja-JP,ja;q=0.9']));

        $this->assertSame('ja', app()->getLocale());
    }

    #[Test]
    public function it_falls_back_to_the_storefront_default_when_nothing_matches(): void
    {
        Config::set('inno.system.front_locale', 'zh-cn');

        $resolver = $this->mockResolver();
        $resolver->shouldReceive('resolveFromIp')->once()->andReturn(null);

        $this->enableIpDetection();

        $this->runMiddleware($this->request('/'));

        $this->assertSame('zh-cn', app()->getLocale());
    }

    #[Test]
    public function it_skips_the_ip_lookup_for_private_addresses(): void
    {
        $resolver = $this->mockResolver();
        $resolver->shouldNotReceive('resolveFromIp');

        $this->enableIpDetection();

        $this->runMiddleware($this->request('/', 'GET', ['REMOTE_ADDR' => '192.168.1.10']));

        $this->assertSame('en', app()->getLocale());
    }

    #[Test]
    public function it_redirects_the_storefront_root_to_the_locale_prefixed_url(): void
    {
        $resolver = $this->mockResolver();
        $resolver->shouldReceive('resolveFromIp')->once()->andReturn('zh-cn');

        $this->enableIpDetection();

        $response = $this->runMiddleware($this->request('/'));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('/zh-cn', $response->headers->get('Location'));
    }

    #[Test]
    public function it_keeps_the_query_string_when_redirecting(): void
    {
        $resolver = $this->mockResolver();
        $resolver->shouldReceive('resolveFromIp')->once()->andReturn('zh-cn');

        $this->enableIpDetection();

        $response = $this->runMiddleware($this->request('/?utm_source=test'));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('utm_source=test', $response->headers->get('Location'));
    }

    #[Test]
    public function it_does_not_redirect_when_the_feature_is_disabled(): void
    {
        $response = $this->runMiddleware($this->request('/'));

        $this->assertNotInstanceOf(RedirectResponse::class, $response);
    }

    #[Test]
    public function it_does_not_redirect_when_the_visitor_gets_the_default_language(): void
    {
        $resolver = $this->mockResolver();
        $resolver->shouldReceive('resolveFromIp')->once()->andReturn('en');

        $this->enableIpDetection();

        $response = $this->runMiddleware($this->request('/'));

        $this->assertNotInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('en', app()->getLocale());
    }

    #[Test]
    public function it_does_not_redirect_paths_other_than_the_storefront_root(): void
    {
        $resolver = $this->mockResolver();
        $resolver->shouldReceive('resolveFromIp')->once()->andReturn('zh-cn');

        $this->enableIpDetection();

        $response = $this->runMiddleware($this->request('/products'));

        $this->assertNotInstanceOf(RedirectResponse::class, $response);
    }

    #[Test]
    public function it_does_not_redirect_non_get_requests(): void
    {
        $resolver = $this->mockResolver();
        $resolver->shouldReceive('resolveFromIp')->once()->andReturn('zh-cn');

        $this->enableIpDetection();

        $response = $this->runMiddleware($this->request('/', 'POST'));

        $this->assertNotInstanceOf(RedirectResponse::class, $response);
    }

    #[Test]
    public function it_stores_the_resolved_locale_in_the_session(): void
    {
        $resolver = $this->mockResolver();
        $resolver->shouldReceive('resolveFromIp')->once()->andReturn('zh-cn');

        $this->enableIpDetection();

        $this->runMiddleware($this->request('/'));

        $this->assertSame('zh-cn', session('locale'));
    }

    /**
     * Run the middleware and return whatever it produced.
     */
    private function runMiddleware(Request $request)
    {
        $middleware = new SetFrontLocale;

        return $middleware->handle($request, fn (Request $request) => new Response('next'));
    }

    /**
     * Build a request with the given server variables.
     *
     * The Accept-Language header is emptied by default: Symfony's
     * Request::create() would otherwise inject "en-US,en;q=0.5" and the
     * browser language step would always win over the storefront default.
     */
    private function request(string $uri, string $method = 'GET', array $server = []): Request
    {
        return Request::create(
            $uri,
            $method,
            [],
            [],
            [],
            array_merge([
                'REMOTE_ADDR'          => '8.8.8.8',
                'HTTP_ACCEPT_LANGUAGE' => '',
            ], $server)
        );
    }

    /**
     * Mock the country → locale resolver.
     *
     * No default expectation is declared on purpose: Mockery evaluates the
     * expectations in the order they were declared, so a permissive default
     * would shadow the precise per-test expectation.
     */
    private function mockResolver()
    {
        return $this->mock(CountryLocaleResolver::class);
    }

    /**
     * Turn the IP based locale detection on.
     */
    private function enableIpDetection(): void
    {
        Config::set('inno.system.auto_detect_locale_by_ip', true);
    }

    /**
     * Enable an extra locale for the duration of the test.
     *
     * The locale is enabled and served on a prefixed route.
     */
    private function createLocale(string $code): void
    {
        $this->enableLocale($code);
        $this->registerLocaleRoute($code);
    }

    /**
     * Enable a locale without registering its storefront routes.
     *
     * This is the state of a language that was switched on in the panel after
     * the last route cache clear.
     */
    private function createLocaleWithoutRoute(string $code): void
    {
        $this->enableLocale($code);
    }

    /**
     * Enable a locale in the locales table.
     */
    private function enableLocale(string $code): void
    {
        Locale::query()->create([
            'name'     => $code,
            'code'     => $code,
            'image'    => "images/flags/{$code}.svg",
            'position' => 99,
            'active'   => true,
        ]);

        LocaleRepo::$enabledLocales = null;
    }

    /**
     * Register a storefront route for a locale code.
     */
    private function registerLocaleRoute(string $code): void
    {
        Route::get('/__fake_locale_route_'.$code, fn () => '')->name($code.'.front.__fake');
    }
}
