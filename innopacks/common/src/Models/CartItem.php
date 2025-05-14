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

/**
 * @property int $id
 * @property int $quantity
 * @property string $sku_code
 */
class CartItem extends BaseModel
{
    protected $table = 'cart_items';

    protected $fillable = [
        'customer_id', 'product_id', 'sku_code', 'guest_id', 'selected', 'quantity', 'item_type', 'reference',
    ];

    protected $appends = [
        'subtotal',
        'price',
        'item_type_label',
    ];

    protected $casts = [
        'reference' => 'array',
    ];

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function productSku(): BelongsTo
    {
        return $this->belongsTo(Product\Sku::class, 'sku_code', 'code');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function getSubtotalAttribute(): float
    {
        return round($this->price * $this->quantity, 2);
    }

    /**
     * Get the price for the cart item
     *
     * @return float
     */
    public function getPriceAttribute(): float
    {
        $price = $this->productSku->getFinalPrice();

        return fire_hook_filter('model.cart.item.price', [
            'price' => $price,
            'item'  => $this,
        ])['price'];
    }

    /**
     * Get the item type label
     *
     * @return string
     */
    public function getItemTypeLabelAttribute(): string
    {
        $data = [
            'label' => '',
            'item'  => $this,
        ];

        return fire_hook_filter('model.cart.item.type_label', $data)['label'];
    }
}
