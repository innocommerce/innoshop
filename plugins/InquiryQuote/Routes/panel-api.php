<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\InquiryQuote\Controllers\PanelApi\HistoryController;
use Plugin\InquiryQuote\Controllers\PanelApi\InquiryController;
use Plugin\InquiryQuote\Controllers\PanelApi\QuoteController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::put('/inquiries/{inquiry}', [InquiryController::class, 'update'])->name('inquiries.update');

    Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
    Route::put('/quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
    Route::put('/quotes/{quote}/fees', [QuoteController::class, 'updateFees'])->name('quotes.update_fees');
    Route::put('/quotes/{quote}/status', [QuoteController::class, 'updateStatus'])->name('quotes.status');
    Route::delete('/quotes/{quote}', [QuoteController::class, 'destroy'])->name('quotes.destroy');

    Route::post('/quotes/{quote}/histories', [HistoryController::class, 'store'])->name('quotes.histories.store');
});
