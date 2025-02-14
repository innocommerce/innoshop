<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     XING GUI YU <xingguiyu@foxmail.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Coupon\Controllers\CouponController;

Route::resource('/coupons', CouponController::class);
Route::post('/apply-coupon', [CouponController::class, 'apply'])->name('api.apply-coupon');
