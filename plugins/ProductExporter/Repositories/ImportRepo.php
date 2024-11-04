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
use Rap2hpoutre\FastExcel\SheetCollection;

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
     * @throws Exception
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
     */
    private function importProducts($items): void
    {
        $items = collect($items)->map(function ($item) {
            if (empty($item['deleted_at'])) {
                unset($item['deleted_at']);
            }
            if (empty($item['slug'])) {
                $item['slug'] = null;
            }

            return $item;
        })->toArray();

        if ($this->clearData) {
            Product::query()->truncate();
            Product::query()->insert($items);
        }

        foreach ($items as $item) {
            $product = Product::query()->find($item['id'] ?? 0);
            if (empty($product) && ($item['slug'] ?? '')) {
                $product = Product::query()->where('slug', $item['slug'] ?? '')->first();
            }

            if ($product) {
                $product->update($item);
            } else {
                Product::query()->create($item);
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
            Product\Translation::query()->insert($items);
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
            Product\Sku::query()->insert($items);
        }

        foreach ($items as $item) {
            $productSku = Product\Sku::query()->find($item['id'] ?? 0);
            if (empty($productSku) && ($item['code'] ?? '')) {
                $productSku = Product\Sku::query()->where('code', $item['code'] ?? '')->first();
            }

            if ($productSku) {
                $productSku->update($item);
            } else {
                Product\Sku::query()->create($item);
            }
        }
    }
}
