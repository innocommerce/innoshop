<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Visit;

use InnoShop\Common\Models\BaseModel;

class VisitCountryDaily extends BaseModel
{
    protected $table = 'visit_country_daily';

    public $incrementing = false;

    protected $fillable = [
        'date', 'country_code', 'country_name',
        'visits', 'ip_count',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
