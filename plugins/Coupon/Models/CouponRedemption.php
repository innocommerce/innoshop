<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     XING GUI YU <xingguiyu@foxmail.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Coupon\Models;

use InnoShop\Common\Models\BaseModel;
use InnoShop\Common\Models\Order;

class CouponRedemption extends BaseModel
{
    protected $table = 'coupon_redemptions';

    protected $fillable = [
        'coupon_id',
        'user_id',
        'order_id',
        'last_used_at',
        'date_used',
    ];

    // 定义需要转换为 Carbon 实例的日期字段
    protected $dates = [
        'last_used_at',
        'date_used',
        'created_at',
        'updated_at',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
