<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Services;

use Exception;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Repositories\CategoryRepo;
use InnoShop\Common\Repositories\PageRepo;
use InnoShop\Common\Repositories\ProductRepo;
use Spatie\Sitemap\Sitemap;
use Symfony\Component\HttpFoundation\Response;

class SitemapService extends BaseService
{
    private Sitemap $sitemap;

    public function __construct()
    {
        $this->sitemap = Sitemap::create();
    }

    /**
     * Render sitemap.xml
     * @param  $request
     * @return Response
     * @throws Exception
     */
    public function response($request): Response
    {
        $locales = enabled_locale_codes();
        $this->sitemap->add(route('front.home.index'));

        foreach ($locales as $locale) {
            $this->addSpecials($locale);
            $this->addProducts($locale);
            $this->addCategories($locale);
            $this->addArticles($locale);
            $this->addPages($locale);
        }

        return $this->sitemap->toResponse($request);
    }

    /**
     * @param  $locale
     * @return void
     */
    private function addSpecials($locale): void
    {
        $this->sitemap->add($this->frontRoute($locale, 'register.index'));
        $this->sitemap->add($this->frontRoute($locale, 'login.index'));
        $this->sitemap->add($this->frontRoute($locale, 'products.index'));
        $this->sitemap->add($this->frontRoute($locale, 'brands.index'));
    }

    /**
     * @param  $locale
     * @return void
     * @throws Exception
     */
    private function addProducts($locale): void
    {
        $products = ProductRepo::getInstance()->builder(['active' => true])->limit(1000)->get();
        foreach ($products as $item) {
            if ($item->slug) {
                $url = $this->frontRoute($locale, 'products.slug_show', ['slug' => $item->slug]);
            } else {
                $url = $this->frontRoute($locale, 'products.show', $item);
            }
            $this->sitemap->add($url);
        }
    }

    /**
     * @param  $locale
     * @return void
     */
    private function addCategories($locale): void
    {
        $categories = CategoryRepo::getInstance()->builder(['active' => true])->limit(1000)->get();
        foreach ($categories as $item) {
            if ($item->slug) {
                $url = $this->frontRoute($locale, 'categories.slug_show', ['slug' => $item->slug]);
            } else {
                $url = $this->frontRoute($locale, 'categories.show', $item);
            }
            $this->sitemap->add($url);
        }
    }

    /**
     * @param  $locale
     * @return void
     * @throws Exception
     */
    private function addArticles($locale): void
    {
        $articles = ArticleRepo::getInstance()->builder(['active' => true])->limit(1000)->get();
        foreach ($articles as $item) {
            if ($item->slug) {
                $url = $this->frontRoute($locale, 'articles.slug_show', ['slug' => $item->slug]);
            } else {
                $url = $this->frontRoute($locale, 'articles.show', $item);
            }
            $this->sitemap->add($url);
        }
    }

    /**
     * @param  $locale
     * @return void
     * @throws Exception
     */
    private function addPages($locale): void
    {
        $pages = PageRepo::getInstance()->builder(['active' => true])->limit(1000)->get();
        foreach ($pages as $item) {
            $url = $this->frontRoute($locale, 'pages.'.$item->slug);

            $this->sitemap->add($url);
        }
    }

    /**
     * @param  $locale
     * @param  $name
     * @param  mixed  $parameters
     * @return string
     */
    private function frontRoute($locale, $name, mixed $parameters = []): string
    {
        return route($locale.'.front.'.$name, $parameters);
    }
}
