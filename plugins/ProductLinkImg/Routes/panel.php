<?php

use Illuminate\Support\Facades\Route;

Route::get('/product_link_img/index', [
    \Plugin\ProductLinkImg\Controllers\ProductLinkImgController::class,
    'index',
])->name('product_link_img.index');
