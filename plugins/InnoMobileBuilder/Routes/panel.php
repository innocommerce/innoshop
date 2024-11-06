<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\InnoMobileBuilder\Controllers\InnoMobileBuilderController;

Route::get('/inno_mobile_builder', [InnoMobileBuilderController::class, 'index'])->name('inno_mobile_builder.index');
Route::post('/inno_mobile_builder/images', [InnoMobileBuilderController::class, 'uploadImages'])->name('inno_mobile_builder.upload.images');
