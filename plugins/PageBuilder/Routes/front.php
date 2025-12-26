<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\PageBuilder\Controllers\Front\HomeController;
use Plugin\PageBuilder\Controllers\Front\PageController;

Route::get('/', [HomeController::class, 'index'])->name('home.index');

// Pages - Use page-{slug} pattern to maintain consistency with other resources (product-{slug}, category-{slug}, article-{slug})
Route::get('/pages', [PageController::class, 'index'])->name('pages.index');
Route::get('/pages/{page}', [PageController::class, 'show'])->name('pages.show');
Route::get('/page-{slug}', [PageController::class, 'slugShow'])->name('pages.slug_show');
