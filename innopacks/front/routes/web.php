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
use InnoShop\Front\Controllers;
use InnoShop\Front\Controllers\Account;

Route::get('/', [Controllers\HomeController::class, 'index'])->name('home.index');

Route::get('/locales/switch/{code}', [Controllers\LocaleController::class, 'switch'])->name('locales.switch');
Route::get('/currencies/switch/{code}', [Controllers\CurrencyController::class, 'switch'])->name('currencies.switch');

// Category
Route::get('/categories', [Controllers\CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category}', [Controllers\CategoryController::class, 'show'])->name('categories.show');
Route::get('/category-{slug}', [Controllers\CategoryController::class, 'slugShow'])->name('categories.slug_show');

// Product
Route::get('/products', [Controllers\ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [Controllers\ProductController::class, 'show'])->name('products.show');
Route::get('/product-{slug}', [Controllers\ProductController::class, 'slugShow'])->name('products.slug_show');

// Brands
Route::get('/brands', [Controllers\BrandController::class, 'index'])->name('brands.index');
Route::get('/brands/{brand}', [Controllers\BrandController::class, 'show'])->name('brands.show');
Route::get('/brand-{slug}', [Controllers\BrandController::class, 'slugShow'])->name('brands.slug_show');

// Cart
Route::get('/cart', [Controllers\CartController::class, 'index'])->name('carts.index');
Route::get('/cart/mini', [Controllers\CartController::class, 'mini'])->name('carts.mini');
Route::post('/carts', [Controllers\CartController::class, 'store'])->name('carts.store');
Route::post('/carts/select', [Controllers\CartController::class, 'select'])->name('carts.select');
Route::post('/carts/unselect', [Controllers\CartController::class, 'unselect'])->name('carts.unselect');
Route::put('/carts/{cart}', [Controllers\CartController::class, 'update'])->name('carts.update');
Route::delete('/carts/{cart}', [Controllers\CartController::class, 'destroy'])->name('carts.destroy');

// Checkout
Route::get('/checkout', [Controllers\CheckoutController::class, 'index'])->name('checkout.index');
Route::put('/checkout', [Controllers\CheckoutController::class, 'update'])->name('checkout.update');
Route::post('/checkout/confirm', [Controllers\CheckoutController::class, 'confirm'])->name('checkout.confirm');
Route::get('/checkout/success', [Controllers\CheckoutController::class, 'success'])->name('checkout.success');

// Orders
Route::get('/orders/{number}/pay', [Controllers\OrderController::class, 'pay'])->name('orders.pay');
Route::get('/orders/{number}', [Controllers\OrderController::class, 'numberShow'])->name('orders.number_show');

// Guest Address
Route::post('/addresses', [Controllers\AddressesController::class, 'store'])->name('addresses.store');
Route::put('/addresses/{address}', [Controllers\AddressesController::class, 'update'])->name('addresses.update');
Route::delete('/addresses/{address}', [Controllers\AddressesController::class, 'destroy'])->name('addresses.destroy');

// Countries and States
Route::get('/countries', [Controllers\CountryController::class, 'index'])->name('countries.index');
Route::get('/countries/{country}', [Controllers\CountryController::class, 'show'])->name('countries.show');

// Catalogs
Route::get('/catalogs', [Controllers\CatalogController::class, 'index'])->name('catalogs.index');
Route::get('/catalogs/{catalog}', [Controllers\CatalogController::class, 'show'])->name('catalogs.show');
Route::get('/catalog-{slug}', [Controllers\CatalogController::class, 'slugShow'])->name('catalogs.slug_show');

// Articles
Route::get('/articles', [Controllers\ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{article}', [Controllers\ArticleController::class, 'show'])->name('articles.show');
Route::get('/article-{slug}', [Controllers\ArticleController::class, 'slugShow'])->name('articles.slug_show');

// Tags
Route::get('/tags', [Controllers\TagController::class, 'index'])->name('tags.index');
Route::get('/tags/{tag}', [Controllers\TagController::class, 'show'])->name('tags.show');
Route::get('/tag-{slug}', [Controllers\TagController::class, 'slugShow'])->name('tags.slug_show');

// Upload
Route::post('/upload/images', [Controllers\UploadController::class, 'images'])->name('upload.images');
Route::post('/upload/files', [Controllers\UploadController::class, 'files'])->name('upload.files');

// Pages, like product, service, about
if (installed()) {
    $pages = PageRepo::getInstance()->withActive()->builder()->get();
    foreach ($pages as $page) {
        Route::get($page->slug, [Controllers\PageController::class, 'show'])->name('pages.'.$page->slug);
    }
}

Route::get('/login', [Account\LoginController::class, 'index'])->name('login.index');
Route::post('/login', [Account\LoginController::class, 'store'])->name('login.store');
Route::get('/register', [Account\RegisterController::class, 'index'])->name('register.index');
Route::post('/register', [Account\RegisterController::class, 'store'])->name('register.store');

Route::get('/forgotten', [Account\ForgottenController::class, 'index'])->name('forgotten.index');
Route::post('/forgotten/verify_code', [Account\ForgottenController::class, 'sendVerifyCode'])->name('forgotten.verify_code');
Route::post('/forgotten/password', [Account\ForgottenController::class, 'changePassword'])->name('forgotten.password');

Route::prefix('account')
    ->name('account.')
    ->middleware('customer_auth:customer')
    ->group(function () {
        Route::get('/', [Account\AccountController::class, 'index'])->name('index');

        // Orders
        Route::get('/orders', [Account\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [Account\OrderController::class, 'show'])->name('orders.show');
        Route::get('/order-{number}', [Account\OrderController::class, 'numberShow'])->name('orders.number_show');

        // Return Orders
        Route::get('/order_returns', [Account\OrderReturnController::class, 'index'])->name('order_returns.index');
        Route::get('/order_returns/create', [Account\OrderReturnController::class, 'create'])->name('order_returns.create');
        Route::post('/order_returns', [Account\OrderReturnController::class, 'store'])->name('order_returns.store');
        Route::get('/order_returns/{order_return}', [Account\OrderReturnController::class, 'show'])->name('order_returns.show');

        // Favorites
        Route::get('/favorites', [Account\FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('/favorites', [Account\FavoriteController::class, 'store'])->name('favorites.store');
        Route::post('/favorites/cancel', [Account\FavoriteController::class, 'cancel'])->name('favorites.cancel');

        // Addresses
        Route::get('/addresses', [Account\AddressesController::class, 'index'])->name('addresses.index');
        Route::post('/addresses', [Account\AddressesController::class, 'store'])->name('addresses.store');
        Route::put('/addresses/{address}', [Account\AddressesController::class, 'update'])->name('addresses.update');
        Route::delete('/addresses/{address}', [Account\AddressesController::class, 'destroy'])->name('addresses.destroy');

        Route::get('/edit', [Account\EditController::class, 'index'])->name('edit.index');
        Route::put('/edit', [Account\EditController::class, 'update'])->name('edit.update');

        Route::get('/password', [Account\PasswordController::class, 'index'])->name('password.index');
        Route::put('/password', [Account\PasswordController::class, 'update'])->name('password.update');

        Route::get('/logout', [Account\LogoutController::class, 'index'])->name('logout');
    });
