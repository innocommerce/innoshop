<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Customer;

use InnoShop\Common\Models\BaseModel;
use InnoShop\Common\Traits\Translatable;

class Group extends BaseModel
{
    use Translatable;

    protected $table = 'customer_groups';

    protected $fillable = [
        'level', 'mini_cost', 'discount_rate',
    ];

    public function getForeignKey()
    {
        return 'customer_group_id';
    }
}
