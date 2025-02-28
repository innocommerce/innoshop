<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'other_sku'], function () {
    Route::post('/', [\Plugin\ProductVariable\Controllers\SkuController::class, 'store'])->name('other_sku.store');
});
