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
    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'customer', 'type' => 'input', 'label' => trans('panel/order_return.customer')],
            ['name' => 'order_number', 'type' => 'input', 'label' => trans('panel/order_return.order_number')],
            ['name' => 'number', 'type' => 'input', 'label' => trans('panel/order_return.number')],
            ['name' => 'name', 'type' => 'input', 'label' => trans('panel/order_return.name')],
            ['name' => 'email', 'type' => 'input', 'label' => trans('panel/order_return.email')],
        ];
    }

    protected string $model = OrderReturn::class;

    public function create($data): mixed
    {
        dd($data);
    }
}
