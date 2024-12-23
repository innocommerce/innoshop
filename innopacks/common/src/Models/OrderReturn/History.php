<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\OrderReturn;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InnoShop\Common\Models\BaseModel;
use InnoShop\Common\Models\OrderReturn;

class History extends BaseModel
{
    protected $table = 'order_return_histories';

    protected $fillable = [
        'order_return_id', 'status', 'notify', 'comment',
    ];

    /**
     * @return BelongsTo
     */
    public function orderReturn(): BelongsTo
    {
        return $this->belongsTo(OrderReturn::class, 'order_return_id');
    }

    /**
     * @return string
     */
    public function getStatusFormatAttribute(): string
    {
        return trans('common/rma.'.$this->status);
    }
}
