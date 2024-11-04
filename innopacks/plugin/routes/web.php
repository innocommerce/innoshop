<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use InnoShop\Plugin\Controllers\MarketplaceController;
use InnoShop\Plugin\Controllers\PluginController;
use InnoShop\Plugin\Controllers\PluginMarketController;
use InnoShop\Plugin\Controllers\ThemeMarketController;

Route::resource('/plugins', PluginController::class);
Route::post('/plugins/enabled', [PluginController::class, 'updateStatus'])->name('plugins.update_status');

Route::get('/plugin_market', [PluginMarketController::class, 'index'])->name('plugin_market.index');
Route::get('/plugin_market/{slug}', [PluginMarketController::class, 'show'])->name('plugin_market.show');

Route::get('/theme_market', [ThemeMarketController::class, 'index'])->name('theme_market.index');
Route::get('/theme_market/{slug}', [ThemeMarketController::class, 'show'])->name('theme_market.show');

Route::get('/marketplaces/{id}/download', [MarketplaceController::class, 'download'])->name('marketplaces.download');
Route::post('/marketplaces/quick_checkout', [MarketplaceController::class, 'quickCheckout'])->name('marketplaces.quick_checkout');
Route::put('/marketplaces/domain_token', [MarketplaceController::class, 'updateDomainToken'])->name('marketplaces.domain_token');
