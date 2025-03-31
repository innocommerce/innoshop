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
use Illuminate\Support\Collection;
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
     * @return Collection
     */
    public function getRoleNames(): Collection
    {
        return $this->roles->pluck('name');
    }

    /**
     * @return string
     */
    public function getRoleLabel(): string
    {
        if ($this->id == 1) {
            return 'Root';
        }
        $names = $this->getRoleNames();

        return $names->implode(', ');
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
}
