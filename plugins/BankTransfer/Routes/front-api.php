<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\BankTransfer\Controllers\ReceiptController;

// Bank transfer receipt upload endpoint
Route::post('/orders/{number}/receipt', [ReceiptController::class, 'upload'])->name('orders.receipt_upload');
