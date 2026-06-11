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

Route::post('/plugins/enabled', [PluginController::class, 'updateStatus'])->name('plugins.update_status');
Route::post('/plugins/seeders', [PluginController::class, 'runSeeders'])->name('plugins.run_seeders');
Route::post('/plugins/reset', [PluginController::class, 'reset'])->name('plugins.reset');
Route::resource('/plugins', PluginController::class);

Route::get('/plugin-market', [PluginMarketController::class, 'index'])->name('plugin-market.index');
Route::get('/plugin-market/{slug}', [PluginMarketController::class, 'show'])->name('plugin-market.show');

Route::get('/theme-market', [ThemeMarketController::class, 'index'])->name('theme-market.index');
Route::get('/theme-market/{slug}', [ThemeMarketController::class, 'show'])->name('theme-market.show');

Route::get('/marketplaces/{id}/download', [MarketplaceController::class, 'download'])->name('marketplaces.download');
Route::post('/marketplaces/quick_checkout', [MarketplaceController::class, 'quickCheckout'])->name('marketplaces.quick_checkout');
Route::get('/marketplaces/order_status/{number}', [MarketplaceController::class, 'checkOrderStatus'])->name('marketplaces.order_status');
Route::put('/marketplaces/domain_token', [MarketplaceController::class, 'updateDomainToken'])->name('marketplaces.domain_token');
Route::put('/marketplaces/auth_token', [MarketplaceController::class, 'updateAuthToken'])->name('marketplaces.auth_token');
Route::get('/marketplaces/get_token', [MarketplaceController::class, 'getToken'])->name('marketplaces.get_token');
Route::get('/marketplaces/billing_methods', [MarketplaceController::class, 'billingMethods'])->name('marketplaces.billing_methods');
Route::post('/marketplaces/login', [MarketplaceController::class, 'proxyLogin'])->name('marketplaces.login');
Route::post('/marketplaces/register', [MarketplaceController::class, 'proxyRegister'])->name('marketplaces.register');
Route::post('/marketplaces/bind_domain', [MarketplaceController::class, 'proxyBindDomain'])->name('marketplaces.bind_domain');
Route::get('/marketplaces/account', [MarketplaceController::class, 'proxyAccountMe'])->name('marketplaces.account');
Route::post('/marketplaces/clear_cache', [MarketplaceController::class, 'clearCache'])->name('marketplaces.clear_cache');
