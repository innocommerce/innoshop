<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Libraries;

use Exception;
use Illuminate\Support\Str;
use InnoShop\Common\Models\Brand;
use InnoShop\Common\Models\Catalog;
use InnoShop\Common\Models\Category;
use InnoShop\Common\Models\Page;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Repositories\BrandRepo;
use InnoShop\Common\Repositories\CatalogRepo;
use InnoShop\Common\Repositories\CategoryRepo;
use InnoShop\Common\Repositories\PageRepo;
use InnoShop\Common\Repositories\ProductRepo;

class Link
{
    public const TYPES = [
        'category', 'product', 'brand', 'page', 'page_category', 'order', 'rma', 'static', 'custom',
    ];

    public static function getInstance(): self
    {
        return new self;
    }

    /**
     * Handle link.
     *
     * @return mixed|string
     * @throws Exception
     */
    public function link($type, $value): mixed
    {
        if (empty($type) || empty($value) || ! in_array($type, self::TYPES)) {
            return '';
        }

        if (is_array($value)) {
            throw new Exception('Value must be integer, string or object');
        }

        if ($type == 'category') {
            if (! $value instanceof Category) {
                $value = Category::query()->find($value);
            }

            return $value->url ?? '';

        } elseif ($type == 'product') {
            if (! $value instanceof Product) {
                $value = Product::query()->find($value);
            }

            return $value->url ?? '';
        } elseif ($type == 'brand') {
            if (! $value instanceof Brand) {
                $value = Brand::query()->find($value);
            }

            return $value->url ?? '';
        } elseif ($type == 'page') {
            if (! $value instanceof Page) {
                $value = Page::query()->find($value);
            }

            return $value->url ?? '';
        } elseif ($type == 'page_category') {
            if (! $value instanceof Catalog) {
                $value = Catalog::query()->find($value);
            }

            return $value->url ?? '';
        } elseif ($type == 'order') {
            return front_route('account.order.show', ['number' => $value]);
        } elseif ($type == 'rma') {
            return front_route('account.rma.show', ['id' => $value]);
        } elseif ($type == 'static') {
            return front_route($value);
        } elseif ($type == 'custom') {
            if (Str::startsWith($value, ['http://', 'https://'])) {
                return $value;
            }

            return "//{$value}";
        }

        return '';
    }

    /**
     * Handle link label
     *
     * @param  $type
     * @param  $value
     * @param  $texts
     * @return mixed
     * @throws Exception
     */
    public function label($type, $value, $texts): mixed
    {
        $types = ['category', 'product', 'brand', 'page', 'page_category', 'static', 'custom'];
        if (empty($type) || empty($value) || ! in_array($type, $types)) {
            return '';
        }

        $locale = locale_code();
        $text   = $texts[$locale] ?? '';
        if ($text) {
            return $text;
        }

        if ($type == 'category') {
            return CategoryRepo::getInstance()->getNameByID($value);
        } elseif ($type == 'product') {
            return ProductRepo::getInstance()->getNameByID($value);
        } elseif ($type == 'brand') {
            return BrandRepo::getInstance()->getNameByID($value);
        } elseif ($type == 'page') {
            return PageRepo::getInstance()->getNameByID($value);
        } elseif ($type == 'catalog') {
            return CatalogRepo::getInstance()->getNameByID($value);
        } elseif ($type == 'static') {
            $value = $this->handleLocale($value);

            return trans('shop/'.$value);
        } elseif ($type == 'custom') {
            return $text;
        }

        return '';
    }

    /**
     * preg_replace('/\/([^\/]+)$/', '.$1', str_replace(".", "/", $value));
     *
     * @param  $value
     * @return string
     */
    private function handleLocale($value): string
    {
        $parts = explode('.', $value);
        if (count($parts) < 2) {
            return $value;
        }

        $result = '';
        foreach ($parts as $index => $part) {
            if ($index < count($parts) - 2) {
                $result .= $part.'/';
            } elseif ($index < count($parts) - 1) {
                $result .= $part.'.';
            } else {
                $result .= $part;
            }
        }

        return $result;
    }
}
