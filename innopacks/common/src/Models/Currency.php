<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models;

class Currency extends BaseModel
{
    protected $table = 'currencies';

    protected $fillable = [
        'name', 'code', 'symbol_left', 'symbol_right', 'decimal_place', 'value', 'active',
    ];
}
