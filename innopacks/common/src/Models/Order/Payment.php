<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Order;

use InnoShop\Common\Models\BaseModel;

class Payment extends BaseModel
{
    protected $table = 'order_payments';

    protected $fillable = [
        'order_id', 'charge_id', 'amount', 'handling_fee', 'paid', 'reference',
    ];
}
