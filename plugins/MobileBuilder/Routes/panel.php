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

Route::get('/inno_mobile_builder', [MobileBuilderController::class, 'index'])->name('inno_mobile_builder.index');
Route::post('/inno_mobile_builder/images', [MobileBuilderController::class, 'uploadImages'])->name('inno_mobile_builder.upload.images');

Route::get('/inno_mobile_builder/design', [MobileBuilderController::class, 'getDesign'])->name('inno_mobile_builder.design.get');
Route::put('/inno_mobile_builder/design', [MobileBuilderController::class, 'saveDesign'])->name('inno_mobile_builder.design.save');
