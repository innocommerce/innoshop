<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Models;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\File;
use InnoShop\Common\Models\Locale;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

/**
 * The storefront registers its /{locale} routes while the application boots,
 * so a locale change has to drop the compiled route cache.
 */
class LocaleRouteCacheTest extends TestCase
{
    /**
     * A relative path so the application resolves it against its base path
     * instead of prefixing it with the base path again.
     */
    private const CACHE_PATH = 'storage/framework/testing/routes-cache-test.php';

    private string $cachePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cachePath          = base_path(self::CACHE_PATH);
        $_ENV['APP_ROUTES_CACHE'] = self::CACHE_PATH;
    }

    protected function tearDown(): void
    {
        unset($_ENV['APP_ROUTES_CACHE']);

        File::delete($this->cachePath);

        parent::tearDown();
    }

    #[Test]
    public function it_clears_the_route_cache_when_a_locale_is_created(): void
    {
        $this->seedRouteCache();

        $this->createLocale('ja');

        $this->assertFileDoesNotExist($this->cachePath);
    }

    #[Test]
    public function it_clears_the_route_cache_when_a_locale_is_updated(): void
    {
        $locale = $this->createLocale('ja');

        $this->seedRouteCache();

        $locale->update(['active' => false]);

        $this->assertFileDoesNotExist($this->cachePath);
    }

    #[Test]
    public function it_clears_the_route_cache_when_a_locale_is_deleted(): void
    {
        $locale = $this->createLocale('ja');

        $this->seedRouteCache();

        $locale->delete();

        $this->assertFileDoesNotExist($this->cachePath);
    }

    #[Test]
    public function it_does_nothing_when_the_site_has_no_route_cache(): void
    {
        $this->assertFileDoesNotExist($this->cachePath);

        $locale = $this->createLocale('ja');

        $this->assertSame('ja', $locale->code);
        $this->assertFileDoesNotExist($this->cachePath);
    }

    #[Test]
    public function it_stays_inert_without_an_application_container(): void
    {
        $original = Container::getInstance();

        try {
            // A bare container: no Application bound, the same situation as a
            // container-less unit test or an early bootstrap.
            Container::setInstance(new Container);
            $this->assertFalse(Container::getInstance()->bound('app'));

            $method = new ReflectionMethod(Locale::class, 'clearRouteCache');
            $method->setAccessible(true);
            $method->invoke(null);

            $this->assertTrue(true, 'clearing the route cache must never throw');
        } finally {
            Container::setInstance($original);
        }
    }

    /**
     * Write a placeholder route cache file.
     */
    private function seedRouteCache(): void
    {
        File::ensureDirectoryExists(dirname($this->cachePath));
        File::put($this->cachePath, '<?php return [];');
    }

    /**
     * Enable a locale.
     */
    private function createLocale(string $code): Locale
    {
        return Locale::query()->create([
            'name'     => $code,
            'code'     => $code,
            'image'    => "images/flags/{$code}.svg",
            'position' => 99,
            'active'   => true,
        ]);
    }
}
