<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\WebBuilder\Controllers\Panel\HomeBuilderController;
use Plugin\WebBuilder\Controllers\Panel\PageBuilderController;

// Home Builder
Route::get('/web_builder', [HomeBuilderController::class, 'index'])->name('web_builder.index');
Route::get('/web_builder/design', [HomeBuilderController::class, 'getDesign'])->name('web_builder.design');
Route::put('/web_builder/design', [HomeBuilderController::class, 'saveDesign'])->name('web_builder.design.update');

// Single Page Builder
Route::get('/page_builder/{page}', [PageBuilderController::class, 'index'])->name('page_builder.index');
Route::get('/page_builder/{page}/design', [PageBuilderController::class, 'getDesign'])->name('page_builder.design');
Route::put('/page_builder/{page}/design', [PageBuilderController::class, 'saveDesign'])->name('page_builder.design.update');
