<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Repositories;

use Illuminate\Support\Facades\Cache;
use InnoShop\Plugin\Core\Plugin;
use InnoShop\Plugin\Repositories\PluginTypeRepo;

class MenuSearchRepo
{
    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * Search menus by keyword.
     *
     * @param  string  $keyword
     * @return array
     */
    public function search(string $keyword = ''): array
    {
        $admin = current_admin();
        if (! $admin) {
            return [];
        }

        $cacheKey = 'menu_search:'.$admin->getAuthIdentifier().':'.app()->getLocale();
        $items    = Cache::remember($cacheKey, 60, fn () => $this->getSearchableMenus());

        if ($keyword === '') {
            return $items;
        }

        $keyword = mb_strtolower($keyword);

        return array_values(array_filter($items, function ($item) use ($keyword) {
            $haystack = mb_strtolower($item['title'].' '.($item['keywords'] ?? ''));

            return str_contains($haystack, $keyword);
        }));
    }

    /**
     * Get all searchable menus.
     *
     * @return array
     */
    public function getSearchableMenus(): array
    {
        $admin = current_admin();
        if (! $admin) {
            return [];
        }

        $items = [];
        $seen  = [];

        $menuTree = $this->getMenuTree();

        foreach ($menuTree as $group) {
            $groupTitle = $group['title'] ?? '';
            $children   = $group['children'] ?? [];

            foreach ($children as $child) {
                $route = $child['route'] ?? '';
                if (empty($route)) {
                    continue;
                }

                // Permission check
                $routeCode = str_replace('.', '_', $route);
                if (! $admin->can($routeCode)) {
                    continue;
                }

                $seen[] = $route;

                $items[] = [
                    'title'    => $child['title'] ?? '',
                    'url'      => $this->resolveUrl($route, $child['url'] ?? ''),
                    'keywords' => $groupTitle,
                ];
            }
        }

        // Supplement with enabled plugins
        $pluginItems = $this->getSupplementPluginRoutes($seen, $admin);

        return array_merge($items, $pluginItems);
    }

    /**
     * Build the menu tree with translations, same structure as Sidebar::getMenus().
     */
    private function getMenuTree(): array
    {
        return fire_hook_filter('panel.component.sidebar.menus', [
            [
                'title'    => __('panel/menu.dashboard'),
                'children' => [
                    ['route' => 'home.index', 'title' => __('panel/menu.dashboard')],
                ],
            ],
            [
                'title'    => __('panel/menu.top_product'),
                'children' => $this->getProductRoutes(),
            ],
            [
                'title'    => __('panel/menu.top_order'),
                'children' => $this->getOrderRoutes(),
            ],
            [
                'title'    => __('panel/menu.top_customer'),
                'children' => $this->getCustomerRoutes(),
            ],
            [
                'title'    => __('panel/menu.top_marketing'),
                'children' => $this->getMarketingRoutes(),
            ],
            [
                'title'    => __('panel/menu.top_content'),
                'children' => $this->getContentRoutes(),
            ],
            [
                'title'    => __('panel/menu.top_design'),
                'children' => $this->getDesignRoutes(),
            ],
            [
                'title'    => __('panel/menu.top_analytic'),
                'children' => $this->getAnalyticRoutes(),
            ],
            [
                'title'    => __('panel/menu.top_plugin'),
                'children' => $this->getPluginRoutes(),
            ],
            [
                'title'    => __('panel/menu.top_setting'),
                'children' => $this->getSettingRoutes(),
            ],
        ]);
    }

    private function getProductRoutes(): array
    {
        return fire_hook_filter('panel.component.sidebar.product.routes', [
            ['route' => 'products.index', 'title' => __('panel/menu.products')],
            ['route' => 'categories.index', 'title' => __('panel/menu.categories')],
            ['route' => 'brands.index', 'title' => __('panel/menu.brands')],
            ['route' => 'attributes.index', 'title' => __('panel/menu.attributes')],
            ['route' => 'options.index', 'title' => __('panel/menu.options')],
            ['route' => 'reviews.index', 'title' => __('panel/menu.reviews')],
        ]);
    }

    private function getOrderRoutes(): array
    {
        return fire_hook_filter('panel.component.sidebar.order.routes', [
            ['route' => 'orders.index', 'title' => __('panel/menu.orders')],
            ['route' => 'order_returns.index', 'title' => __('panel/menu.order_returns')],
        ]);
    }

    private function getCustomerRoutes(): array
    {
        return fire_hook_filter('panel.component.sidebar.customer.routes', [
            ['route' => 'customers.index', 'title' => __('panel/menu.customers')],
            ['route' => 'customer_groups.index', 'title' => __('panel/menu.customer_groups')],
            ['route' => 'transactions.index', 'title' => __('panel/menu.transactions')],
            ['route' => 'withdrawals.index', 'title' => __('panel/menu.withdrawals')],
            ['route' => 'socials.index', 'title' => __('panel/menu.sns')],
        ]);
    }

    private function getMarketingRoutes(): array
    {
        return fire_hook_filter('panel.component.sidebar.marketing.routes', [
            ['route' => 'newsletter_subscribers.index', 'title' => __('panel/menu.newsletter_subscribers')],
            ['route' => 'visits.index', 'title' => __('panel/menu.visits')],
        ]);
    }

    private function getContentRoutes(): array
    {
        return fire_hook_filter('panel.component.sidebar.content.routes', [
            ['route' => 'articles.index', 'title' => __('panel/menu.articles')],
            ['route' => 'catalogs.index', 'title' => __('panel/menu.catalogs')],
            ['route' => 'tags.index', 'title' => __('panel/menu.tags')],
            ['route' => 'pages.index', 'title' => __('panel/menu.pages')],
            ['route' => 'file_manager.index', 'title' => __('panel/menu.file_manager')],
        ]);
    }

    private function getDesignRoutes(): array
    {
        return fire_hook_filter('panel.component.sidebar.design.routes', [
            ['route' => 'themes_settings.index', 'title' => __('panel/menu.themes_settings')],
            ['route' => 'themes.index', 'title' => __('panel/menu.themes')],
        ]);
    }

    private function getAnalyticRoutes(): array
    {
        return fire_hook_filter('panel.component.sidebar.analytic.routes', [
            ['route' => 'analytics.index', 'title' => __('panel/menu.analytics')],
            ['route' => 'analytics_order', 'title' => __('panel/menu.analytics_order')],
            ['route' => 'analytics_product', 'title' => __('panel/menu.analytics_product')],
            ['route' => 'analytics_customer', 'title' => __('panel/menu.analytics_customer')],
            ['route' => 'analytics_visit', 'title' => __('panel/menu.analytics_visit')],
        ]);
    }

    private function getPluginRoutes(): array
    {
        $routes = [
            ['route' => 'plugins.index', 'title' => __('panel/plugin.all')],
        ];

        foreach (PluginTypeRepo::getInstance()->getTypeMenus() as $menu) {
            $routes[] = $menu;
        }

        return fire_hook_filter('panel.component.sidebar.plugin.routes', $routes);
    }

    private function getSettingRoutes(): array
    {
        return fire_hook_filter('panel.component.sidebar.setting.routes', [
            ['route' => 'settings.index', 'title' => __('panel/menu.settings')],
            ['route' => 'plugin_coordination.index', 'title' => __('panel/menu.plugin_coordination')],
            ['route' => 'account.index', 'title' => __('panel/menu.account')],
            ['route' => 'admins.index', 'title' => __('panel/menu.admins')],
            ['route' => 'roles.index', 'title' => __('panel/menu.roles')],
            ['route' => 'countries.index', 'title' => __('panel/menu.countries')],
            ['route' => 'states.index', 'title' => __('panel/menu.states')],
            ['route' => 'regions.index', 'title' => __('panel/menu.regions')],
            ['route' => 'locales.index', 'title' => __('panel/menu.locales')],
            ['route' => 'currencies.index', 'title' => __('panel/menu.currencies')],
            ['route' => 'tax_rates.index', 'title' => __('panel/menu.tax_rates')],
            ['route' => 'tax_classes.index', 'title' => __('panel/menu.tax_classes')],
            ['route' => 'weight_classes.index', 'title' => __('panel/menu.weight_classes')],
        ]);
    }

    /**
     * Resolve URL from route name.
     */
    private function resolveUrl(string $route, string $fallbackUrl = ''): string
    {
        try {
            return panel_route($route);
        } catch (\Exception $e) {
            return $fallbackUrl;
        }
    }

    /**
     * Supplement with enabled plugins that have panel_route.
     *
     * @param  array  $seenRoutes
     * @param  mixed  $admin
     * @return array
     */
    private function getSupplementPluginRoutes(array $seenRoutes, $admin): array
    {
        $items = [];

        if (! $admin->can('plugins_edit')) {
            return [];
        }

        try {
            $plugins = app('plugin')->getPlugins();
        } catch (\Exception $e) {
            return [];
        }

        foreach ($plugins as $plugin) {
            $dirname     = $plugin->getDirname();
            $pluginTitle = $this->getPluginName($plugin);
            $enabled     = $plugin->checkInstalled() && $plugin->getEnabled();

            // Enabled plugin with panel_route: link to its own page
            $panelRoute = $enabled ? $this->getPluginPanelRoute($plugin) : '';
            if (! empty($panelRoute) && ! in_array($panelRoute, $seenRoutes)) {
                $routeCode = str_replace('.', '_', $panelRoute);
                if ($admin->can($routeCode)) {
                    try {
                        $url          = panel_route($panelRoute);
                        $seenRoutes[] = $panelRoute;
                        $items[]      = [
                            'title'    => $pluginTitle,
                            'url'      => $url,
                            'keywords' => $dirname,
                        ];

                        continue;
                    } catch (\Exception $e) {
                        // ignore route resolution errors
                    }
                }
            }

            // All other plugins: link to plugins.edit page
            try {
                $url     = panel_route('plugins.edit', ['plugin' => $dirname]);
                $items[] = [
                    'title'    => $pluginTitle,
                    'url'      => $url,
                    'keywords' => $dirname,
                ];
            } catch (\Exception $e) {
                // ignore
            }
        }

        return $items;
    }

    /**
     * Get panel_route from plugin config.json.
     */
    private function getPluginPanelRoute(Plugin $plugin): string
    {
        $configFile = $plugin->getPath().'/config.json';
        if (! file_exists($configFile)) {
            return '';
        }

        $config = json_decode(file_get_contents($configFile), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return '';
        }

        return $config['panel_route'] ?? '';
    }

    /**
     * Get localized plugin name.
     */
    private function getPluginName(Plugin $plugin): string
    {
        $configFile = $plugin->getPath().'/config.json';
        if (! file_exists($configFile)) {
            return $plugin->getDirname();
        }

        $config = json_decode(file_get_contents($configFile), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $plugin->getDirname();
        }

        $names = $config['name'] ?? [];
        if (! is_array($names)) {
            return (string) $names;
        }

        $locale = app()->getLocale();

        return $names[$locale] ?? $names['en'] ?? $plugin->getDirname();
    }
}
