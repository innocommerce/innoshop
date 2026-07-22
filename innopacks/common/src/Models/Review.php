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
use InnoShop\Common\Models\Order\Item;

class Review extends BaseModel
{
    protected $table = 'reviews';

    protected $fillable = [
        'customer_id', 'product_id', 'order_item_id', 'rating', 'content', 'like', 'dislike', 'active',
    ];

    /**
     * Boot: on review write, recompute the parent product's rating + reviews_count.
     * Covers create, update (incl. active toggle), and delete.
     */
    protected static function booted(): void
    {
        static::saved(function (self $review): void {
            $review->refreshProductRating();
        });

        static::deleted(function (self $review): void {
            $review->refreshProductRating();
        });
    }

    /**
     * Recompute rating + reviews_count on the related product (active reviews only).
     */
    protected function refreshProductRating(): void
    {
        $product = $this->product;
        if (! $product) {
            return;
        }

        $active = static::query()
            ->where('product_id', $product->id)
            ->where('active', 1);

        $product->rating        = round((float) $active->average('rating'), 2);
        $product->reviews_count = (int) $active->count();
        $product->saveQuietly();
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
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'order_item_id', 'id');
    }
}
