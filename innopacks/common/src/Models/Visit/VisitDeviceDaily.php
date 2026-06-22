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

class VisitDeviceDaily extends BaseModel
{
    protected $table = 'visit_device_daily';

    public $incrementing = false;

    protected $fillable = [
        'date', 'device_type',
        'visits', 'ip_count', 'page_views',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
