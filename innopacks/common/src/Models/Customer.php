<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Notifications\Notifiable;
use InnoShop\Common\Models\Customer\Favorite;
use InnoShop\Common\Models\Customer\Group;
use InnoShop\Common\Models\Customer\Social;
use InnoShop\Common\Models\Customer\Transaction;
use InnoShop\Common\Notifications\ForgottenNotification;
use InnoShop\Common\Notifications\RegistrationNotification;
use Laravel\Sanctum\HasApiTokens;
use Throwable;

class Customer extends AuthUser
{
    use HasApiTokens, HasFactory, Notifiable;

    // From constants
    public const FROM_PC_WEB = 'pc_web';

    public const FROM_MOBILE_WEB = 'mobile_web';

    public const FROM_MINIAPP = 'miniapp';

    public const FROM_WECHAT_OFFICIAL = 'wechat_official';

    public const FROM_APP = 'app';

    protected $fillable = [
        'email', 'password', 'name', 'avatar', 'customer_group_id', 'address_id', 'locale', 'active', 'code', 'from',
        'deleted_at',
    ];

    /**
     * Get customer group object.
     *
     * @return BelongsTo
     */
    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * @return HasMany
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'customer_id');
    }

    /**
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    /**
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'customer_id');
    }

    /**
     * @return HasMany
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'customer_id');
    }

    /**
     * @return HasMany
     */
    public function socials(): HasMany
    {
        return $this->hasMany(Social::class, 'customer_id');
    }

    /**
     * @return bool
     */
    public function getHasPasswordAttribute(): bool
    {
        return ! empty($this->password);
    }

    /**
     * Check if the given string matches the user's set password.
     *
     * @param  string  $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function syncBalance(): void
    {
        $this->balance = $this->transactions->sum('amount');
        $this->saveOrFail();
    }

    /**
     * @return void
     */
    public function notifyRegistration(): void
    {
        $useQueue = system_setting('use_queue', false);
        if ($useQueue) {
            $this->notify(new RegistrationNotification($this));
        } else {
            $this->notifyNow(new RegistrationNotification($this));
        }
    }

    /**
     * @param  $code
     * @return void
     */
    public function notifyForgotten($code): void
    {
        $useQueue = system_setting('use_queue', false);
        if ($useQueue) {
            $this->notify(new ForgottenNotification($this, $code));
        } else {
            $this->notifyNow(new ForgottenNotification($this, $code));
        }
    }

    /**
     * Get all from options.
     *
     * @return array
     */
    public static function getFromOptions(): array
    {
        return [
            self::FROM_PC_WEB          => trans('common/customer.from_pc_web'),
            self::FROM_MOBILE_WEB      => trans('common/customer.from_mobile_web'),
            self::FROM_MINIAPP         => trans('common/customer.from_miniapp'),
            self::FROM_WECHAT_OFFICIAL => trans('common/customer.from_wechat_official'),
            self::FROM_APP             => trans('common/customer.from_app'),
        ];
    }

    /**
     * Get the display name of the from.
     *
     * @return string
     */
    public function getFromDisplayAttribute(): string
    {
        $options = self::getFromOptions();

        return $options[$this->from] ?? $this->from;
    }

    /**
     * Check if the customer is from mobile.
     *
     * @return bool
     */
    public function isMobile(): bool
    {
        return in_array($this->from, [self::FROM_MOBILE_WEB, self::FROM_MINIAPP, self::FROM_WECHAT_OFFICIAL, self::FROM_APP]);
    }
}
