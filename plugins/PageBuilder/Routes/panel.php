<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\PageBuilder\Controllers\Panel\PageBuilderController;

Route::get('/pbuilder', [PageBuilderController::class, 'index'])->name('pbuilder.index');
Route::get('/pbuilder/{page}', [PageBuilderController::class, 'index'])->name('pbuilder.page.index');

// 设计数据管理
Route::put('/pbuilder/{page}/modules', [PageBuilderController::class, 'update'])->name('pbuilder.modules.update');

// 模块预览
Route::post('/pbuilder/{page}/modules/preview', [PageBuilderController::class, 'previewModule'])->name('pbuilder.modules.preview');

// 演示数据
Route::post('/pbuilder/{page}/demo/import', [PageBuilderController::class, 'importDemo'])->name('pbuilder.demo.import');
