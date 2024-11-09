<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\MobileBuilder\Controllers\MobileBuilderController;

Route::get('/mobile_builder', [MobileBuilderController::class, 'index'])->name('mobile_builder.index');
