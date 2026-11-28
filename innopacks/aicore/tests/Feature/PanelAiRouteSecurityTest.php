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
use InnoShop\Aicore\AicoreServiceProvider;
use InnoShop\Panel\Repositories\RouteRepo;
use ReflectionMethod;
use Tests\TestCase;

class PanelAiRouteSecurityTest extends TestCase
{
    public function test_panel_web_ai_routes_require_admin_auth_and_are_throttled(): void
    {
        $provider = new AicoreServiceProvider($this->app);
        $method   = new ReflectionMethod(AicoreServiceProvider::class, 'registerPanelRoutes');
        $method->invoke($provider);

        foreach ([
            'panel.content_ai.chat',
            'panel.content_ai.generate',
            'panel.content_ai.generate_batch',
            'panel.content_ai.list_models',
        ] as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, "Missing route: {$name}");

            $middleware = $route->gatherMiddleware();
            $this->assertContains('admin_auth:admin', $middleware, $name);
            $this->assertContains('throttle:30,1', $middleware, $name);
        }
    }

    public function test_guest_cannot_post_to_content_ai_chat(): void
    {
        $response = $this->post(panel_route('content_ai.chat'), [
            'message' => 'hello',
            'history' => [],
        ]);

        $this->assertTrue(
            $response->isRedirection() || in_array($response->getStatusCode(), [401, 403], true),
            'Guest chat request must be rejected, got '.$response->getStatusCode()
        );
    }

    public function test_content_ai_routes_are_ignored_for_per_route_permission_checks(): void
    {
        foreach ([
            'content_ai.generate',
            'content_ai.generate_batch',
            'content_ai.list_models',
            'content_ai.chat',
        ] as $routeCode) {
            $this->assertContains($routeCode, RouteRepo::IGNORE_LIST);
        }
    }
}
