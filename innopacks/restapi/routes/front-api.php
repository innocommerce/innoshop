<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use InnoShop\RestAPI\FrontApiControllers;

Route::get('/home', [FrontApiControllers\HomeController::class, 'index'])->name('home.index');

Route::post('/login', [FrontApiControllers\AuthController::class, 'login'])->name('login.index');
Route::post('/register', [FrontApiControllers\AuthController::class, 'register'])->name('login.register');

Route::get('/categories', [FrontApiControllers\CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/tree', [FrontApiControllers\CategoryController::class, 'tree'])->name('categories.tree');

Route::get('/products', [FrontApiControllers\ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [FrontApiControllers\ProductController::class, 'show'])->name('products.show');

Route::get('/checkout/billing_methods', [FrontApiControllers\CheckoutController::class, 'billingMethods'])->name('checkout.billing_methods');
Route::post('/checkout/quick_confirm', [FrontApiControllers\CheckoutController::class, 'quickConfirm'])->name('checkout.quick_confirm');

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/customer', [FrontApiControllers\AuthController::class, 'customer'])->name('auth.customer');

    Route::get('/carts', [FrontApiControllers\CartController::class, 'index'])->name('carts.index');
    Route::post('/carts', [FrontApiControllers\CartController::class, 'store'])->name('carts.store');

    Route::get('/checkout', [FrontApiControllers\CheckoutController::class, 'index'])->name('checkout.index');
    Route::put('/checkout', [FrontApiControllers\CheckoutController::class, 'update'])->name('checkout.store');
    Route::post('/checkout/confirm', [FrontApiControllers\CheckoutController::class, 'confirm'])->name('checkout.confirm');

    Route::get('/favorites', [FrontApiControllers\FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites', [FrontApiControllers\FavoriteController::class, 'store'])->name('favorites.store');
    Route::post('/favorites/cancel', [FrontApiControllers\FavoriteController::class, 'cancel'])->name('favorites.cancel');

    Route::get('/addresses', [FrontApiControllers\AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [FrontApiControllers\AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}', [FrontApiControllers\AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [FrontApiControllers\AddressController::class, 'destroy'])->name('addresses.destroy');

    Route::get('/orders', [FrontApiControllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [FrontApiControllers\OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders-{number}', [FrontApiControllers\OrderController::class, 'numberShow'])->name('orders.number_show');

});
