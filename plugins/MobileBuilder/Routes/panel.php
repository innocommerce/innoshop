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
Route::post('/mobile_builder/images', [MobileBuilderController::class, 'uploadImages'])->name('mobile_builder.upload.images');

Route::get('/mobile_builder/design', [MobileBuilderController::class, 'getDesign'])->name('mobile_builder.design.get');
Route::put('/mobile_builder/design', [MobileBuilderController::class, 'saveDesign'])->name('mobile_builder.design.save');
