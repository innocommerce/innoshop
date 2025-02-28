<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InquiryQuote\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InnoShop\Common\Models\BaseModel;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Product;

class InquiryQuoteItem extends BaseModel
{
    protected $table = 'inquiry_quote_items';

    protected $fillable = [
        'inquiry_quote_id', 'customer_id', 'product_id', 'seller_id', 'sku_code', 'quantity', 'origin_price',
        'inquiry_price',
    ];

    /**
     * @return BelongsTo
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(InquiryQuote::class, 'inquiry_quote_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        if (seller_enabled()) {
            return $this->belongsTo(\InnoShop\Seller\Models\Override\Product::class, 'product_id', 'id');
        } else {
            return $this->belongsTo(Product::class, 'product_id', 'id');
        }
    }

    /**
     * @return BelongsTo
     */
    public function productSku(): BelongsTo
    {
        return $this->belongsTo(Product\Sku::class, 'sku_code', 'code');
    }

    /**
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * @return float
     */
    public function getOriginSubtotalAttribute(): float
    {
        return round($this->origin_price * $this->quantity, 2);
    }

    /**
     * @return float
     */
    public function getInquirySubtotalAttribute(): float
    {
        return round($this->inquiry_price * $this->quantity, 2);
    }

    /**
     * @return string
     */
    public function getInquiryPriceFormatAttribute(): string
    {
        return currency_format($this->inquiry_price);
    }

    /**
     * @return string
     */
    public function getOriginPriceFormatAttribute(): string
    {
        return currency_format($this->origin_price);
    }

    /**
     * @return string
     */
    public function getInquirySubtotalFormatAttribute(): string
    {
        return currency_format($this->inquiry_subtotal);
    }
}
