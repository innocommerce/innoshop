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
use Illuminate\Support\Collection;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Repositories\CategoryRepo;
use InnoShop\Common\Repositories\ProductRepo;

class HomeRepo
{
    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * Home slideshow: load settings, resolve links, and resolve localized title/subtitle for the view.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSlideShow(): array
    {
        $slideShow = system_setting('slideshow');
        if (empty($slideShow)) {
            return [];
        }

        $result = [];
        foreach ($slideShow as $item) {
            $linkRaw              = $item['link'] ?? '';
            $parsed               = entity_link_display(is_string($linkRaw) ? $linkRaw : '');
            $item['link']         = $parsed['entity_href'];
            $item['entity_label'] = $parsed['entity_label'] ?? '';
            $item['entity_image'] = $parsed['entity_image'] ?? '';
            $item['entity_price'] = $parsed['entity_price'] ?? '';
            $result[]             = $item;
        }

        return $this->formatSlideShowForHomeView($result);
    }

    /**
     * Map slideshow rows (same shape as the theme setting) to view fields: display_title, locale, etc.
     * If a `home.index.data` hook replaces `slideshow`, pass raw rows in the same shape and call this method to reuse the same logic.
     *
     * @param  array<int, array<string, mixed>>  $slides
     * @return array<int, array<string, mixed>>
     */
    public function formatSlideShowForHomeView(array $slides): array
    {
        if ($slides === []) {
            return [];
        }

        $locale         = front_locale_code();
        $fallbackLocale = (string) config('app.locale', 'en');

        $out = [];
        foreach ($slides as $item) {
            $images = $item['image'] ?? null;
            if (! is_array($images) || ($images[$locale] ?? '') === '') {
                continue;
            }

            $imagePath = (string) ($images[$locale] ?? '');
            $isVideo   = (bool) preg_match('/\.(mp4|webm|ogg)(\?|$)/i', $imagePath);

            $titles    = is_array($item['title'] ?? null) ? $item['title'] : [];
            $subtitles = is_array($item['subtitle'] ?? null) ? $item['subtitle'] : [];

            $displayTitle    = $this->resolveLocalizedSlideText($titles, $locale, $fallbackLocale);
            $displaySubtitle = $this->resolveLocalizedSlideText($subtitles, $locale, $fallbackLocale);

            $item['locale']                = $locale;
            $item['display_title']         = $displayTitle;
            $item['display_subtitle']      = $displaySubtitle;
            $item['image_alt']             = $displayTitle !== '' ? $displayTitle : __('front/common.home');
            $item['has_slideshow_caption'] = $displayTitle !== '' || $displaySubtitle !== '';
            $item['is_video']              = $isVideo;

            $out[] = $item;
        }

        return $out;
    }

    /**
     * Resolve a non-empty string from per-locale rows: current locale, then app fallback, then any locale.
     *
     * @param  array<string, string>  $rows
     */
    private function resolveLocalizedSlideText(array $rows, string $locale, string $fallbackLocale): string
    {
        $v = trim((string) ($rows[$locale] ?? ''));
        if ($v !== '') {
            return $v;
        }
        $v = trim((string) ($rows[$fallbackLocale] ?? ''));
        if ($v !== '') {
            return $v;
        }
        foreach ($rows as $one) {
            $t = trim((string) $one);
            if ($t !== '') {
                return $t;
            }
        }

        return '';
    }

    /**
     * Format product data for home page (core fields only).
     *
     * Extensions (e.g. supplier/seller) must use hook:
     * `front.repo.home.format_product_data`
     *
     * Payload shape:
     * - `data`: array shown to the view (keys below are the baseline).
     * - `product`: the Product model instance.
     *
     * Listeners must return the same payload shape with `data` updated (and may keep `product`).
     *
     * @param  Product  $product
     * @return array<string, mixed>
     */
    public function formatProductData(Product $product): array
    {
        $masterSku = $product->masterSku;
        $price     = $masterSku ? $masterSku->price : $product->price;
        $moq       = $masterSku ? $masterSku->quantity : 0;
        $image     = $product->getImageUrl(400, 400);

        $productName = $product->fallbackName();

        $data = [
            'id'       => $product->id,
            'name'     => $productName,
            'image'    => $image,
            'url'      => $product->url,
            'price'    => number_format($price, 2),
            'moq'      => $moq,
            'category' => '',
            'sku_id'   => $masterSku ? $masterSku->id : null,
        ];

        $payload = [
            'data'    => $data,
            'product' => $product,
        ];

        $payload = fire_hook_filter('front.repo.home.format_product_data', $payload);

        if (! is_array($payload) || ! isset($payload['data']) || ! is_array($payload['data'])) {
            return $data;
        }

        return $payload['data'];
    }

    /**
     * Get home categories from settings
     *
     * @return array
     * @throws Exception
     */
    public function getHomeCategories(): array
    {
        $categoryIds = system_setting('home_categories', []);

        // Handle both string and array return types from system_setting
        if (! is_array($categoryIds)) {
            $categoryIds = json_decode($categoryIds, true) ?: [];
        }

        if (empty($categoryIds) || ! is_array($categoryIds)) {
            return [];
        }

        try {
            $categories = CategoryRepo::getInstance()
                ->builder(['category_ids' => $categoryIds, 'active' => true])
                ->with(['translation'])
                ->orderBy('position')
                ->get();

            $formatted = [];
            foreach ($categories as $category) {
                $formatted[] = [
                    'id'          => $category->id,
                    'name'        => $category->fallbackName(),
                    'slug'        => $category->slug,
                    'url'         => $category->url,
                    'image'       => $category->image ? image_resize($category->image, 300, 300) : '',
                    'description' => $category->translation->description ?? '',
                ];
            }

            return $formatted;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Product IDs from `home_hot_products` (admin Theme Settings → 首页产品), in panel order.
     * Duplicates are removed (first occurrence wins).
     *
     * @return array<int, int>
     */
    public function getHomeHotProductIdsOrdered(): array
    {
        $hotProductsSetting = system_setting('home_hot_products', '{}');
        if (is_array($hotProductsSetting)) {
            $hotProductsData = $hotProductsSetting;
        } else {
            $hotProductsData = json_decode($hotProductsSetting, true) ?: [];
        }

        $ids    = [];
        $groups = $hotProductsData['floors'] ?? [];
        foreach ($groups as $group) {
            foreach ($group['products'] ?? [] as $productId) {
                $id = (int) $productId;
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }

        $seen   = [];
        $unique = [];
        foreach ($ids as $id) {
            if (! isset($seen[$id])) {
                $seen[$id] = true;
                $unique[]  = $id;
            }
        }

        return $unique;
    }

    /**
     * Active Product models for theme home grids, matching admin order from `home_hot_products`.
     * Returns empty collection when the setting has no product IDs.
     *
     * @return Collection<int, Product>
     */
    public function getHomeHotProductsOrdered(): Collection
    {
        $orderedIds = $this->getHomeHotProductIdsOrdered();
        if ($orderedIds === []) {
            return collect();
        }

        $products = ProductRepo::getInstance()
            ->builder(['active' => true])
            ->whereIn('products.id', $orderedIds)
            ->with(['masterSku'])
            ->get()
            ->keyBy('id');

        $ordered = collect();
        foreach ($orderedIds as $id) {
            $product = $products->get($id);
            if ($product) {
                $ordered->push($product);
            }
        }

        return $ordered;
    }

    /**
     * Get home articles from settings. Falls back to latest 3 when setting is empty.
     *
     * @return array
     */
    public function getHomeArticles(): array
    {
        $articleIds = system_setting('home_articles', []);

        if (! is_array($articleIds)) {
            $articleIds = json_decode($articleIds, true) ?: [];
        }

        // Fallback to latest 3 articles when no setting configured
        if (empty($articleIds) || ! is_array($articleIds)) {
            $articles = ArticleRepo::getInstance()->getLatestArticles(3);
        } else {
            $articles = ArticleRepo::getInstance()->getListByArticleIDs($articleIds);
        }

        $formatted = [];
        foreach ($articles as $article) {
            $catalogName = $article->catalog
                ? $article->catalog->fallbackName('title')
                : '';

            $formatted[] = [
                'id'           => $article->id,
                'title'        => $article->title,
                'summary'      => $article->summary,
                'image'        => $article->image ? image_resize($article->image, 400, 300) : '',
                'url'          => $article->url,
                'catalog_name' => $catalogName,
                'created_at'   => $article->created_at->format('Y-m-d'),
            ];
        }

        return $formatted;
    }
}
