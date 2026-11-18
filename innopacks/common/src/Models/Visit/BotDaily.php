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

class BotDaily extends BaseModel
{
    protected $table = 'bot_daily';

    protected $fillable = [
        'date', 'bot_brand', 'category',
        'visits', 'page_views', 'ip_count', 'last_ip', 'last_path',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
