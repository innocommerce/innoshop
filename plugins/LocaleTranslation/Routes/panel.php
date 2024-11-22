<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\LocaleTranslation\Controllers\TranslationController;

Route::get('/locale_translations', [TranslationController::class, 'index'])->name('locale_translations.index');
Route::get('/locale_translations/values', [TranslationController::class, 'values'])->name('locale_translations.values');
Route::post('/locale_translations/format', [TranslationController::class, 'format'])->name('locale_translations.format');
Route::post('/locale_translations/translate', [TranslationController::class, 'translateText'])->name('locale_translations.translate');
