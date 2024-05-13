<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Region;

use InnoShop\Common\Models\BaseModel;

class State extends BaseModel
{
    protected $table = 'region_states';

    protected $fillable = [
        'country_id', 'state_id',
    ];
}
