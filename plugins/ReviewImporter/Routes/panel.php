<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\ReviewImporter\Controllers\ReviewImportController;

Route::get('/review_importer/template', [ReviewImportController::class, 'template'])->name('review_importer.template');
Route::post('/review_importer/import', [ReviewImportController::class, 'import'])->name('review_importer.import');
