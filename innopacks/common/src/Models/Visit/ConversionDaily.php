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

class ConversionDaily extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'conversion_daily';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'date';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'home_views',
        'category_views',
        'product_views',
        'add_to_carts',
        'checkout_starts',
        'order_placed',
        'payment_completed',
        'registers',
        'searches',
        'cart_views',
        'order_cancelled',
        'cart_to_checkout_rate',
        'checkout_to_order_rate',
        'order_to_payment_rate',
        'overall_conversion_rate',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get cart to checkout rate as percentage.
     *
     * @return float
     */
    public function getCartToCheckoutPercentAttribute(): float
    {
        return $this->cart_to_checkout_rate / 100;
    }

    /**
     * Get checkout to order rate as percentage.
     *
     * @return float
     */
    public function getCheckoutToOrderPercentAttribute(): float
    {
        return $this->checkout_to_order_rate / 100;
    }

    /**
     * Get order to payment rate as percentage.
     *
     * @return float
     */
    public function getOrderToPaymentPercentAttribute(): float
    {
        return $this->order_to_payment_rate / 100;
    }

    /**
     * Get overall conversion rate as percentage.
     *
     * @return float
     */
    public function getOverallConversionPercentAttribute(): float
    {
        return $this->overall_conversion_rate / 100;
    }
}
