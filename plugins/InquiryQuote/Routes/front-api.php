<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\InquiryQuote\FrontApiControllers\HistoryController;
use Plugin\InquiryQuote\FrontApiControllers\InquiryController;
use Plugin\InquiryQuote\FrontApiControllers\QuoteController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/inquiries', [InquiryController::class, 'store'])->name('inquiries.store');
    Route::put('/inquiries/{inquiry}', [InquiryController::class, 'update'])->name('inquiries.update');
    Route::delete('/inquiries/{inquiry}', [InquiryController::class, 'destroy'])->name('inquiries.destroy');

    Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/latest', [QuoteController::class, 'latest'])->name('quotes.latest');
    Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
    Route::put('/quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
    Route::put('/quotes/{quote}/fees', [QuoteController::class, 'updateFees'])->name('quotes.update_fees');
    Route::put('/quotes/{quote}/status', [QuoteController::class, 'updateStatus'])->name('quotes.status');
    Route::delete('/quotes/{quote}', [QuoteController::class, 'destroy'])->name('quotes.destroy');

    Route::post('/quotes/{quote}/histories', [HistoryController::class, 'store'])->name('quotes.histories.store');

    Route::post('/quotes/{quote}/add_cart', [QuoteController::class, 'addCart'])->name('quotes.add_cart');
});
