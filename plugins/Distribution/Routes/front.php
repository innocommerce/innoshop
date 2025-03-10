<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Distribution\Controllers\Front\DistributionController;

Route::prefix('account')
    ->name('account.')
    ->middleware('customer_auth:customer')
    ->group(function () {
        Route::get('/distributions', [DistributionController::class, 'index'])->name('distributions.index');
        Route::get('/distributions/members', [DistributionController::class, 'members'])->name('distributions.members');
        Route::get('/distributions/commissions', [DistributionController::class, 'commissions'])->name('distributions.commissions');
        Route::get('/distributions/orders', [DistributionController::class, 'orders'])->name('distributions.orders');
    });
