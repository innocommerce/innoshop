<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\InquiryQuote\Controllers\Front\QuoteController;

Route::middleware('customer_auth:customer')->group(function () {
    Route::get('/quote', [QuoteController::class, 'current'])->name('quotes.current');
});

Route::prefix('account')
    ->name('account.')
    ->middleware('customer_auth:customer')
    ->group(function () {
        Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');
        Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
        Route::get('/quotes-{number}', [QuoteController::class, 'numberShow'])->name('quotes.number_show');
        Route::get('/quotes/{number}/checkout', [QuoteController::class, 'checkout'])->name('quotes.checkout');
        Route::get('/quotes/{number}/confirm', [QuoteController::class, 'confirm'])->name('quotes.confirm');
    });
