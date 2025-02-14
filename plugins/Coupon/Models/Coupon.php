<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     XING GUI YU <xingguiyu@foxmail.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Coupon\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InnoShop\Common\Models\BaseModel;
use InnoShop\Common\Models\Order;
use Illuminate\Support\Carbon;

class Coupon extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'code',
        'type',
        'value',
        'start_at',
        'end_at',
        'active',
        // 'times_used',
        'max_uses',
        'max_uses_per_user',
        'daily_limit',
        'use_interval',
    ];

    protected $dates = ['start_at', 'end_at', 'created_at', 'updated_at', 'deleted_at'];

    protected $attributes = [
        'start_at' => 'now',  // 默认当前时间
        'times_used' => 0,    // 添加这行
    ];

    public static function boot()
    {
        parent::boot();

        // 设置默认开始时间为当前时间
        static::creating(function ($model) {
            if (empty($model->start_at)) {
                $model->start_at = now();
            }
        });

        static::deleting(function ($coupon) {
            $coupon->redemptions()->delete();
        });
    }

    /**
     * 判断优惠券是否有效
     *
     * @return bool
     */
    public function isValid($userId = null)
    {
        // 检查优惠券是否已经使用（对于仅可使用一次的优惠券）
        if ($this->is_used) {
            return false;
        }

        // 检查优惠券是否过期
        if ($this->end_at && $this->end_at->isPast()) {
            return false;
        }

        // 检查优惠券是否超过总使用次数
        if (($this->max_uses !== null || $this->max_uses !== 0) && $this->max_uses <= $this->times_used) {
            return false;
        }

        // 如果提供了 userId，检查该用户的使用次数是否超限
        // 检查每天的使用限制
        if (! empty($userId)) {
            // 检查该用户的使用次数是否超限
            if (! empty($this->max_uses_per_user)) {
                $userUses = $this->redemptions()
                    ->where('user_id', $userId)
                    ->whereDate('date_used', '>=', now()->subHours($this->use_interval))
                    ->count();
                if ($userUses >= $this->max_uses_per_user) {
                    return false;
                }
            }

            // 检查每天的使用限制
            if (! empty($this->daily_limit)) {
                $todayUses = $this->redemptions()
                    ->where('user_id', $userId)
                    ->whereDate('date_used', today())
                    ->count();
                if ($todayUses >= $this->daily_limit) {
                    return false;
                }
            }

            // 检查用户的使用间隔限制
            if (! empty($this->use_interval)) {
                $lastUse = $this->redemptions()
                    ->where('user_id', $userId)
                    ->latest('last_used_at')
                    ->first();

                if ($lastUse && $lastUse->last_used_at->diffInHours(now()) < $this->use_interval) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 检查是否超出使用限制
     *
     * @return bool
     */
    public function isMaxedOut(): bool
    {
        // 条件 1: 检查最大使用次数是否存在，并且超过或等于最大使用次数
        if ($this->max_uses > 0 && $this->times_used >= $this->max_uses) {
            return true;
        }

        // 条件 2: 如果最大使用次数为 0 或 null，则不限制使用
        return false;
    }

    /**
     * 生成一个不重复的优惠券码
     *
     * @param  int  $length  优惠券码的长度，默认为16
     * @return string 生成的优惠券码
     */
    public static function findAvailableCode(int $length = 16): string
    {
        do {
            // 生成一个指定长度的随机字符串，并转成大写
            $code = strtoupper(Str::random($length));
            // 如果生成的代码已经存在就继续循环
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function redemptions()
    {
        return $this->hasMany(CouponRedemption::class, 'coupon_id');
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'coupon_redemptions', 'coupon_id', 'order_id')
            ->withPivot(['last_used_at', 'date_used'])
            ->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'coupon_redemptions', 'coupon_id', 'user_id')
            ->withPivot(['last_used_at', 'date_used'])
            ->withTimestamps();
    }

    // 确保 end_at 总是返回 Carbon 实例
    public function getEndAtAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    // 同样处理 start_at
    public function getStartAtAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }
}
