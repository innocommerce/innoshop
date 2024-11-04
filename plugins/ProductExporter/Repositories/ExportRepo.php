<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ProductExporter\Repositories;

use Rap2hpoutre\FastExcel\SheetCollection;

class ExportRepo
{
    /**
     * @return self
     */
    public static function getInstance(): ExportRepo
    {
        return new self;
    }

    /**
     * @param  $items
     * @return SheetCollection
     */
    public function getExportData($items): SheetCollection
    {
        return new SheetCollection([
            'Products'     => $this->getProductSheet($items),
            'Translations' => $this->getTranslationSheet($items),
            'Skus'         => $this->getSkuSheet($items),
        ]);
    }

    /**
     * @param  $items
     * @return array
     */
    private function getProductSheet($items): array
    {
        $products = [];
        foreach ($items as $item) {
            $itemData = $item->toArray();

            $itemData['created_at'] = $item->created_at->format('Y-m-d H:i:s');
            $itemData['updated_at'] = $item->updated_at->format('Y-m-d H:i:s');
            $itemData['variables']  = json_encode($itemData['variables']);

            $products[] = $itemData;
        }

        return $products;
    }

    /**
     * @param  $items
     * @return array
     */
    private function getTranslationSheet($items): array
    {
        $translations = [];
        foreach ($items as $item) {
            foreach ($item->translations as $translation) {
                $itemData = $translation->toArray();

                $itemData['created_at'] = $translation->created_at->format('Y-m-d H:i:s');
                $itemData['updated_at'] = $translation->updated_at->format('Y-m-d H:i:s');

                $translations[] = $itemData;
            }
        }

        return $translations;
    }

    /**
     * @param  $items
     * @return array
     */
    private function getSkuSheet($items): array
    {
        $skus = [];
        foreach ($items as $item) {
            foreach ($item->skus as $sku) {
                $itemData = $sku->toArray();

                $itemData['created_at'] = $sku->created_at->format('Y-m-d H:i:s');
                $itemData['updated_at'] = $sku->updated_at->format('Y-m-d H:i:s');

                $skus[] = $itemData;
            }
        }

        return $skus;
    }
}
