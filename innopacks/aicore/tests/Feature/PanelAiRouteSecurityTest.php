<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use InnoShop\Common\Models\Admin;
use InnoShop\Panel\Repositories\RouteRepo;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PanelAiRouteSecurityTest extends TestCase
{
    public function test_panel_web_ai_routes_require_admin_auth_and_are_throttled(): void
    {
        // Assert against the routes registered during normal app boot. Do
        // NOT re-register them here: a provider regression must fail this
        // test instead of being masked by a test-registered copy.
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

    public function test_list_models_denies_admin_without_settings_permission(): void
    {
        // id 1 is the super-admin (Gate::before bypass); consume it so the
        // second admin is a genuine low-privilege account.
        $this->createAdmin('root@example.com');
        $lowPriv = $this->createAdmin('lowpriv@example.com');

        $this->actingAs($lowPriv, 'admin');

        $response = $this->postJson(panel_route('content_ai.list_models'), [
            'provider_code' => 'openai',
            'api_key'       => 'sk-test',
            'base_url'      => 'https://api.openai.com/v1',
        ]);

        $response->assertStatus(403);
    }

    public function test_list_models_allows_admin_with_settings_permission(): void
    {
        $this->createAdmin('root2@example.com');
        $editor = $this->createAdmin('settings-editor@example.com');
        Permission::findOrCreate('settings_update', 'admin');
        $editor->givePermissionTo('settings_update');

        $this->actingAs($editor, 'admin');

        // Missing provider_code fails validation AFTER the permission gate,
        // proving the gate let this admin through without any HTTP call.
        $response = $this->postJson(panel_route('content_ai.list_models'), []);

        $this->assertNotSame(403, $response->getStatusCode());
        $response->assertJsonPath('message', 'Missing provider_code');
    }

    private function createAdmin(string $email): Admin
    {
        return Admin::query()->create([
            'name'     => 'Test Admin',
            'email'    => $email,
            'password' => Hash::make('password'),
            'locale'   => 'en',
            'active'   => true,
        ]);
    }
}
