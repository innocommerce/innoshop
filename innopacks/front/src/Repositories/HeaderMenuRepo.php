<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Repositories;

use Exception;
use InnoShop\Common\Models\Catalog;
use InnoShop\Common\Models\Category;
use InnoShop\Common\Models\Page;
use InnoShop\Common\Repositories\CatalogRepo;
use InnoShop\Common\Repositories\CategoryRepo;
use InnoShop\Common\Repositories\PageRepo;
use InnoShop\Common\Repositories\SpecialPageRepo;
use InnoShop\Common\Resources\CatalogSimple;
use InnoShop\Common\Resources\PageSimple;

/**
 * @phpstan-consistent-constructor
 */
class HeaderMenuRepo
{
    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * Generate header menus for frontend.
     *
     * The menu tree costs ~90 queries (two-level categories, each with
     * translations, plus catalogs and pages) on EVERY page render, so the
     * assembled array is cached. The cache key fingerprints the menu
     * settings and the max updated_at of the three source tables, so
     * admin edits (re-assignments AND renames) invalidate immediately;
     * the TTL is only a backstop.
     *
     * @return array
     * @throws Exception
     */
    public function getMenus(): array
    {
        $settings = [
            system_setting('menu_header_specials'),
            system_setting('menu_header_categories'),
            system_setting('menu_header_catalogs'),
            system_setting('menu_header_pages'),
        ];

        $stamp = (string) Category::query()->max('updated_at')
            .(string) Catalog::query()->max('updated_at')
            .(string) Page::query()->max('updated_at');
        $cacheKey = 'header_menus:'.app()->getLocale().':'.md5(json_encode($settings)).':'.md5($stamp);

        $menus = cache()->get($cacheKey);
        if (is_array($menus)) {
            return $menus;
        }

        $menus = array_merge(
            $this->getSpecials($settings[0]),
            $this->getCategories($settings[1]),
            $this->getCatalogs($settings[2]),
            $this->getPages($settings[3]),
        );

        $menus = fire_hook_filter('global.header.menus', $menus);
        cache()->put($cacheKey, $menus, now()->addDay());

        return $menus;
    }

    /**
     * @param  $specials
     * @return array
     * @throws Exception
     */
    public function getSpecials($specials): array
    {
        if (empty($specials)) {
            return [];
        }

        return SpecialPageRepo::getInstance()->getSpecialLinks($specials);
    }

    /**
     * @param  $categoryIds
     * @return array
     */
    public function getCategories($categoryIds): array
    {
        if (empty($categoryIds)) {
            return [];
        }

        return CategoryRepo::getInstance()->getTwoLevelCategories($categoryIds);
    }

    /**
     * @param  $catalogIds
     * @return array
     */
    public function getCatalogs($catalogIds): array
    {
        if (empty($catalogIds)) {
            return [];
        }

        $catalogs = CatalogRepo::getInstance()
            ->builder(['active' => true, 'parent_id' => 0, 'catalog_ids' => $catalogIds])
            ->orderBy('position')
            ->get();

        return CatalogSimple::collection($catalogs)->jsonSerialize();
    }

    /**
     * @param  $pageIds
     * @return array
     */
    public function getPages($pageIds): array
    {
        if (empty($pageIds)) {
            return [];
        }

        $catalogs = PageRepo::getInstance()
            ->builder(['active' => true, 'page_ids' => $pageIds])
            ->orderBy('position')
            ->get();

        return PageSimple::collection($catalogs)->jsonSerialize();
    }
}
