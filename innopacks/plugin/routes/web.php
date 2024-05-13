<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use InnoShop\Plugin\Controllers\PluginController;

Route::resource('/plugins', PluginController::class);
Route::post('/plugins/enabled', [PluginController::class, 'updateStatus'])->name('plugins.update_status');
