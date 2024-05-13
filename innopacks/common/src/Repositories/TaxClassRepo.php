<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use InnoShop\Common\Models\TaxClass;

class TaxClassRepo extends BaseRepo
{
    /**
     * @param  $data
     * @return TaxClass
     * @throws \Throwable
     */
    public function create($data): TaxClass
    {
        $item = new TaxClass($data);
        $item->saveOrFail();

        $item->taxRules()->createMany($data['tax_rules']);

        return $item;
    }

    /**
     * @param  $item
     * @param  $data
     * @return mixed
     */
    public function update($item, $data): mixed
    {
        $item->fill($data);
        $item->saveOrFail();

        $item->taxRules()->delete();
        $item->taxRules()->createMany($data['tax_rules']);

        return $item;
    }
}
