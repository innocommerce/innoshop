<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Order;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InnoShop\Common\Models\BaseModel;
use InnoShop\Common\Models\Order;

class Fee extends BaseModel
{
    protected $table = 'order_fees';

    protected $fillable = [
        'order_id', 'code', 'value', 'title', 'reference',
    ];

    protected $casts = [
        'reference' => 'array',
    ];

    protected $appends = [
        'value_format',
    ];

    /**
     * Get fee title with fallback.
     *
     * The fee title is persisted to the database when the order is placed.
     * It is re-translated by code when the language changes, falling back
     * to the original stored value when no translation is found.
     */
    public function getTitleAttribute($value): string
    {
        $value = $value ?? '';

        $code = $this->code;
        if (empty($code)) {
            return $value;
        }

        $translated = __("front/order.{$code}");
        if ($translated !== "front/order.{$code}") {
            return $translated;
        }

        return $value;
    }

    /**
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * @return string
     */
    public function getValueFormatAttribute(): string
    {
        $order = $this->order()->first();

        if ($order) {
            return currency_format($this->value, $order->currency_code, $order->currency_value);
        }

        return currency_format($this->value);
    }
}
