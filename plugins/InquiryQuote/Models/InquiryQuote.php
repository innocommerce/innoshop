<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InquiryQuote\Models;

use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InnoShop\Common\Models\Address;
use InnoShop\Common\Models\Admin;
use InnoShop\Common\Models\BaseModel;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Order;
use InnoShop\Seller\Models\Seller;
use Plugin\InquiryQuote\Services\StateService;

class InquiryQuote extends BaseModel
{
    protected $table = 'inquiry_quotes';

    protected $fillable = [
        'parent_id', 'admin_id', 'seller_id', 'order_id', 'customer_id', 'number', 'based', 'shipping_address_id',
        'shipping_method_code', 'total', 'comment', 'status',
    ];

    /**
     * @return BelongsTo
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'adminer_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id', 'id');
    }

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
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(InquiryQuoteItem::class, 'inquiry_quote_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function fees(): HasMany
    {
        return $this->hasMany(InquiryQuoteFee::class, 'inquiry_quote_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function histories(): HasMany
    {
        return $this->hasMany(InquiryQuoteHistory::class, 'inquiry_quote_id', 'id');
    }

    /**
     * @return string
     */
    public function getTotalFormatAttribute(): string
    {
        return currency_format($this->total);
    }

    /**
     * @return ?string
     */
    public function getBasedFormatAttribute(): ?string
    {
        if (empty($this->based)) {
            return '-';
        }

        return trans('InquiryQuote::quote.'.$this->based);
    }

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

        $statusMap = array_column(StateService::getAllStatuses(), 'name', 'status');

        return $statusMap[$statusCode] ?? '';
    }

    /**
     * @param  float|null  $total
     * @return float
     */
    public function syncTotal(?float $total = null): float
    {
        if (is_null($total)) {
            $total = $this->fees()->whereNot('code', 'total')->sum('inquiry_amount');
        }
        $this->total = $total;
        $this->save();

        return $total;
    }
}
