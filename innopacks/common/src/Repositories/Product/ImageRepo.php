<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories\Product;

use InnoShop\Common\Models\Product;
use InnoShop\Common\Repositories\BaseRepo;

class ImageRepo extends BaseRepo
{
    /**
     * @param  Product  $product
     * @param  $path
     * @return mixed
     */
    public function findOrCreate(Product $product, $path): mixed
    {
        if (empty($path)) {
            return null;
        }

        $path  = str_replace('\\', '/', trim($path));
        $image = $product->images()->where('path', $path)->first();
        if ($image) {
            return $image;
        }

        return $product->images()->create([
            'path'       => $path,
            'is_cover'   => 0,
            'belong_sku' => 0,
            'position'   => 0,
        ]);
    }
}
