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
Route::get('/settings', [FrontApiControllers\SettingController::class, 'index'])->name('settings.index');

Route::post('/login', [FrontApiControllers\AuthController::class, 'login'])->name('login.index');
Route::post('/register', [FrontApiControllers\AuthController::class, 'register'])->name('login.register');

Route::post('/upload/images', [FrontApiControllers\UploadController::class, 'images'])->name('upload.images');
Route::post('/upload/files', [FrontApiControllers\UploadController::class, 'files'])->name('upload.files');

Route::post('/miniapp', [FrontApiControllers\MiniappController::class, 'index'])->name('miniapp.index');

Route::get('/brands', [FrontApiControllers\BrandController::class, 'index'])->name('brands.index');
Route::get('/brands/group', [FrontApiControllers\BrandController::class, 'group'])->name('brands.group');

Route::get('/categories', [FrontApiControllers\CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/tree', [FrontApiControllers\CategoryController::class, 'tree'])->name('categories.tree');

Route::get('/products', [FrontApiControllers\ProductController::class, 'index'])->name('products.index');
Route::get('/products/filters', [FrontApiControllers\ProductController::class, 'filters'])->name('products.filters');
Route::get('/products/{product}', [FrontApiControllers\ProductController::class, 'show'])->name('products.show');
Route::get('/products/{product}/reviews', [FrontApiControllers\ProductController::class, 'productReviews'])->name('products.reviews');

Route::get('/countries', [FrontApiControllers\CountryController::class, 'index'])->name('countries.index');
Route::get('/countries/{country}/states', [FrontApiControllers\CountryController::class, 'states'])->name('countries.states');
Route::get('/states', [FrontApiControllers\StateController::class, 'index'])->name('states.index');

Route::get('/checkout/billing_methods', [FrontApiControllers\CheckoutController::class, 'billingMethods'])->name('checkout.billing_methods');
Route::post('/checkout/quick_confirm', [FrontApiControllers\CheckoutController::class, 'quickConfirm'])->name('checkout.quick_confirm');

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/account/me', [FrontApiControllers\AccountController::class, 'me'])->name('account.me');
    Route::put('/account/profile', [FrontApiControllers\AccountController::class, 'updateProfile'])->name('account.update_profile');
    Route::put('/account/password', [FrontApiControllers\AccountController::class, 'updatePassword'])->name('account.update_password');
    Route::post('/account/password', [FrontApiControllers\AccountController::class, 'setPassword'])->name('account.set_password');

    Route::get('/carts', [FrontApiControllers\CartController::class, 'index'])->name('carts.index');
    Route::post('/carts', [FrontApiControllers\CartController::class, 'store'])->name('carts.store');
    Route::put('/carts/{cart}', [FrontApiControllers\CartController::class, 'update'])->name('carts.update');
    Route::delete('/carts/{cart}', [FrontApiControllers\CartController::class, 'destroy'])->name('carts.destroy');
    Route::post('/carts/select', [FrontApiControllers\CartController::class, 'select'])->name('carts.select');
    Route::post('/carts/unselect', [FrontApiControllers\CartController::class, 'unselect'])->name('carts.unselect');
    Route::post('/carts/select_all', [FrontApiControllers\CartController::class, 'selectAll'])->name('carts.select_all');
    Route::post('/carts/unselect_all', [FrontApiControllers\CartController::class, 'unselectAll'])->name('carts.unselect_all');

    Route::get('/checkout', [FrontApiControllers\CheckoutController::class, 'index'])->name('checkout.index');
    Route::put('/checkout', [FrontApiControllers\CheckoutController::class, 'update'])->name('checkout.store');
    Route::post('/checkout/confirm', [FrontApiControllers\CheckoutController::class, 'confirm'])->name('checkout.confirm');

    Route::get('/favorites', [FrontApiControllers\FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites', [FrontApiControllers\FavoriteController::class, 'store'])->name('favorites.store');
    Route::post('/favorites/cancel', [FrontApiControllers\FavoriteController::class, 'cancel'])->name('favorites.cancel');

    Route::get('/reviews', [FrontApiControllers\ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews', [FrontApiControllers\ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [FrontApiControllers\ReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('/addresses', [FrontApiControllers\AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [FrontApiControllers\AddressController::class, 'store'])->name('addresses.store');
    Route::get('/addresses/{address}', [FrontApiControllers\AddressController::class, 'show'])->name('addresses.show');
    Route::put('/addresses/{address}', [FrontApiControllers\AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [FrontApiControllers\AddressController::class, 'destroy'])->name('addresses.destroy');

    Route::get('/orders', [FrontApiControllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{number}', [FrontApiControllers\OrderController::class, 'numberShow'])->name('orders.number_show');
    Route::post('/orders/{number}/pay', [FrontApiControllers\OrderController::class, 'pay'])->name('orders.pay');
    Route::post('/orders/{number}/cancel', [FrontApiControllers\OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{number}/complete', [FrontApiControllers\OrderController::class, 'complete'])->name('orders.complete');
    Route::post('/orders/{number}/reorder', [FrontApiControllers\OrderController::class, 'reorder'])->name('orders.reorder');

});
