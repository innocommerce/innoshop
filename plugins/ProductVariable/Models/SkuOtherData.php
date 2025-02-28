<?php

namespace Plugin\ProductVariable\Models;

use Illuminate\Database\Eloquent\Model;

class SkuOtherData extends Model
{
    public $table = 'product_custom_sku_cart';

    public $fillable = [
        'session_id',
        'user_id',
        'product_id',
        'sku_id',
        'custom_sku',
        'cart_id',
    ];
}
