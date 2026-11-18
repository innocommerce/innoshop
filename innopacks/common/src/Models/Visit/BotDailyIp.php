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

class BotDailyIp extends BaseModel
{
    public $timestamps = false;

    protected $table = 'bot_daily_ips';

    protected $primaryKey = null;

    public $incrementing = false;

    protected $fillable = ['date', 'ip', 'brand'];

    protected $casts = [
        'date' => 'date',
    ];
}
