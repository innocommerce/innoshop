<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Distribution\Controllers\Panel\DistributionController;

Route::get('/customers/{customer}/commissions', [DistributionController::class, 'commissions'])->name('distributions.commissions');
Route::get('/customers/{customer}/orders', [DistributionController::class, 'orders'])->name('distributions.orders');
