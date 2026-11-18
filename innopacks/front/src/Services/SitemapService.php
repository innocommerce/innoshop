<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Repositories\CategoryRepo;
use InnoShop\Common\Repositories\PageRepo;
use InnoShop\Common\Repositories\ProductRepo;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Sitemap as SitemapTag;
use Spatie\Sitemap\Tags\Url;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shopify-style split sitemap: /sitemap.xml is a sitemap index pointing to
 * per-locale, per-type child files like /{locale}/sitemap_products_1.xml.
 */
class SitemapService extends BaseService
{
    /**
     * Maximum URLs per child sitemap file (protocol hard limit is 50,000).
     */
    private const CHUNK_SIZE = 5000;

    /**
     * Rows fetched per query when building a child file. Hydrating a full
     * 5,000-row chunk of products exhausts the 128M memory limit, so each
     * file is assembled from smaller batches.
     */
    private const BATCH_SIZE = 500;

    /**
     * Child sitemap types, rendered as sitemap_{type}_{page}.xml.
     */
    public const TYPES = ['products', 'categories', 'articles', 'pages', 'misc'];

    private const INDEX_CACHE_TTL = 3600;

    private const CHILD_CACHE_TTL = 21600;

    /**
     * Render sitemap.xml as a sitemap index.
     *
     * @param  $request
     * @return Response
     * @throws Exception
     */
    public function response($request): Response
    {
        $xml = Cache::remember('sitemap_index', self::INDEX_CACHE_TTL, fn () => $this->buildIndex()->render());

        return response($xml, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * Render a child sitemap for the given type and page.
     *
     * @param  $request
     * @param  string  $type
     * @param  int  $page
     * @return Response
     * @throws Exception
     */
    public function typeResponse($request, string $type, int $page): Response
    {
        $locale = $this->requestLocale();

        $xml = Cache::remember(
            "sitemap_child_{$locale}_{$type}_{$page}",
            self::CHILD_CACHE_TTL,
            fn () => $this->buildType($locale, $type, $page)->render()
        );

        return response($xml, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * Build the sitemap index over types x locales. Child entries carry the
     * type's latest updated_at so crawlers can skip unchanged files.
     *
     * @return SitemapIndex
     * @throws Exception
     */
    private function buildIndex(): SitemapIndex
    {
        $index = SitemapIndex::create();

        // Packs may append their own types (e.g. suppliers) via this filter.
        $types = fire_hook_filter('sitemap.types', self::TYPES);

        foreach ($types as $type) {
            [$total, $lastModified] = $type === 'misc'
                ? [1, null]
                : $this->typeStats($type);
            if ($total < 1) {
                continue;
            }

            $pages = (int) ceil($total / self::CHUNK_SIZE);
            foreach (enabled_locale_codes() as $locale) {
                for ($page = 1; $page <= $pages; $page++) {
                    $childUrl = $this->frontRoute($locale, 'sitemap.type', ['type' => $type, 'page' => $page]);
                    if ($childUrl === '') {
                        continue;
                    }

                    $tag = SitemapTag::create($childUrl);
                    if ($lastModified !== null) {
                        $tag->setLastModificationDate($lastModified);
                    }
                    $index->add($tag);
                }
            }
        }

        return $index;
    }

    /**
     * Record count and latest updated_at for a content type (locale independent).
     *
     * @param  string  $type
     * @return array{0: int, 1: Carbon|null}
     * @throws Exception
     */
    private function typeStats(string $type): array
    {
        $query = match ($type) {
            'products'   => ProductRepo::getInstance()->builder(['active' => true]),
            'categories' => CategoryRepo::getInstance()->builder(['active' => true]),
            'articles'   => ArticleRepo::getInstance()->builder(['active' => true]),
            'pages'      => PageRepo::getInstance()->builder(['active' => true]),
            default      => null,
        };

        if ($query === null) {
            // Hooked types (e.g. suppliers) report their own count and lastmod.
            $stats        = fire_hook_filter('sitemap.type.stats', ['type' => $type, 'total' => 0, 'last_modified' => null]);
            $lastModified = $stats['last_modified'];

            return [(int) $stats['total'], $lastModified ? new Carbon($lastModified) : null];
        }

        $lastModified = $query->max('updated_at');

        return [$query->count(), $lastModified ? new Carbon($lastModified) : null];
    }

    /**
     * Build one child sitemap.
     *
     * @param  string  $locale
     * @param  string  $type
     * @param  int  $page
     * @return Sitemap
     * @throws Exception
     */
    private function buildType(string $locale, string $type, int $page): Sitemap
    {
        $sitemap = Sitemap::create();
        $offset  = ($page - 1) * self::CHUNK_SIZE;

        match ($type) {
            'products'   => $this->addProducts($sitemap, $locale, $offset),
            'categories' => $this->addCategories($sitemap, $locale, $offset),
            'articles'   => $this->addArticles($sitemap, $locale, $offset),
            'pages'      => $this->addPages($sitemap, $locale, $offset),
            'misc'       => $this->addMisc($sitemap, $locale),
            default      => $this->addHookedUrls($sitemap, $locale, $type, $offset),
        };

        return $sitemap;
    }

    /**
     * @param  Sitemap  $sitemap
     * @param  string  $locale
     * @param  int  $offset
     * @return void
     * @throws Exception
     */
    private function addProducts(Sitemap $sitemap, string $locale, int $offset): void
    {
        for ($i = 0; $i < self::CHUNK_SIZE; $i += self::BATCH_SIZE) {
            $products = ProductRepo::getInstance()->builder(['active' => true])
                ->with('translations')
                ->orderBy('id')
                ->offset($offset + $i)
                ->limit(self::BATCH_SIZE)
                ->get();

            if ($products->isEmpty()) {
                break;
            }

            foreach ($products as $item) {
                if ($item->slug) {
                    $url = $this->frontRoute($locale, 'products.slug_show', ['slug' => $item->slug]);
                } else {
                    $url = $this->frontRoute($locale, 'products.show', $item);
                }
                $this->addUrl($sitemap, $url, $item->updated_at, $this->imageUrl($item->image), $item->fallbackName('name'));
            }
        }
    }

    /**
     * @param  Sitemap  $sitemap
     * @param  string  $locale
     * @param  int  $offset
     * @return void
     * @throws Exception
     */
    private function addCategories(Sitemap $sitemap, string $locale, int $offset): void
    {
        $categories = CategoryRepo::getInstance()->builder(['active' => true])
            ->with('translations')
            ->orderBy('id')
            ->offset($offset)
            ->limit(self::CHUNK_SIZE)
            ->get();

        foreach ($categories as $item) {
            if ($item->slug) {
                $url = $this->frontRoute($locale, 'categories.slug_show', ['slug' => $item->slug]);
            } else {
                $url = $this->frontRoute($locale, 'categories.show', $item);
            }
            $this->addUrl($sitemap, $url, $item->updated_at, $this->imageUrl($item->image), $item->fallbackName('name'));
        }
    }

    /**
     * @param  Sitemap  $sitemap
     * @param  string  $locale
     * @param  int  $offset
     * @return void
     * @throws Exception
     */
    private function addArticles(Sitemap $sitemap, string $locale, int $offset): void
    {
        $articles = ArticleRepo::getInstance()->builder(['active' => true])
            ->with('translations')
            ->orderBy('id')
            ->offset($offset)
            ->limit(self::CHUNK_SIZE)
            ->get();

        foreach ($articles as $item) {
            if ($item->slug) {
                $url = $this->frontRoute($locale, 'articles.slug_show', ['slug' => $item->slug]);
            } else {
                $url = $this->frontRoute($locale, 'articles.show', $item);
            }
            $this->addUrl($sitemap, $url, $item->updated_at, $this->imageUrl($item->image), $item->fallbackName('title'));
        }
    }

    /**
     * @param  Sitemap  $sitemap
     * @param  string  $locale
     * @param  int  $offset
     * @return void
     * @throws Exception
     */
    private function addPages(Sitemap $sitemap, string $locale, int $offset): void
    {
        $pages = PageRepo::getInstance()->builder(['active' => true])
            ->orderBy('id')
            ->offset($offset)
            ->limit(self::CHUNK_SIZE)
            ->get();

        foreach ($pages as $item) {
            // Dynamic per-page routes (e.g. PageBuilder plugin) take precedence,
            // then fall back to the standard page-{slug} route.
            $url = $this->frontRoute($locale, 'pages.'.$item->slug);
            if ($url === '') {
                $url = $this->frontRoute($locale, 'pages.slug_show', ['slug' => $item->slug]);
            }
            $this->addUrl($sitemap, $url, $item->updated_at);
        }
    }

    /**
     * Site-level URLs that do not map to a content table.
     *
     * @param  Sitemap  $sitemap
     * @param  string  $locale
     * @return void
     * @throws Exception
     */
    private function addMisc(Sitemap $sitemap, string $locale): void
    {
        $names = ['home.index', 'products.index', 'brands.index', 'register.index', 'login.index'];

        foreach ($names as $name) {
            $this->addUrl($sitemap, $this->frontRoute($locale, $name));
        }
    }

    /**
     * URLs for types contributed by packs through the sitemap.types hook.
     * Each row carries url plus optional updated_at, image and title; the
     * querying and route assembly stay inside the registering pack.
     *
     * @param  Sitemap  $sitemap
     * @param  string  $locale
     * @param  string  $type
     * @param  int  $offset
     * @return void
     * @throws Exception
     */
    private function addHookedUrls(Sitemap $sitemap, string $locale, string $type, int $offset): void
    {
        $payload = fire_hook_filter('sitemap.type.rows', [
            'type'   => $type,
            'locale' => $locale,
            'offset' => $offset,
            'limit'  => self::CHUNK_SIZE,
            'rows'   => [],
        ]);

        foreach ($payload['rows'] ?? [] as $row) {
            $this->addUrl($sitemap, $row['url'] ?? '', $row['updated_at'] ?? null, $row['image'] ?? '', $row['title'] ?? '');
        }
    }

    /**
     * Resolve the locale for a child sitemap request from the URL segment,
     * falling back to the system default. Session or browser detection must
     * not influence sitemap output.
     *
     * @return string
     */
    private function requestLocale(): string
    {
        $segment = request()->segment(1);
        if ($segment !== null && in_array($segment, enabled_locale_codes(), true)) {
            return $segment;
        }

        return setting_locale_code();
    }

    /**
     * Resolve a locale-prefixed front route, '' when the route does not exist.
     * Public static so hooked packs can assemble their sitemap URLs the same way.
     *
     * @param  $locale
     * @param  $name
     * @param  mixed  $parameters
     * @return string
     * @throws Exception
     */
    public static function frontRoute($locale, $name, mixed $parameters = []): string
    {
        $prefix   = (hide_url_locale() || locales()->isEmpty()) ? 'front.' : $locale.'.front.';
        $fullName = $prefix.$name;
        if (! Route::has($fullName)) {
            return '';
        }

        try {
            return route($fullName, $parameters);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return '';
        }
    }

    /**
     * Resolve a media path to an absolute URL, or '' when empty. The
     * placeholder fallback of image_origin() must never leak into the sitemap.
     *
     * @param  mixed  $image
     * @return string
     */
    public static function imageUrl(mixed $image): string
    {
        if (empty($image) || ! is_string($image)) {
            return '';
        }

        return image_origin($image);
    }

    /**
     * Add a URL entry to the sitemap, with optional last modification date
     * and image (Google image sitemap extension, with localized title).
     *
     * @param  Sitemap  $sitemap
     * @param  string  $url
     * @param  mixed  $lastModified
     * @param  string  $imageUrl
     * @param  string  $imageTitle
     * @return void
     */
    private function addUrl(
        Sitemap $sitemap,
        string $url,
        mixed $lastModified = null,
        string $imageUrl = '',
        string $imageTitle = ''
    ): void {
        if (empty($url)) {
            return;
        }

        $tag = Url::create($url);
        if (! empty($lastModified)) {
            $tag->setLastModificationDate($lastModified);
        }
        if ($imageUrl !== '') {
            $tag->addImage($imageUrl, title: $imageTitle);
        }

        $sitemap->add($tag);
    }
}
