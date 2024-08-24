<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models;

class Review extends BaseModel
{
    protected $table = 'reviews';

    protected $fillable = [
        'customer_id', 'product_id', 'order_product_id', 'rating', 'title', 'content', 'like', 'dislike', 'active',
    ];
}
