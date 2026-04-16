<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace InnoShop\Common\Support;

use InnoShop\Common\Models\Article;
use InnoShop\Common\Models\Brand;
use InnoShop\Common\Models\Catalog;
use InnoShop\Common\Models\Category;
use InnoShop\Common\Models\Page;
use InnoShop\Common\Models\Product;
use Throwable;

/**
 * Fills entity_label / entity_image / entity_price from DB. Labels are always refreshed from models when the
 * entity resolves so storefront locale matches (stored JSON may keep a stale label, e.g. English from admin).
 *
 * @see entity_link_enrich()
 * @see entity_link_resolve()
 */
final class EntityLinkEnricher
{
    /**
     * Same as product detail: `locale_code()` + model translations (front middleware already set app + session locale).
     *
     * @param  array{type: string, value: string, entity_label: string, link: string, entity_image: string, entity_price: string}  $row
     * @return array{type: string, value: string, entity_label: string, link: string, entity_image: string, entity_price: string}
     */
    public static function enrichRow(array $row): array
    {
        $type  = (string) ($row['type'] ?? '');
        $value = (string) ($row['value'] ?? '');
        if ($value === '' || $type === '' || $type === 'custom') {
            return $row;
        }

        $needImage = ($row['entity_image'] ?? '') === '';
        $needPrice = ($type === 'product') && (($row['entity_price'] ?? '') === '');

        try {
            switch ($type) {
                case 'product':
                    $product = self::resolveByIdOrSlug(Product::class, $value, ['masterSku']);
                    if ($product instanceof Product) {
                        $row['entity_label'] = $product->fallbackName();
                        if ($needImage) {
                            $sku                 = $product->masterSku;
                            $row['entity_image'] = $sku ? $sku->getImageUrl(100, 100) : $product->getImageUrl(100, 100);
                        }
                        if ($needPrice && ($sku = $product->masterSku)) {
                            $row['entity_price'] = (string) $sku->price_format;
                        }
                    }
                    break;

                case 'category':
                    $category = self::resolveByIdOrSlug(Category::class, $value, ['translation']);
                    if ($category instanceof Category) {
                        $row['entity_label'] = $category->fallbackName();
                        if ($needImage && $category->image) {
                            $row['entity_image'] = (string) image_resize($category->image, 100, 100);
                        }
                    }
                    break;

                case 'brand':
                    $brand = self::resolveByIdOrSlug(Brand::class, $value);
                    if ($brand instanceof Brand) {
                        $row['entity_label'] = (string) $brand->name;
                        if ($needImage && $brand->logo) {
                            $row['entity_image'] = (string) image_resize($brand->logo, 100, 100);
                        }
                    }
                    break;

                case 'page':
                    $page = self::resolveByIdOrSlug(Page::class, $value, ['translation']);
                    if ($page instanceof Page && $page->translation) {
                        $row['entity_label'] = (string) $page->translation->title;
                    }
                    break;

                case 'article':
                    $article = self::resolveByIdOrSlug(Article::class, $value, ['translation']);
                    if ($article instanceof Article) {
                        $row['entity_label'] = $article->fallbackName('title');
                        if ($needImage && $article->image) {
                            $row['entity_image'] = (string) image_resize($article->image, 100, 100);
                        }
                    }
                    break;

                case 'catalog':
                    $catalog = self::resolveByIdOrSlug(Catalog::class, $value, ['translation']);
                    if ($catalog instanceof Catalog) {
                        $row['entity_label'] = $catalog->fallbackName('title');
                    }
                    break;
            }
        } catch (Throwable) {
            return $row;
        }

        return $row;
    }

    /**
     * @param  class-string  $modelClass
     */
    public static function resolveByIdOrSlug(string $modelClass, string $value, array $with = []): ?object
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        $query = $modelClass::query()->with($with);
        if (ctype_digit($value)) {
            return $query->find((int) $value);
        }

        return $query->where('slug', $value)->first();
    }

    /**
     * Storefront URL using each model's {@see Product::getUrlAttribute()} etc., which prefer *-slug routes when `slug` is set.
     *
     * @return string|null null if entity not found (caller may fall back to id/slug in route())
     */
    public static function storefrontUrlForEntity(string $type, string $value): ?string
    {
        $type  = strtolower($type);
        $value = trim($value);
        if ($value === '' || $type === '' || $type === 'custom') {
            return null;
        }

        $model = match ($type) {
            'product'  => self::resolveByIdOrSlug(Product::class, $value, []),
            'category' => self::resolveByIdOrSlug(Category::class, $value, []),
            'brand'    => self::resolveByIdOrSlug(Brand::class, $value, []),
            'page'     => self::resolveByIdOrSlug(Page::class, $value, []),
            'article'  => self::resolveByIdOrSlug(Article::class, $value, []),
            'catalog'  => self::resolveByIdOrSlug(Catalog::class, $value, []),
            default    => null,
        };

        if ($model === null) {
            return null;
        }

        try {
            $url = $model->url ?? '';

            return is_string($url) && $url !== '' ? $url : null;
        } catch (Throwable) {
            return null;
        }
    }
}
