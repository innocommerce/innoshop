<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Components;

use Illuminate\View\Component;

class Sidebar extends Component
{
    public array $menuLinks = [];

    private string $currentRouteName;

    private string $currentPrefix;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $routeNameWithPrefix    = request()->route()->getName();
        $this->currentRouteName = (string) str_replace(panel_name().'.', '', $routeNameWithPrefix);

        $patterns            = explode('.', $this->currentRouteName);
        $this->currentPrefix = $patterns[0];
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return mixed
     */
    public function render(): mixed
    {
        $this->menuLinks = $this->handleMenus($this->getMenus());

        return view('panel::components.sidebar');
    }

    /**
     * Get all menus
     */
    private function getMenus(): array
    {
        return [
            [
                'route' => 'home.index',
                'title' => __('panel::menu.home'),
                'icon'  => 'bi-house',
            ],
            [
                'title'    => __('panel::menu.article'),
                'icon'     => 'bi-files',
                'prefixes' => ['articles'],
                'children' => $this->getArticleSubRoutes(),
            ],
            [
                'title'    => __('panel::menu.catalog'),
                'icon'     => 'bi-folder',
                'prefixes' => ['catalogs'],
                'children' => $this->getCatalogSubRoutes(),
            ],
            [
                'title'    => __('panel::menu.tag'),
                'icon'     => 'bi-tag',
                'prefixes' => ['tags'],
                'children' => $this->getTagSubRoutes(),
            ],
            [
                'title'    => __('panel::menu.page'),
                'icon'     => 'bi-file-text',
                'prefixes' => ['pages'],
                'children' => $this->getPageSubRoutes(),
            ],
            [
                'title'    => __('panel::menu.design'),
                'icon'     => 'bi-brush',
                'children' => $this->getDesignSubRoutes(),
            ],
            [
                'title'    => __('panel::menu.setting'),
                'icon'     => 'bi-gear',
                'children' => $this->getSettingSubRoutes(),
            ],
        ];
    }

    /**
     * Handle menus like whether check or not.
     */
    private function handleMenus($links): array
    {
        $result = [];
        foreach ($links as $index => $link) {
            $topUrl   = $link['url']   ?? '';
            $topRoute = $link['route'] ?? '';
            if (empty($topUrl) && $topRoute) {
                $link['url'] = panel_route($topRoute);
            }

            $parentChecked = false;
            if ($this->checkChildActive($topRoute)) {
                $parentChecked = true;
            }

            $prefixes = $link['prefixes'] ?? [];
            $children = $link['children'] ?? [];

            $link['has_children'] = (bool) $children;
            foreach ($children as $key => $item) {
                $url = $item['url'] ?? '';
                if (empty($url)) {
                    $item['url'] = panel_route($item['route']);
                }
                if ($this->checkChildActive($item['route'])) {
                    $item['active'] = true;
                    $parentChecked  = true;
                } else {
                    $item['active'] = false;
                }

                if (! isset($item['blank'])) {
                    $item['blank'] = false;
                }
                $link['children'][$key] = $item;
            }

            if (! $parentChecked && $this->checkParentActive($prefixes)) {
                $parentChecked = true;
            }

            $result[$index]           = $link;
            $result[$index]['active'] = $parentChecked;
        }

        return $result;
    }

    /**
     * @param  $route
     * @return bool
     */
    private function checkChildActive($route): bool
    {
        if ($route == $this->currentRouteName) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param  $prefixes
     * @return bool
     */
    private function checkParentActive($prefixes): bool
    {
        if ($prefixes && in_array($this->currentPrefix, $prefixes)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get article sub routes
     */
    public function getArticleSubRoutes(): array
    {
        return [
            ['route' => 'articles.index', 'title' => __('panel::article.list')],
            ['route' => 'articles.create', 'title' => __('panel::article.create')],
        ];
    }

    /**
     * Get catalog sub routes
     */
    public function getCatalogSubRoutes(): array
    {
        return [
            ['route' => 'catalogs.index', 'title' => __('panel::catalog.list')],
            ['route' => 'catalogs.create', 'title' => __('panel::catalog.create')],
        ];
    }

    /**
     * Get tag sub routes.
     */
    public function getTagSubRoutes(): array
    {
        return [
            ['route' => 'tags.index', 'title' => __('panel::tag.list')],
            ['route' => 'tags.create', 'title' => __('panel::tag.create')],
        ];
    }

    /**
     * Get page sub routes.
     */
    public function getPageSubRoutes(): array
    {
        return [
            ['route' => 'pages.index', 'title' => __('panel::page.list')],
            ['route' => 'pages.create', 'title' => __('panel::page.create')],
        ];
    }

    /**
     * Get design sub routes.
     */
    public function getDesignSubRoutes(): array
    {
        return [
            ['route' => 'themes.index', 'title' => __('panel::menu.theme')],
        ];
    }

    /**
     * Get setting sub routes.
     */
    public function getSettingSubRoutes(): array
    {
        return [
            ['route' => 'settings.index', 'title' => __('panel::menu.setting')],
            ['route' => 'account.index', 'title' => __('panel::menu.account')],
            ['route' => 'locales.index', 'title' => __('panel::menu.locale')],
        ];
    }
}
