<?php

namespace Plugin\Coupon\Models;

use InnoShop\Common\Models\Order as BaseModel;

class Order extends BaseModel
{
    protected $fillable = ['coupon_id'];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'id');
    }
}
