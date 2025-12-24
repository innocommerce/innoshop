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

Route::put('/pbuilder/modules', [PageBuilderController::class, 'update'])->name('pbuilder.modules.update');
Route::put('/pbuilder/{page}/modules', [PageBuilderController::class, 'update'])->name('pbuilder.page.modules.update');
Route::post('/pbuilder/modules/preview', [PageBuilderController::class, 'previewModule'])->name('pbuilder.modules.preview');
Route::post('/pbuilder/{page}/modules/preview', [PageBuilderController::class, 'previewModule'])->name('pbuilder.page.modules.preview');
Route::post('/pbuilder/demo/import', [PageBuilderController::class, 'importDemo'])->name('pbuilder.demo.import');
Route::post('/pbuilder/{page}/demo/import', [PageBuilderController::class, 'importDemo'])->name('pbuilder.page.demo.import');
