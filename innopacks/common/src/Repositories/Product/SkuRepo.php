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
use InnoShop\Common\Models\Product\Sku;
use InnoShop\Common\Repositories\BaseRepo;

class SkuRepo extends BaseRepo
{
    /**
     * @param  $code
     * @return Sku|null
     */
    public function getSkuByCode($code): ?Sku
    {
        return Sku::query()->where('code', $code)->first();
    }

    /**
     * @param  $code
     * @return mixed|null
     */
    public function getProductByCode($code): ?Product
    {
        return Sku::query()->where('code', $code)->first()->product ?? null;
    }
}
