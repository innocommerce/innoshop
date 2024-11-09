<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ProductExporter\Repositories;

use Exception;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Repositories\ProductRepo;
use Rap2hpoutre\FastExcel\SheetCollection;
use Throwable;

class ImportRepo
{
    private bool $clearData;

    public function __construct($clearData)
    {
        $this->clearData = $clearData;
    }

    /**
     * @param  $clearData
     * @return self
     */
    public static function getInstance($clearData): ImportRepo
    {
        return new self($clearData);
    }

    /**
     * @param  SheetCollection  $excelData
     * @return void
     * @throws Exception|Throwable
     */
    public function importSheets(SheetCollection $excelData): void
    {
        if (count($excelData) != 3) {
            throw new Exception('Invalid sheet number, must be 3');
        }
        $this->importProducts($excelData->get(0));
        $this->importTranslations($excelData->get(1));
        $this->importSkus($excelData->get(2));
    }

    /**
     * @param  $items
     * @return void
     * @throws Throwable
     */
    private function importProducts($items): void
    {
        if ($this->clearData) {
            Product::query()->truncate();
        }

        $productRepo = ProductRepo::getInstance();
        foreach ($items as $item) {
            if (empty($item['slug'] ?? '')) {
                continue;
            }
            $itemData = $productRepo->handleProductData($item);
            $product  = Product::query()->find($item['id'] ?? 0);
            if (empty($product)) {
                $product = Product::query()->where('slug', $item['slug'] ?? '')->first();
            }

            if ($product) {
                $product->update($itemData);
            } else {
                $product = Product::query()->create($itemData);
            }

            if (isset($item['image'])) {
                $product->images()->delete();
                $images = explode(',', $item['image']);
                $productRepo->syncImages($product, $images);
                $coverImage = $product->images()->first();
                if ($coverImage) {
                    $coverImage->update(['is_cover' => 1]);
                }
            }
        }
    }

    /**
     * @param  $items
     * @return void
     */
    private function importTranslations($items): void
    {
        if ($this->clearData) {
            Product\Translation::query()->truncate();
        }

        foreach ($items as $item) {
            $productTranslation = Product\Translation::query()->find($item['id'] ?? 0);
            if (empty($productTranslation) && ($productID = $item['product_id'] ?? 0) && ($locale = $item['locale'] ?? '')) {
                $productTranslation = Product\Translation::query()
                    ->where('product_id', $productID)
                    ->where('locale', $locale)
                    ->first();
            }

            if ($productTranslation) {
                $productTranslation->update($item);
            } else {
                Product\Translation::query()->create($item);
            }
        }
    }

    /**
     * @param  $items
     * @return void
     */
    private function importSkus($items): void
    {
        if ($this->clearData) {
            Product\Sku::query()->truncate();
        }

        foreach ($items as $item) {
            $productSku = Product\Sku::query()->find($item['id'] ?? 0);
            if (empty($productSku) && ($item['code'] ?? '')) {
                $productSku = Product\Sku::query()->where('code', $item['code'] ?? '')->first();
            }

            $itemData = $this->handleSku($item);
            if ($productSku) {
                $productSku->update($itemData);
            } else {
                Product\Sku::query()->create($itemData);
            }
        }
    }

    /**
     * @param  $skuItem
     * @return array
     */
    private function handleSku($skuItem): array
    {
        $skuCode = $skuItem['code'] ?? '';

        return [
            'product_id'       => $skuItem['product_id'],
            'product_image_id' => 0,
            'variants'         => [],
            'code'             => $skuCode,
            'model'            => $skuItem['model']        ?? $skuCode,
            'price'            => $skuItem['price']        ?? 0,
            'origin_price'     => $skuItem['origin_price'] ?? 0,
            'quantity'         => $skuItem['quantity']     ?? 0,
            'is_default'       => 1,
            'position'         => $skuItem['position'] ?? 0,
        ];
    }
}
