<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use InnoShop\RestAPI\Middleware\EnsureUserIsAdmin;
use InnoShop\RestAPI\PanelApiControllers;
use InnoShop\RestAPI\PanelApiControllers\FileManagerController;

Route::get('/', [PanelApiControllers\IntroductionController::class, 'index'])->name('base.index');
Route::post('/login', [PanelApiControllers\AuthController::class, 'login'])->name('auth.login');

$middlewares = ['auth:sanctum', EnsureUserIsAdmin::class];
Route::middleware($middlewares)->group(function () {

    Route::get('/admin', [PanelApiControllers\AuthController::class, 'admin'])->name('auth.admin');

    Route::get('/dashboard', [PanelApiControllers\DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/{date}', [PanelApiControllers\DashboardController::class, 'daily'])->name('dashboard.daily');

    Route::get('/products', [PanelApiControllers\ProductController::class, 'index'])->name('products.index');
    Route::get('/products/names', [PanelApiControllers\ProductController::class, 'names'])->name('products.names');
    Route::get('/products/autocomplete', [PanelApiControllers\ProductController::class, 'autocomplete'])->name('products.autocomplete');
    Route::get('/products/sku_autocomplete', [PanelApiControllers\ProductController::class, 'skuAutocomplete'])->name('products.sku_autocomplete');
    Route::post('/products', [PanelApiControllers\ProductController::class, 'store'])->name('products.store');
    Route::post('/products/import', [PanelApiControllers\ProductController::class, 'import'])->name('products.import');
    Route::get('/products/{id}', [PanelApiControllers\ProductController::class, 'show'])->name('products.show');
    Route::put('/products/{id}', [PanelApiControllers\ProductController::class, 'update'])->name('products.update');
    Route::patch('/products/{id}', [PanelApiControllers\ProductController::class, 'patch'])->name('products.patch');
    Route::delete('/products/{id}', [PanelApiControllers\ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/categories', [PanelApiControllers\CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/names', [PanelApiControllers\CategoryController::class, 'names'])->name('categories.names');
    Route::get('/categories/autocomplete', [PanelApiControllers\CategoryController::class, 'autocomplete'])->name('categories.autocomplete');
    Route::get('/categories/{id}', [PanelApiControllers\CategoryController::class, 'show'])->name('categories.show');
    Route::post('/categories', [PanelApiControllers\CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}', [PanelApiControllers\CategoryController::class, 'update'])->name('categories.update');
    Route::patch('/categories/{id}', [PanelApiControllers\CategoryController::class, 'patch'])->name('categories.patch');
    Route::delete('/categories/{id}', [PanelApiControllers\CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/brands', [PanelApiControllers\BrandController::class, 'index'])->name('brands.index');
    Route::get('/brands/names', [PanelApiControllers\BrandController::class, 'names'])->name('brands.name');
    Route::get('/brands/autocomplete', [PanelApiControllers\BrandController::class, 'autocomplete'])->name('brands.autocomplete');
    Route::get('/brands/{id}', [PanelApiControllers\BrandController::class, 'show'])->name('brands.show');
    Route::post('/brands', [PanelApiControllers\BrandController::class, 'store'])->name('brands.store');
    Route::put('/brands/{id}', [PanelApiControllers\BrandController::class, 'update'])->name('brands.update');
    Route::patch('/brands/{id}', [PanelApiControllers\BrandController::class, 'patch'])->name('brands.patch');
    Route::delete('/brands/{id}', [PanelApiControllers\BrandController::class, 'destroy'])->name('brands.destroy');

    Route::get('/articles', [PanelApiControllers\ArticleController::class, 'index'])->name('articles.index');
    Route::get('/articles/names', [PanelApiControllers\ArticleController::class, 'names'])->name('articles.names');
    Route::get('/articles/autocomplete', [PanelApiControllers\ArticleController::class, 'autocomplete'])->name('articles.autocomplete');
    Route::post('/articles', [PanelApiControllers\ArticleController::class, 'store'])->name('articles.store');
    Route::put('/articles/{article}', [PanelApiControllers\ArticleController::class, 'update'])->name('articles.update');
    Route::patch('/articles/{article}', [PanelApiControllers\ArticleController::class, 'patch'])->name('articles.patch');
    Route::delete('/articles/{article}', [PanelApiControllers\ArticleController::class, 'destroy'])->name('articles.destroy');

    Route::get('/catalogs', [PanelApiControllers\CatalogController::class, 'index'])->name('catalogs.index');
    Route::get('/catalogs/names', [PanelApiControllers\CatalogController::class, 'names'])->name('catalogs.names');
    Route::get('/catalogs/autocomplete', [PanelApiControllers\CatalogController::class, 'autocomplete'])->name('catalogs.autocomplete');
    Route::post('/catalogs', [PanelApiControllers\CatalogController::class, 'store'])->name('catalogs.store');
    Route::put('/catalogs/{catalog}', [PanelApiControllers\CatalogController::class, 'update'])->name('catalogs.update');
    Route::patch('/catalogs/{catalog}', [PanelApiControllers\CatalogController::class, 'patch'])->name('catalogs.patch');
    Route::delete('/catalogs/{catalog}', [PanelApiControllers\CatalogController::class, 'destroy'])->name('catalogs.destroy');

    Route::get('/orders', [PanelApiControllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [PanelApiControllers\OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}/status', [PanelApiControllers\OrderController::class, 'changeStatus'])->name('orders.change_status');
    Route::post('/orders/{order}/notes', [PanelApiControllers\OrderController::class, 'updateNote'])->name('orders.update_note');

    Route::get('/shipments', [PanelApiControllers\ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/{shipment}', [PanelApiControllers\ShipmentController::class, 'show'])->name('shipments.show');
    Route::post('/orders/{order}/shipments', [PanelApiControllers\ShipmentController::class, 'store'])->name('shipments.store');
    Route::delete('/shipments/{shipment}', [PanelApiControllers\ShipmentController::class, 'destroy'])->name('shipments.destroy');
    Route::get('/shipments/{shipment}/traces', [PanelApiControllers\ShipmentController::class, 'getTraces'])->name('shipments.get_traces');

    Route::get('/pages', [PanelApiControllers\PageController::class, 'index'])->name('pages.index');
    Route::get('/pages/names', [PanelApiControllers\PageController::class, 'names'])->name('pages.names');
    Route::get('/pages/autocomplete', [PanelApiControllers\PageController::class, 'autocomplete'])->name('pages.autocomplete');
    Route::post('/pages', [PanelApiControllers\PageController::class, 'store'])->name('pages.store');
    Route::put('/pages/{page}', [PanelApiControllers\PageController::class, 'update'])->name('pages.update');
    Route::patch('/pages/{page}', [PanelApiControllers\PageController::class, 'patch'])->name('pages.patch');
    Route::delete('/pages/{page}', [PanelApiControllers\PageController::class, 'destroy'])->name('pages.destroy');

    Route::get('/tags', [PanelApiControllers\TagController::class, 'index'])->name('tags.index');
    Route::get('/tags/names', [PanelApiControllers\TagController::class, 'names'])->name('tags.name');
    Route::get('/tags/autocomplete', [PanelApiControllers\TagController::class, 'autocomplete'])->name('tags.autocomplete');
    Route::post('/tags', [PanelApiControllers\TagController::class, 'store'])->name('tags.store');
    Route::put('/tags/{tag}', [PanelApiControllers\TagController::class, 'update'])->name('tags.update');
    Route::patch('/tags/{tag}', [PanelApiControllers\TagController::class, 'patch'])->name('tags.patch');
    Route::delete('/tags/{tag}', [PanelApiControllers\TagController::class, 'destroy'])->name('tags.destroy');

    Route::get('/attributes', [PanelApiControllers\AttributeController::class, 'index'])->name('attributes.index');
    Route::get('/attribute_values', [PanelApiControllers\AttributeValueController::class, 'index'])->name('attribute_values.index');

    Route::get('/options/available', [PanelApiControllers\OptionController::class, 'available'])->name('options.available');
    Route::get('/options/{option}/values', [PanelApiControllers\OptionController::class, 'values'])->name('options.values');

    Route::get('/customers', [PanelApiControllers\CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/names', [PanelApiControllers\CustomerController::class, 'names'])->name('customers.name');
    Route::get('/customers/autocomplete', [PanelApiControllers\CustomerController::class, 'autocomplete'])->name('customers.autocomplete');
    Route::post('/customers', [PanelApiControllers\CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{customer}', [PanelApiControllers\CustomerController::class, 'update'])->name('customers.update');
    Route::patch('/customers/{customer}', [PanelApiControllers\CustomerController::class, 'patch'])->name('customers.patch');
    Route::delete('/customers/{customer}', [PanelApiControllers\CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('/file_manager/files', [FileManagerController::class, 'getFiles'])->name('file_manager.get_files');
    Route::get('/file_manager/directories', [FileManagerController::class, 'getDirectories'])->name('file_manager.get_directories');
    Route::post('/file_manager/directories', [FileManagerController::class, 'createDirectory'])->name('file_manager.create_directory');
    Route::post('/file_manager/upload', [FileManagerController::class, 'uploadFiles'])->name('file_manager.upload');
    Route::post('/file_manager/rename', [FileManagerController::class, 'rename'])->name('file_manager.rename');
    Route::delete('/file_manager/files', [FileManagerController::class, 'destroyFiles'])->name('file_manager.delete_files');
    Route::delete('/file_manager/directories', [FileManagerController::class, 'destroyDirectories'])->name('file_manager.delete_directories');
    Route::post('/file_manager/move_directories', [FileManagerController::class, 'moveDirectories'])->name('file_manager.move_directories');
    Route::post('/file_manager/move_files', [FileManagerController::class, 'moveFiles'])->name('file_manager.move_files');
    Route::post('/file_manager/copy_files', [FileManagerController::class, 'copyFiles'])->name('file_manager.copy_files');

    Route::get('/file_manager/storage_config', [FileManagerController::class, 'getStorageConfig']);
    Route::post('/file_manager/storage_config', [FileManagerController::class, 'saveStorageConfig']);

    Route::post('/ai/generate_image', [PanelApiControllers\AIImageController::class, 'generate'])->name('ai.generate_image');
    Route::get('/ai/models_info', [PanelApiControllers\AIImageController::class, 'modelsInfo'])->name('ai.models_info');

    Route::get('/currencies', [PanelApiControllers\CurrencyController::class, 'index'])->name('currencies.index');
    Route::post('/currencies', [PanelApiControllers\CurrencyController::class, 'store'])->name('currencies.store');
    Route::put('/currencies/{id}', [PanelApiControllers\CurrencyController::class, 'update'])->name('currencies.update');

    // Reviews
    Route::get('/reviews', [PanelApiControllers\ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews', [PanelApiControllers\ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{id}', [PanelApiControllers\ReviewController::class, 'show'])->name('reviews.show');
    Route::put('/reviews/{id}', [PanelApiControllers\ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{id}', [PanelApiControllers\ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Tax Rates
    Route::get('/tax_rates', [PanelApiControllers\TaxRateController::class, 'index'])->name('tax_rates.index');
    Route::get('/tax_rates/{id}', [PanelApiControllers\TaxRateController::class, 'show'])->name('tax_rates.show');
    Route::post('/tax_rates', [PanelApiControllers\TaxRateController::class, 'store'])->name('tax_rates.store');
    Route::put('/tax_rates/{id}', [PanelApiControllers\TaxRateController::class, 'update'])->name('tax_rates.update');
    Route::delete('/tax_rates/{id}', [PanelApiControllers\TaxRateController::class, 'destroy'])->name('tax_rates.destroy');

    // Tax Classes
    Route::get('/tax_classes', [PanelApiControllers\TaxClassController::class, 'index'])->name('tax_classes.index');
    Route::get('/tax_classes/{id}', [PanelApiControllers\TaxClassController::class, 'show'])->name('tax_classes.show');
    Route::post('/tax_classes', [PanelApiControllers\TaxClassController::class, 'store'])->name('tax_classes.store');
    Route::put('/tax_classes/{id}', [PanelApiControllers\TaxClassController::class, 'update'])->name('tax_classes.update');
    Route::delete('/tax_classes/{id}', [PanelApiControllers\TaxClassController::class, 'destroy'])->name('tax_classes.destroy');

    // Settings
    Route::get('/settings', [PanelApiControllers\SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [PanelApiControllers\SettingController::class, 'update'])->name('settings.update');

    // Locales
    Route::get('/locales', [PanelApiControllers\LocaleController::class, 'index'])->name('locales.index');
    Route::get('/locales/{id}', [PanelApiControllers\LocaleController::class, 'show'])->name('locales.show');
    Route::post('/locales', [PanelApiControllers\LocaleController::class, 'store'])->name('locales.store');
    Route::put('/locales/{id}', [PanelApiControllers\LocaleController::class, 'update'])->name('locales.update');
    Route::delete('/locales/{id}', [PanelApiControllers\LocaleController::class, 'destroy'])->name('locales.destroy');

    // Countries
    Route::get('/countries', [PanelApiControllers\CountryController::class, 'index'])->name('countries.index');
    Route::get('/countries/{id}', [PanelApiControllers\CountryController::class, 'show'])->name('countries.show');

    // Regions
    Route::get('/regions', [PanelApiControllers\RegionController::class, 'index'])->name('regions.index');
    Route::get('/regions/{id}', [PanelApiControllers\RegionController::class, 'show'])->name('regions.show');

    // Order Returns
    Route::get('/order_returns', [PanelApiControllers\OrderReturnController::class, 'index'])->name('order_returns.index');
    Route::get('/order_returns/{id}', [PanelApiControllers\OrderReturnController::class, 'show'])->name('order_returns.show');
    Route::put('/order_returns/{id}', [PanelApiControllers\OrderReturnController::class, 'update'])->name('order_returns.update');
});
