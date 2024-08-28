<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use InnoShop\Panel\Controllers;

Route::get('login', [Controllers\LoginController::class, 'index'])->name('login.index');
Route::post('login', [Controllers\LoginController::class, 'store'])->name('login.store');

Route::middleware(['admin_auth:admin'])
    ->group(function () {
        Route::get('logout', [Controllers\LogoutController::class, 'index'])->name('logout.index');

        Route::get('/', [Controllers\DashboardController::class, 'index'])->name('dashboard.index');

        Route::get('/locale/{code}', [Controllers\LocaleController::class, 'switch'])->name('locale.switch');

        Route::resource('/orders', Controllers\OrderController::class);
        Route::put('/orders/{order}/status', [Controllers\OrderController::class, 'changeStatus'])->name('orders.change_status');

        Route::resource('/order_returns', Controllers\OrderReturnController::class);

        Route::resource('/products', Controllers\ProductController::class);

        Route::resource('/categories', Controllers\CategoryController::class);
        Route::put('/categories/{currency}/active', [Controllers\CategoryController::class, 'active'])->name('categories.active');

        Route::resource('/attribute_groups', Controllers\AttributeGroupController::class);

        Route::resource('/attributes', Controllers\AttributeController::class);

        Route::resource('/brands', Controllers\BrandController::class);
        Route::put('/brands/{currency}/active', [Controllers\BrandController::class, 'active'])->name('brands.active');

        Route::resource('/articles', Controllers\ArticleController::class);
        Route::put('/articles/{currency}/active', [Controllers\ArticleController::class, 'active'])->name('articles.active');

        Route::resource('/catalogs', Controllers\CatalogController::class);
        Route::put('/catalogs/{catalog}/active', [Controllers\CatalogController::class, 'active'])->name('catalogs.active');

        Route::resource('/tags', Controllers\TagController::class);
        Route::put('/tags/{tag}/active', [Controllers\TagController::class, 'active'])->name('tags.active');

        Route::resource('/pages', Controllers\PageController::class);
        Route::put('/pages/{page}/active', [Controllers\PageController::class, 'active'])->name('pages.active');

        Route::resource('/customers', Controllers\CustomerController::class);
        Route::put('/customers/{customer}/active', [Controllers\CustomerController::class, 'active'])->name('customers.active');

        Route::resource('/customer_groups', Controllers\CustomerGroupController::class);

        Route::get('/analytics', [Controllers\AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/analytics/order', [Controllers\AnalyticsController::class, 'order'])->name('analytics_order');
        Route::get('/analytics/product', [Controllers\AnalyticsController::class, 'product'])->name('analytics_product');
        Route::get('/analytics/customer', [Controllers\AnalyticsController::class, 'customer'])->name('analytics_customer');

        Route::get('/locales', [Controllers\LocaleController::class, 'index'])->name('locales.index');
        Route::post('/locales/install', [Controllers\LocaleController::class, 'install'])->name('locales.install');
        Route::get('/locales/{locale}/edit', [Controllers\LocaleController::class, 'edit'])->name('locales.edit');
        Route::put('/locales/{locale}', [Controllers\LocaleController::class, 'update'])->name('locales.update');
        Route::post('/locales/{code}/uninstall', [Controllers\LocaleController::class, 'uninstall'])->name('locales.uninstall');
        Route::put('/locales/{country}/active', [Controllers\LocaleController::class, 'active'])->name('locales.active');

        Route::get('/themes', [Controllers\ThemeController::class, 'index'])->name('themes.index');
        Route::put('/themes/{country}/active', [Controllers\ThemeController::class, 'enable'])->name('themes.active');
        Route::get('/themes/settings', [Controllers\ThemeController::class, 'settings'])->name('themes_settings.index');
        Route::put('/themes/settings', [Controllers\ThemeController::class, 'updateSettings'])->name('themes_settings.update');

        Route::get('/account', [Controllers\AccountController::class, 'index'])->name('account.index');
        Route::put('/account', [Controllers\AccountController::class, 'update'])->name('account.update');

        Route::get('/settings', [Controllers\SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [Controllers\SettingController::class, 'update'])->name('settings.update');

        Route::post('/content_ai/generate', [Controllers\ContentAIController::class, 'generate'])->name('content_ai.generate');

        Route::resource('/admins', Controllers\AdminController::class);
        Route::put('/admins/{currency}/active', [Controllers\AdminController::class, 'active'])->name('admins.active');

        Route::resource('/roles', Controllers\RoleController::class);

        Route::resource('/currencies', Controllers\CurrencyController::class);
        Route::put('/currencies/{currency}/active', [Controllers\CurrencyController::class, 'active'])->name('currencies.active');

        Route::resource('/countries', Controllers\CountryController::class);
        Route::put('/countries/{country}/active', [Controllers\CountryController::class, 'active'])->name('countries.active');

        Route::resource('/states', Controllers\StateController::class);
        Route::put('/states/{state}/active', [Controllers\StateController::class, 'active'])->name('states.active');

        Route::resource('/regions', Controllers\RegionController::class);
        Route::put('/regions/{state}/active', [Controllers\RegionController::class, 'active'])->name('regions.active');

        Route::resource('/tax_classes', Controllers\TaxClassController::class);
        Route::resource('/tax_rates', Controllers\TaxRateController::class);
    });
