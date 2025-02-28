<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\EmailVerification\Controllers\EmailVerifyController;

Route::prefix('account')
    ->name('account.')
    ->middleware('customer_auth:customer')
    ->group(function () {
        Route::get('/verified', [EmailVerifyController::class, 'verified'])->name('verified');
        Route::get('/verify', [EmailVerifyController::class, 'verify'])->name('verify');
    });
