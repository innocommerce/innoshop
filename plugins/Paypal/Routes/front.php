<?php

/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Paypal\Controllers\PaypalController;

Route::post('/paypal/create', [PaypalController::class, 'create'])->name('paypal.create');
Route::post('/paypal/capture', [PaypalController::class, 'capture'])->name('paypal.capture');
