<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use InnoShop\Common\Models\Locale;
use InnoShop\Common\Repositories\LocaleRepo;
use InnoShop\Common\Services\GeoLocationService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * End to end test of the visitor IP based locale detection: the request goes
 * through the whole HTTP kernel, so the storefront middleware, the session and
 * the redirect are all exercised.
 */
class FrontLocaleDetectionHttpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        LocaleRepo::$enabledLocales = null;
        Config::set('inno.system.auto_detect_locale_by_ip', true);
        Config::set('inno.system.front_locale', 'en');

        // The country of a visitor IP is resolved through the geo location
        // service, which reads the 58MB GeoLite2 mmdb. That database is not
        // part of the repository, so a stub is bound into the container to
        // keep the test independent of it.
        $this->app->instance(GeoLocationService::class, new FakeGeoLocationService);

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
    public function a_chinese_visitor_is_redirected_to_the_chinese_storefront(): void
    {
        $response = $this->withServerVariables(['REMOTE_ADDR' => '114.114.114.114'])->get('/');

        $response->assertRedirect();
        $this->assertStringContainsString('/zh-cn', $response->headers->get('Location'));
    }

    #[Test]
    public function a_japanese_visitor_is_redirected_to_the_japanese_storefront(): void
    {
        $this->createLocale('ja');

        $response = $this->withServerVariables(['REMOTE_ADDR' => '133.242.0.1'])->get('/');

        $response->assertRedirect();
        $this->assertStringContainsString('/ja', $response->headers->get('Location'));
    }

    #[Test]
    public function a_visitor_without_a_mapped_country_falls_back_to_the_default_language(): void
    {
        $response = $this->withServerVariables(['REMOTE_ADDR' => '95.90.28.0'])->get('/');

        $response->assertOk();
    }

    #[Test]
    public function a_local_visitor_is_served_the_storefront_without_a_redirect(): void
    {
        $response = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])->get('/');

        $response->assertOk();
    }

    /**
     * Enable an extra locale for the duration of the test.
     */
    private function createLocale(string $code): void
    {
        Locale::query()->create([
            'name'     => $code,
            'code'     => $code,
            'image'    => "images/flags/{$code}.svg",
            'position' => 99,
            'active'   => true,
        ]);

        LocaleRepo::$enabledLocales = null;

        $this->registerLocaleRoute($code);
    }

    /**
     * Register a storefront route for a locale code.
     *
     * The route is named like the real one so the redirect target is resolved
     * through the router instead of falling back to a hand built URL.
     */
    private function registerLocaleRoute(string $code): void
    {
        if (Route::has($code.'.front.home.index')) {
            return;
        }

        Route::get('/'.$code.'/__fake_home', fn () => '')->name($code.'.front.home.index');
    }
}

/**
 * Stand-in for the GeoLite2 backed geo location service.
 *
 * The real service resolves the country of an IP address from the GeoLite2
 * mmdb, a 58MB binary that is not part of the repository and therefore absent
 * on CI. The stub answers with a fixed country per test IP, so the locale
 * detection chain is exercised no matter where the test runs.
 */
class FakeGeoLocationService extends GeoLocationService
{
    /**
     * Test IP address → ISO 3166-1 alpha-2 country code.
     *
     * 95.90.28.0 maps to a country without a storefront language, so the
     * visitor falls back to the default language.
     */
    private const IP_COUNTRIES = [
        '114.114.114.114' => 'CN',
        '133.242.0.1'     => 'JP',
        '95.90.28.0'      => 'KZ',
    ];

    /**
     * @param  string  $ip
     * @return array
     */
    public function getLocation(string $ip): array
    {
        return [
            'country_code' => self::IP_COUNTRIES[$ip] ?? '',
            'country_name' => '',
            'region_code'  => '',
            'region_name'  => '',
            'city'         => '',
            'latitude'     => null,
            'longitude'    => null,
        ];
    }
}
