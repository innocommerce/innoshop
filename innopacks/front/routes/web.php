<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use InnoShop\Front\Controllers;

Route::get('/', [Controllers\HomeController::class, 'index'])->name('home.index');

// Catalogs
Route::get('/catalogs', [Controllers\CatalogController::class, 'index'])->name('catalogs.index');
Route::get('/catalog-{slug}', [Controllers\CatalogController::class, 'show'])->name('catalogs.show');

// Articles
Route::get('/articles', [Controllers\ArticleController::class, 'index'])->name('articles.index');
Route::get('/article-{slug}', [Controllers\ArticleController::class, 'show'])->name('articles.show');

// Tags
Route::get('/tags', [Controllers\TagController::class, 'index'])->name('tags.index');
Route::get('/tags-{slug}', [Controllers\TagController::class, 'show'])->name('tags.show');

// Upload
Route::post('/upload/images', [Controllers\UploadController::class, 'images'])->name('upload.images');
Route::post('/upload/files', [Controllers\UploadController::class, 'files'])->name('upload.files');

// Pages, like product, service, about
Route::get('/{slug}', [Controllers\PageController::class, 'show'])->name('pages.show');
