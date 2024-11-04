<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Repositories\Dashboard;

use InnoShop\Panel\Repositories\BaseRepo;

class ProductRepo extends BaseRepo
{
    /**
     * @return array
     * @throws \Exception
     */
    public function getTopSaleProducts(): array
    {
        $products = \InnoShop\Common\Repositories\ProductRepo::getInstance()->getBestSellerProducts(8);

        $items = [];
        foreach ($products as $product) {
            if (empty($product->order_items_count)) {
                continue;
            }

            $name    = $product->translation->name ?? '';
            $items[] = [
                'product_id'  => $product->id,
                'image'       => image_resize($product->image->path ?? ''),
                'name'        => $name,
                'summary'     => sub_string($name, 50),
                'order_count' => $product->order_items_count,
            ];
        }

        return $items;
    }

    /**
     * @return array
     */
    public function getTopSaleProductsForPieChart(): array
    {
        $products = \InnoShop\Common\Repositories\ProductRepo::getInstance()->getBestSellerProducts();

        $names = $viewed = [];
        foreach ($products as $product) {
            $names[]  = sub_string($product->translation->name, 64);
            $viewed[] = $product->order_items_count;
        }

        return [
            'period' => $names,
            'totals' => $viewed,
        ];
    }
}
