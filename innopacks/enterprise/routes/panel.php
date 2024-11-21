<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use InnoShop\Enterprise\PanelControllers\FileManagerController;
use InnoShop\Enterprise\PanelControllers\VolumePriceController;

Route::get('file_manager', [FileManagerController::class, 'index'])->name('file_manager.index');

Route::get('volume_prices', [VolumePriceController::class, 'index'])->name('volume_prices.index');
