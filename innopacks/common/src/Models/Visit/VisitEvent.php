<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Visit;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InnoShop\Common\Models\BaseModel;
use InnoShop\Common\Models\Customer;

class VisitEvent extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'visit_events';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'session_id',
        'event_type',
        'event_data',
        'customer_id',
        'ip_address',
        'page_url',
        'referrer',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'event_data' => 'array',
    ];

    /**
     * Event types
     */
    public const TYPE_PRODUCT_VIEW = 'product_view';

    public const TYPE_ADD_TO_CART = 'add_to_cart';

    public const TYPE_CHECKOUT_START = 'checkout_start';

    public const TYPE_ORDER_PLACED = 'order_placed';

    public const TYPE_PAYMENT_COMPLETED = 'payment_completed';

    public const TYPE_REGISTER = 'register';

    public const TYPE_HOME_VIEW = 'home_view';

    public const TYPE_CATEGORY_VIEW = 'category_view';

    public const TYPE_SEARCH = 'search';

    public const TYPE_CART_VIEW = 'cart_view';

    public const TYPE_ORDER_CANCELLED = 'order_cancelled';

    /**
     * Get the customer that owns the event.
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get event type display name.
     *
     * @return string
     */
    public function getEventTypeDisplayAttribute(): string
    {
        $types = [
            self::TYPE_PRODUCT_VIEW      => trans('panel/visit.event_product_view'),
            self::TYPE_ADD_TO_CART       => trans('panel/visit.event_add_to_cart'),
            self::TYPE_CHECKOUT_START    => trans('panel/visit.event_checkout_start'),
            self::TYPE_ORDER_PLACED      => trans('panel/visit.event_order_placed'),
            self::TYPE_PAYMENT_COMPLETED => trans('panel/visit.event_payment_completed'),
            self::TYPE_REGISTER          => trans('panel/visit.event_register'),
            self::TYPE_HOME_VIEW         => trans('panel/visit.event_home_view'),
            self::TYPE_CATEGORY_VIEW     => trans('panel/visit.event_category_view'),
            self::TYPE_SEARCH            => trans('panel/visit.event_search'),
            self::TYPE_CART_VIEW         => trans('panel/visit.event_cart_view'),
            self::TYPE_ORDER_CANCELLED   => trans('panel/visit.event_order_cancelled'),
        ];

        return $types[$this->event_type] ?? $this->event_type;
    }
}
