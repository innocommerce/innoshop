<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\ViewTracker\Controllers\Front\ViewTrackerController;

Route::get('/install/{image}', [ViewTrackerController::class, 'install'])->name('install');
Route::get('/en/install/{image}', [ViewTrackerController::class, 'install'])->name('install');
