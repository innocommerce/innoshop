<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\InnoChat\Controllers\InnoChatController;

Route::get('/inno_chat', [InnoChatController::class, 'index'])->name('openai.index');

Route::get('/inno_chat/histories', [InnoChatController::class, 'histories'])->name('openai.histories');
Route::get('/inno_chat/completions', [InnoChatController::class, 'completions'])->name('openai.completions');
