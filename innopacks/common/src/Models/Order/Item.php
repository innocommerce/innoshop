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
use Illuminate\Database\Eloquent\Relations\HasOne;
use InnoShop\Common\Models\BaseModel;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Models\Product\Sku;
use InnoShop\Common\Models\Review;

class Item extends BaseModel
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id', 'product_id', 'order_number', 'product_sku', 'variant_label', 'name', 'image', 'quantity', 'price',
    ];

    protected $appends = [
        'subtotal',
        'price_format',
        'subtotal_format',
        'has_review',
    ];

    /**
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function productSku(): BelongsTo
    {
        return $this->belongsTo(Sku::class, 'product_sku', 'code');
    }

    /**
     * @return HasOne
     */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class, 'order_item_id', 'id');
    }

    /**
     * @return float
     */
    public function getSubtotalAttribute(): float
    {
        return round($this->price * $this->quantity, 2);
    }

    /**
     * @return string
     */
    public function getPriceFormatAttribute(): string
    {
        $order = $this->order;

        return currency_format($this->price, $order->currency_code, $order->currency_value);
    }

    /**
     * @return string
     */
    public function getSubtotalFormatAttribute(): string
    {
        $order = $this->order;

        return currency_format($this->subtotal, $order->currency_code, $order->currency_value);
    }

    /**
     * @return bool
     */
    public function getHasReviewAttribute(): bool
    {
        return (bool) $this->review;
    }
}
