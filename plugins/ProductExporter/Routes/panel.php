<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\ProductExporter\Controllers\ProductImportController;

Route::get('/exporter', [ProductImportController::class, 'index'])->name('exporter.index');
Route::post('/exporter/export', [ProductImportController::class, 'export'])->name('exporter.export');
Route::post('/exporter/import', [ProductImportController::class, 'import'])->name('exporter.import');
