<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Order;

use Exception;
use InnoShop\Common\Models\BaseModel;
use InnoShop\Common\Services\ReturnStateService;

class History extends BaseModel
{
    protected $table = 'order_histories';

    protected $fillable = [
        'order_id', 'status', 'notify', 'comment',
    ];

    /**
     * @return string
     * @throws Exception
     */
    public function getStatusFormatAttribute(): string
    {
        $statusCode = $this->status;
        if ($statusCode == null) {
            return '';
        }

        $statusMap = array_column(ReturnStateService::getAllStatuses(), 'name', 'status');

        return $statusMap[$statusCode] ?? '';
    }
}
