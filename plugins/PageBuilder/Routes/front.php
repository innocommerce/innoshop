<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use InnoShop\Common\Repositories\PageRepo;
use Plugin\PageBuilder\Controllers\Front\HomeController;
use Plugin\PageBuilder\Controllers\Front\PageController;

Route::get('/', [HomeController::class, 'index'])->name('home.index');

// Pages
if (installed()) {
    $pages = PageRepo::getInstance()->withActive()->builder()->get();
    foreach ($pages as $page) {
        Route::get($page->slug, [PageController::class, 'show'])->name('pages.'.$page->slug);
    }
}
