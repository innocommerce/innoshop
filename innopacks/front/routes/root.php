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
use InnoShop\Front\Controllers\Account;

Route::get('/', [Controllers\HomeController::class, 'index'])->name('front.home.index');

// Social
Route::get('/social/{provider}/redirect', [Account\SocialController::class, 'redirect'])->name('social.redirect');
Route::get('/social/{provider}/callback', [Account\SocialController::class, 'callback'])->name('social.callback');

// Upload
Route::post('/upload/images', [Controllers\UploadController::class, 'images'])->name('upload.images');
Route::post('/upload/files', [Controllers\UploadController::class, 'files'])->name('upload.files');
