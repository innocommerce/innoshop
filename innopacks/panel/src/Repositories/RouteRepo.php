<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Repositories;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Models\Role;

class RouteRepo
{
    const IGNORE_LIST = [
        'login.index', 'login.store', 'logout.index', 'dashboard.index', 'locale.switch',
    ];

    private Role $adminRole;

    private array $systemModules = [];

    /**
     * @param  Role  $adminRole
     */
    public function __construct(Role $adminRole)
    {
        $this->adminRole = $adminRole;
    }

    /**
     * @param  Role  $adminRole
     * @return static
     */
    public static function getInstance(Role $adminRole): static
    {
        return new static($adminRole);
    }

    /**
     * @return array
     */
    public function getPanelPermissions(): array
    {
        $permissions = [];
        $panelRoutes = $this->getPanelRoutes();
        foreach ($panelRoutes as $module => $routes) {
            $isSystem = in_array($module, $this->systemModules);
            if ($isSystem) {
                $label = trans("panel::menu.$module");
            } else {
                $module = Str::studly(Str::singular($module));
                $label  = trans("$module::route.title");
            }
            $permissions[] = [
                'is_plugin'   => ! $isSystem,
                'module'      => $module,
                'label'       => $label,
                'permissions' => $this->getPermissionList($routes),
            ];
        }

        return $permissions;
    }

    /**
     * @return array
     */
    public function getPanelRoutes(): array
    {
        $routeList = [];
        $routes    = Route::getRoutes();
        foreach ($routes as $route) {
            $routeName = $route->getName();
            if (! Str::startsWith($route->getName(), 'panel.')) {
                continue;
            }

            $routeName = substr($routeName, strlen('panel.'));
            if (in_array($routeName, self::IGNORE_LIST)) {
                continue;
            }

            $parseNames  = explode('.', $routeName);
            $routeModule = $parseNames[0];
            $controller  = $route->getControllerClass();
            $pluginSpace = $this->getPluginNamespace($controller);
            if (empty($pluginSpace)) {
                $this->systemModules[] = $routeModule;
            }

            $routeList[$routeModule][] = [
                'route_name'   => $routeName,
                'action_name'  => $controller,
                'plugin_space' => $pluginSpace,
            ];
        }

        return $routeList;
    }

    /**
     * @param  $controller
     * @return string
     */
    private function getPluginNamespace($controller): string
    {
        if (Str::startsWith($controller, 'InnoShop\\Panel')
            || Str::startsWith($controller, 'InnoShop\\Plugin')) {
            return '';
        }
        $parts = explode('\\', $controller);

        return $parts[1];
    }

    /**
     * Get permission list by module and route.
     *
     * @param  $routes
     * @return array
     */
    private function getPermissionList($routes): array
    {
        $items = [];
        foreach ($routes as $route) {
            $routeName   = $route['route_name'];
            $pluginSpace = $route['plugin_space'];

            $routeSlug = str_replace('.', '_', $routeName);
            if ($pluginSpace) {
                $label = trans("{$route['plugin_space']}::route.{$routeSlug}");
            } else {
                $label = trans("panel::route.{$routeSlug}");
            }

            $items[] = ['route_slug' => $routeSlug, 'label' => $label, 'selected' => $this->hasPermission($routeSlug)];
        }

        return $items;
    }

    /**
     * Detect current user or role has permission.
     *
     * @param  $permission
     * @return bool
     */
    private function hasPermission($permission): bool
    {
        try {
            return $this->adminRole->hasPermissionTo($permission);
        } catch (PermissionDoesNotExist $e) {
            Log::error($e->getMessage());

            return false;
        }
    }
}
