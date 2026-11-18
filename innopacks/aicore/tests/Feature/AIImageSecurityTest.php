<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use InnoShop\Aicore\AicoreServiceProvider;
use InnoShop\Aicore\Services\MediaImageResolver;
use InnoShop\Restapi\Middleware\EnsurePanelPermission;
use InnoShop\Restapi\Middleware\EnsureUserIsAdmin;
use ReflectionMethod;
use Tests\TestCase;

class AIImageSecurityTest extends TestCase
{
    public function test_panel_ai_routes_require_an_authenticated_admin_and_are_throttled(): void
    {
        $provider = new AicoreServiceProvider($this->app);
        $method   = new ReflectionMethod(AicoreServiceProvider::class, 'registerPanelApiRoutes');
        $method->invoke($provider);

        foreach (['api.panel.ai.generate_image', 'api.panel.ai.models_info'] as $name) {
            $route      = Route::getRoutes()->getByName($name);
            $middleware = $route->gatherMiddleware();

            $this->assertContains('auth:sanctum', $middleware);
            $this->assertContains(EnsureUserIsAdmin::class, $middleware);
            $this->assertContains(EnsurePanelPermission::class, $middleware);
            $this->assertContains('throttle:30,1', $middleware);
        }
    }

    public function test_reference_images_are_confined_to_the_media_disk(): void
    {
        Storage::fake('media');
        Storage::disk('media')->put('references/example.png', 'image-data');

        $resolver = MediaImageResolver::getInstance();

        $expected = realpath(Storage::disk('media')->path('references/example.png'));
        $this->assertSame($expected, $resolver->resolveLocalImagePath('references/example.png'));
        $this->assertSame($expected, $resolver->resolveLocalImagePath('static/media/references/example.png'));
        $this->assertSame($expected, $resolver->resolveLocalImagePath('https://shop.test/static/media/references/example.png'));

        $this->assertNull($resolver->resolveLocalImagePath(__FILE__));
        $this->assertNull($resolver->resolveLocalImagePath('/etc/passwd'));
        $this->assertNull($resolver->resolveLocalImagePath('../outside.txt'));
        $this->assertNull($resolver->resolveLocalImagePath('references/missing.png'));
    }

    public function test_media_symlinks_cannot_escape_to_a_local_file(): void
    {
        Storage::fake('media');
        $root = realpath(Storage::disk('media')->path(''));
        $link = $root.DIRECTORY_SEPARATOR.'escape.png';

        if (! function_exists('symlink') || ! @symlink(__FILE__, $link)) {
            $this->markTestSkipped('Symlinks are unavailable in this environment.');
        }

        $this->assertNull(MediaImageResolver::getInstance()->resolveLocalImagePath('escape.png'));
    }
}
