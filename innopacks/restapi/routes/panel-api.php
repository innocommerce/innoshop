<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use InnoShop\RestAPI\PanelApiControllers;

Route::get('/', [PanelApiControllers\IntroductionController::class, 'index'])->name('base.index');
Route::post('/login', [PanelApiControllers\AuthController::class, 'login'])->name('auth.login');

$middlewares = ['auth:sanctum'];
Route::middleware($middlewares)->group(function () {

    Route::get('/admin', [PanelApiControllers\AuthController::class, 'admin'])->name('auth.admin');

    Route::get('/dashboard', [PanelApiControllers\DashboardController::class, 'index'])->name('dashboard.index');

    Route::get('/products', [PanelApiControllers\ProductController::class, 'index'])->name('products.index');
    Route::get('/products/names', [PanelApiControllers\ProductController::class, 'names'])->name('products.names');
    Route::get('/products/autocomplete', [PanelApiControllers\ProductController::class, 'autocomplete'])->name('products.autocomplete');
    Route::post('/products/import', [PanelApiControllers\ProductController::class, 'import'])->name('products.import');
    Route::put('/products/{spu_code}', [PanelApiControllers\ProductController::class, 'update'])->name('products.update');
    Route::patch('/products/{spu_code}', [PanelApiControllers\ProductController::class, 'patch'])->name('products.patch');

    Route::get('/categories', [PanelApiControllers\CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/names', [PanelApiControllers\CategoryController::class, 'names'])->name('categories.names');
    Route::get('/categories/autocomplete', [PanelApiControllers\CategoryController::class, 'autocomplete'])->name('categories.autocomplete');

    Route::get('/brands', [PanelApiControllers\BrandController::class, 'index'])->name('brands.index');
    Route::get('/brands/names', [PanelApiControllers\BrandController::class, 'names'])->name('brands.name');
    Route::get('/brands/autocomplete', [PanelApiControllers\BrandController::class, 'autocomplete'])->name('brands.autocomplete');

    Route::get('/articles', [PanelApiControllers\ArticleController::class, 'index'])->name('articles.index');
    Route::get('/articles/names', [PanelApiControllers\ArticleController::class, 'names'])->name('articles.names');
    Route::get('/articles/autocomplete', [PanelApiControllers\ArticleController::class, 'autocomplete'])->name('articles.autocomplete');
    Route::post('/articles', [PanelApiControllers\ArticleController::class, 'store'])->name('articles.store');
    Route::put('/articles/{article}', [PanelApiControllers\ArticleController::class, 'update'])->name('articles.update');
    Route::delete('/articles/{article}', [PanelApiControllers\ArticleController::class, 'destroy'])->name('articles.destroy');

    Route::get('/catalogs', [PanelApiControllers\CatalogController::class, 'index'])->name('catalogs.index');
    Route::get('/catalogs/names', [PanelApiControllers\CatalogController::class, 'names'])->name('catalogs.names');
    Route::get('/catalogs/autocomplete', [PanelApiControllers\CatalogController::class, 'autocomplete'])->name('catalogs.autocomplete');
    Route::post('/catalogs', [PanelApiControllers\CatalogController::class, 'store'])->name('catalogs.store');
    Route::put('/catalogs/{catalog}', [PanelApiControllers\CatalogController::class, 'update'])->name('catalogs.update');
    Route::delete('/catalogs/{catalog}', [PanelApiControllers\CatalogController::class, 'destroy'])->name('catalogs.destroy');

    Route::post('/orders/{order}/notes', [PanelApiControllers\OrderController::class, 'updateNote'])->name('orders.update_note');

    Route::post('/orders/{order}/shipments', [PanelApiControllers\ShipmentController::class, 'store'])->name('shipments.store');
    Route::delete('/shipments/{shipment}', [PanelApiControllers\ShipmentController::class, 'destroy'])->name('shipments.destroy');
    Route::get('/shipments/{shipment}/traces', [PanelApiControllers\ShipmentController::class, 'getTraces'])->name('shipments.get_traces');

    Route::get('/pages', [PanelApiControllers\PageController::class, 'index'])->name('pages.index');
    Route::get('/pages/names', [PanelApiControllers\PageController::class, 'names'])->name('pages.names');
    Route::get('/pages/autocomplete', [PanelApiControllers\PageController::class, 'autocomplete'])->name('pages.autocomplete');
    Route::post('/pages', [PanelApiControllers\PageController::class, 'store'])->name('pages.store');
    Route::put('/pages/{page}', [PanelApiControllers\PageController::class, 'update'])->name('pages.update');
    Route::delete('/pages/{page}', [PanelApiControllers\PageController::class, 'destroy'])->name('pages.destroy');

    Route::get('/tags', [PanelApiControllers\TagController::class, 'index'])->name('tags.index');
    Route::get('/tags/names', [PanelApiControllers\TagController::class, 'names'])->name('tags.name');
    Route::get('/tags/autocomplete', [PanelApiControllers\TagController::class, 'autocomplete'])->name('tags.autocomplete');
    Route::post('/tags', [PanelApiControllers\TagController::class, 'store'])->name('tags.store');
    Route::put('/tags/{tag}', [PanelApiControllers\TagController::class, 'update'])->name('tags.update');
    Route::delete('/tags/{tag}', [PanelApiControllers\TagController::class, 'destroy'])->name('tags.destroy');

    Route::get('/attributes', [PanelApiControllers\AttributeController::class, 'index'])->name('attributes.index');
    Route::get('/attribute_values', [PanelApiControllers\AttributeValueController::class, 'index'])->name('attribute_values.index');

    Route::get('/customers', [PanelApiControllers\CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/names', [PanelApiControllers\CustomerController::class, 'names'])->name('customers.name');
    Route::get('/customers/autocomplete', [PanelApiControllers\CustomerController::class, 'autocomplete'])->name('customers.autocomplete');
    Route::post('/customers', [PanelApiControllers\CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{tag}', [PanelApiControllers\CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{tag}', [PanelApiControllers\CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('/file_manager/files', [PanelApiControllers\FileManagerController::class, 'getFiles'])->name('file_manager.get_files');
    Route::get('/file_manager/directories', [PanelApiControllers\FileManagerController::class, 'getDirectories'])->name('file_manager.get_directories');
    Route::post('/file_manager/directories', [PanelApiControllers\FileManagerController::class, 'createDirectory'])->name('file_manager.create_directory');
    Route::post('/file_manager/upload', [PanelApiControllers\FileManagerController::class, 'uploadFiles'])->name('file_manager.upload');
    Route::post('/file_manager/rename', [PanelApiControllers\FileManagerController::class, 'rename'])->name('file_manager.rename');
    Route::delete('/file_manager/files', [PanelApiControllers\FileManagerController::class, 'destroyFiles'])->name('file_manager.delete_files');
    Route::delete('/file_manager/directories', [PanelApiControllers\FileManagerController::class, 'destroyDirectories'])->name('file_manager.delete_directories');
    Route::post('/file_manager/move_directories', [PanelApiControllers\FileManagerController::class, 'moveDirectories'])->name('file_manager.move_directories');
    Route::post('/file_manager/move_files', [PanelApiControllers\FileManagerController::class, 'moveFiles'])->name('file_manager.move_files');
    Route::get('/file_manager/export', [PanelApiControllers\FileManagerController::class, 'exportZip'])->name('file_manager.export');
    Route::post('/file_manager/copy_files', [PanelApiControllers\FileManagerController::class, 'copyFiles'])->name('file_manager.copy_files');

});
