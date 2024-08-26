<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use InnoShop\Common\Models\Order\Fee;
use InnoShop\Common\Models\Order\History;
use InnoShop\Common\Models\Order\Item;
use InnoShop\Common\Notifications\OrderNewNotification;
use InnoShop\Common\Notifications\OrderUpdateNotification;

/**
 * @Property int customer_id
 * @Property string number
 * @Property float total
 * @Property string currency_code
 * @Property string currency_value
 */
class Order extends BaseModel
{
    use Notifiable;

    protected $table = 'orders';

    protected $fillable = [
        'number', 'customer_id', 'customer_group_id', 'shipping_address_id', 'billing_address_id', 'customer_name',
        'email', 'calling_code', 'telephone', 'total', 'locale', 'currency_code', 'currency_value', 'ip', 'user_agent',
        'status', 'shipping_method_code', 'shipping_method_name', 'shipping_customer_name', 'shipping_calling_code',
        'shipping_telephone', 'shipping_country', 'shipping_country_id', 'shipping_state_id', 'shipping_state',
        'shipping_city', 'shipping_address_1', 'shipping_address_2', 'shipping_zipcode', 'billing_method_code',
        'billing_method_name', 'billing_customer_name', 'billing_calling_code', 'billing_telephone', 'billing_country',
        'billing_country_id', 'billing_state_id', 'billing_state', 'billing_city', 'billing_address_1',
        'billing_address_2', 'billing_zipcode',
    ];

    protected $appends = [
        'total_format',
        'status_format',
    ];

    /**
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * Order items.
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'order_id', 'id');
    }

    /**
     * Order fees.
     *
     * @return HasMany
     */
    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class, 'order_id', 'id');
    }

    /**
     * Order histories.
     *
     * @return HasMany
     */
    public function histories(): HasMany
    {
        return $this->hasMany(History::class, 'order_id', 'id');
    }

    /**
     * Format total by currency.
     *
     * @return string
     */
    public function getTotalFormatAttribute(): string
    {
        return currency_format($this->total, $this->currency_code, $this->currency_value);
    }

    /**
     * @return string
     */
    public function getStatusFormatAttribute(): string
    {
        return front_trans('order.'.$this->status);
    }

    /**
     * Send a new order notification.
     *
     * @return void
     */
    public function notifyNewOrder(): void
    {
        $useQueue = system_setting('use_queue', false);
        if ($useQueue) {
            $this->notify(new OrderNewNotification($this));
        } else {
            $this->notifyNow(new OrderNewNotification($this));
        }
    }

    /**
     * Send an order status update notification.
     *
     * @param  $fromCode
     * @return void
     */
    public function notifyUpdateOrder($fromCode): void
    {
        $useQueue = system_setting('use_queue', false);
        if ($useQueue) {
            $this->notify(new OrderUpdateNotification($this, $fromCode));
        } else {
            $this->notifyNow(new OrderUpdateNotification($this, $fromCode));
        }
    }
}
