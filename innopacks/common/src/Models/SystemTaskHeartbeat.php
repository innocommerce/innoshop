<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models;

class SystemTaskHeartbeat extends BaseModel
{
    protected $table = 'system_task_heartbeats';

    protected $fillable = [
        'task_key',
        'ran_at',
    ];

    protected $casts = [
        'ran_at' => 'datetime',
    ];
}
