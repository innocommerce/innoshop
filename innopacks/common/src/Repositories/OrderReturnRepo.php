<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use InnoShop\Common\Models\OrderReturn;

class OrderReturnRepo extends BaseRepo
{
    protected string $model = OrderReturn::class;

    public function create($data): mixed
    {
        dd($data);
    }
}
