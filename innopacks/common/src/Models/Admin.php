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

class Admin extends AuthUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'locale', 'password', 'active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
}
