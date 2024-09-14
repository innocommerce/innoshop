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

Route::post('/login', [PanelApiControllers\AuthController::class, 'login'])->name('auth.login');

$middlewares = ['auth:sanctum'];
Route::middleware($middlewares)->group(function () {

    Route::get('/admin', [PanelApiControllers\AuthController::class, 'admin'])->name('auth.admin');

    Route::get('/dashboard', [PanelApiControllers\DashboardController::class, 'index'])->name('dashboard.index');

    Route::get('/articles', [PanelApiControllers\ArticleController::class, 'index'])->name('articles.index');
    Route::post('/articles', [PanelApiControllers\ArticleController::class, 'store'])->name('articles.store');
    Route::put('/articles/{article}', [PanelApiControllers\ArticleController::class, 'update'])->name('articles.update');
    Route::delete('/articles/{article}', [PanelApiControllers\ArticleController::class, 'destroy'])->name('articles.destroy');

    Route::get('/catalogs', [PanelApiControllers\CatalogController::class, 'index'])->name('catalogs.index');
    Route::get('/catalogs/autocomplete', [PanelApiControllers\CatalogController::class, 'autocomplete'])->name('catalogs.autocomplete');
    Route::post('/catalogs', [PanelApiControllers\CatalogController::class, 'store'])->name('catalogs.store');
    Route::put('/catalogs/{catalog}', [PanelApiControllers\CatalogController::class, 'update'])->name('catalogs.update');
    Route::delete('/catalogs/{catalog}', [PanelApiControllers\CatalogController::class, 'destroy'])->name('catalogs.destroy');

    Route::get('/pages', [PanelApiControllers\PageController::class, 'index'])->name('pages.index');
    Route::post('/pages', [PanelApiControllers\PageController::class, 'store'])->name('pages.store');
    Route::put('/pages/{page}', [PanelApiControllers\PageController::class, 'update'])->name('pages.update');
    Route::delete('/pages/{page}', [PanelApiControllers\PageController::class, 'destroy'])->name('pages.destroy');

    Route::get('/tags', [PanelApiControllers\TagController::class, 'index'])->name('tags.index');
    Route::get('/tags/autocomplete', [PanelApiControllers\TagController::class, 'autocomplete'])->name('tags.autocomplete');
    Route::post('/tags', [PanelApiControllers\TagController::class, 'store'])->name('tags.store');
    Route::put('/tags/{tag}', [PanelApiControllers\TagController::class, 'update'])->name('tags.update');
    Route::delete('/tags/{tag}', [PanelApiControllers\TagController::class, 'destroy'])->name('tags.destroy');

    Route::get('/attributes', [PanelApiControllers\AttributeController::class, 'index'])->name('attributes.index');
    Route::get('/attribute_values', [PanelApiControllers\AttributeValueController::class, 'index'])->name('attribute_values.index');

});
