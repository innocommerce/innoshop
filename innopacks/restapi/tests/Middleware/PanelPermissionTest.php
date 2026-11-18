<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Restapi\Tests\Middleware;

use Database\Factories\AdminFactory;
use Illuminate\Support\Facades\Route;
use InnoShop\Common\Models\Admin;
use InnoShop\Restapi\Middleware\EnsurePanelPermission;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PanelPermissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::prefix('api/panel')->name('api.panel.')->group(function () {
            require __DIR__.'/../../routes/panel-api.php';
        });
    }

    public function test_every_protected_core_route_has_an_explicit_permission_mapping(): void
    {
        $unmapped = [];
        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if (! str_starts_with((string) $name, 'api.panel.') || ! in_array(EnsurePanelPermission::class, $route->gatherMiddleware(), true)) {
                continue;
            }

            $shortName = substr($name, strlen('api.panel.'));
            if (EnsurePanelPermission::permissionsFor($shortName) === null) {
                $unmapped[] = $name;
            }
        }

        $this->assertSame([], $unmapped, 'Every panel API route must have a permission mapping or an explicit authenticated-only exemption.');
        $this->assertNull(EnsurePanelPermission::permissionsFor('new_unmapped_route'));
        $this->assertSame([], EnsurePanelPermission::permissionsFor('dashboard.index'));
        $this->assertSame(['products_create'], EnsurePanelPermission::permissionsFor('products.import'));
        $this->assertContains('products_create', EnsurePanelPermission::permissionsFor('products.names'));
        $this->assertContains('products_create', EnsurePanelPermission::permissionsFor('options.available'));
        $this->assertSame(['options_values'], EnsurePanelPermission::permissionsFor('options.values'));
        $this->assertContains('attributes_update', EnsurePanelPermission::permissionsFor('attribute_values.index'));
        $this->assertContains('upload_images', EnsurePanelPermission::permissionsFor('ai.generate_image'));
    }

    public function test_staff_admin_cannot_update_settings_without_permission(): void
    {
        AdminFactory::new()->create();
        $staff = AdminFactory::new()->create(['email' => 'staff@example.com']);
        Sanctum::actingAs($staff);

        $this->putJson('/api/panel/settings', ['meta_title' => 'attacker-change'])
            ->assertStatus(403)
            ->assertJsonPath('success', false);

        $this->assertNotSame('attacker-change', system_setting('meta_title'));
    }

    public function test_staff_admin_can_update_settings_with_matching_permission(): void
    {
        AdminFactory::new()->create();
        $staff = AdminFactory::new()->create(['email' => 'editor@example.com']);
        $staff->givePermissionTo(Permission::findOrCreate('settings_update', 'admin'));
        Sanctum::actingAs($staff);

        $this->putJson('/api/panel/settings', ['meta_title' => 'allowed-change'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('allowed-change', system_setting('meta_title'));
    }

    public function test_root_admin_retains_full_api_access(): void
    {
        $root = AdminFactory::new()->create();
        $this->assertSame(1, $root->id);
        Sanctum::actingAs($root);

        $this->putJson('/api/panel/settings', ['meta_title' => 'root-change'])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_unmapped_routes_fail_closed_for_an_admin_without_permissions(): void
    {
        $admin = AdminFactory::new()->create(['id' => 2]);
        $this->assertInstanceOf(Admin::class, $admin);
        $this->assertNull(EnsurePanelPermission::permissionsFor('products.new_dangerous_action'));
    }
}
