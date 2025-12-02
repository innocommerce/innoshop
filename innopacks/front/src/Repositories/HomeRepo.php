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
use InnoShop\Common\Models\Category;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Repositories\CategoryRepo;

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
     * @return array
     */
    public function getSlideShow(): array
    {
        $slideShow = system_setting('slideshow');
        if (empty($slideShow)) {
            return [];
        }

        $result = [];
        foreach ($slideShow as $item) {
            if (str_starts_with($item['link'], 'category:')) {
                $categoryID = str_replace('category:', '', $item['link']);
                $category   = Category::query()->find($categoryID);
                if (empty($category)) {
                    $category = Category::query()->where('slug', $categoryID)->first();
                }
                $item['link'] = $category->url;
            } elseif (str_starts_with($item['link'], 'product:')) {
                $productID = str_replace('product:', '', $item['link']);
                $product   = Product::query()->find($productID);
                if (empty($product)) {
                    $product = Product::query()->where('slug', $productID)->first();
                }
                $item['link'] = $product->url;
            }
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Format product data for home page
     *
     * @param  Product  $product
     * @return array
     * @throws Exception
     */
    public function formatProductData(Product $product): array
    {
        $masterSku = $product->masterSku;
        $price     = $masterSku ? $masterSku->price : $product->price;
        $moq       = $masterSku ? $masterSku->quantity : 0;
        $image     = $product->getImageUrl(400, 400);

        // 获取供应商信息（如果 supplier 模块启用）
        $supplier      = null;
        $supplierBadge = null;
        if (class_exists('InnoShop\Supplier\Models\Supplier') && isset($product->supplier_id) && $product->supplier_id) {
            try {
                $supplierModel = \InnoShop\Supplier\Models\Supplier::query()->find($product->supplier_id);
                if ($supplierModel) {
                    $supplier = $supplierModel->name;
                    if ($supplierModel->status === 'approved' && $supplierModel->supplier_level) {
                        $supplierBadge = $supplierModel->supplier_level_format;
                    } else {
                        $supplierBadge = $supplierModel->status === 'approved' ? trans('Supplier::supplier.gold_supplier') : '';
                    }
                }
            } catch (Exception $e) {
                // 忽略错误
            }
        }

        $productName = $product->fallbackName();

        return [
            'id'             => $product->id,
            'name'           => $productName,
            'image'          => $image,
            'supplier'       => $supplier,
            'supplier_badge' => $supplierBadge,
            'price'          => number_format($price, 2),
            'moq'            => $moq,
            'category'       => '', // 可以从分类关系中获取
        ];
    }

    /**
     * Format supplier data for home page
     *
     * @param  mixed  $supplier
     * @return array
     */
    public function formatSupplierData($supplier): array
    {
        $customer = $supplier->customer ?? null;
        $category = $supplier->category ?? null;

        // Logo：优先供应商 logo，其次客户头像
        $logo = $supplier->logo ?: ($customer->avatar ?? '');
        if ($logo) {
            $logo = image_resize($logo, 400, 400);
        }

        // 供应商等级
        $level     = $supplier->supplier_level ?? 'gold';
        $levelName = $supplier->supplier_level ? $supplier->supplier_level_format : trans('Supplier::supplier.gold_supplier');

        $categoryName = '';
        $categorySlug = null;
        $categoryId   = null;
        if ($category) {
            $categoryName = $category->fallbackName() ?? ($category->name ?? '');
            $categorySlug = $category->slug ?? null;
            $categoryId   = $category->id ?? null;
        }

        return [
            'id'            => $supplier->id,
            'name'          => $supplier->name ?? '',
            'logo'          => $logo,
            'level'         => $level,
            'level_name'    => $levelName,
            'verified'      => ($supplier->status ?? '') === 'approved',
            'location'      => $supplier->location ?: ($customer->address ?? ''),
            'main_products' => $supplier->main_products ?? '',
            'established'   => $supplier->established_year,
            'employees'     => $supplier->employees_count,
            'export_value'  => $supplier->annual_export_value_text ?? $supplier->annual_export_value,
            'category'      => $categoryName,
            'category_slug' => $categorySlug,
            'category_id'   => $categoryId,
            'type'          => $supplier->type ?? '',
            'website'       => $supplier->website ?? '',
        ];
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
}
