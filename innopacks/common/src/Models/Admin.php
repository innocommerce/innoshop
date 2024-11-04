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
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Notifications\Notifiable;
use InnoShop\Panel\Notifications\ForgottenNotification;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Admin extends AuthUser
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'locale', 'active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

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
}
