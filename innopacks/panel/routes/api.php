<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use InnoShop\Panel\ApiControllers;

// Article
Route::get('/articles', [ApiControllers\ArticleController::class, 'index'])->name('articles.index');
Route::post('/articles', [ApiControllers\ArticleController::class, 'store'])->name('articles.store');
Route::put('/articles/{article}', [ApiControllers\ArticleController::class, 'update'])->name('articles.update');
Route::delete('/articles/{article}', [ApiControllers\ArticleController::class, 'destroy'])->name('articles.destroy');

// Catalog
Route::get('/catalogs', [ApiControllers\CatalogController::class, 'index'])->name('catalogs.index');
Route::get('/catalogs/autocomplete', [ApiControllers\CatalogController::class, 'autocomplete'])->name('catalogs.autocomplete');
Route::post('/catalogs', [ApiControllers\CatalogController::class, 'store'])->name('catalogs.store');
Route::put('/catalogs/{catalog}', [ApiControllers\CatalogController::class, 'update'])->name('catalogs.update');
Route::delete('/catalogs/{catalog}', [ApiControllers\CatalogController::class, 'destroy'])->name('catalogs.destroy');

// Page
Route::get('/pages', [ApiControllers\PageController::class, 'index'])->name('pages.index');
Route::post('/pages', [ApiControllers\PageController::class, 'store'])->name('pages.store');
Route::put('/pages/{page}', [ApiControllers\PageController::class, 'update'])->name('pages.update');
Route::delete('/pages/{page}', [ApiControllers\PageController::class, 'destroy'])->name('pages.destroy');

// Tag
Route::get('/tags', [ApiControllers\TagController::class, 'index'])->name('tags.index');
Route::get('/tags/autocomplete', [ApiControllers\TagController::class, 'autocomplete'])->name('tags.autocomplete');
Route::post('/tags', [ApiControllers\TagController::class, 'store'])->name('tags.store');
Route::put('/tags/{tag}', [ApiControllers\TagController::class, 'update'])->name('tags.update');
Route::delete('/tags/{tag}', [ApiControllers\TagController::class, 'destroy'])->name('tags.destroy');
