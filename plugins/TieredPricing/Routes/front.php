<?php

use Illuminate\Support\Facades\Route;

Route::get('/tiered_pricing/total_price', [\Plugin\TieredPricing\Controllers\TieredPricingController::class, 'getTotalPrice'])->name('tiered_pricing.total_price');
Route::get('/tiered_pricing/min_quantity', [\Plugin\TieredPricing\Controllers\TieredPricingController::class, 'getMinQuantity'])->name('tiered_pricing.min_quantity');
