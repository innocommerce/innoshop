<?php

use Illuminate\Support\Facades\Route;
use Plugin\SmsBao\Controllers\SmsBaoApiController;

Route::post('/smsbao/sms', [
    SmsBaoApiController::class,
    'postSmsCode',
])->name('postSmsCode');

Route::post('/smsbao/login', [
    SmsBaoApiController::class,
    'loginBySms',
])->name('loginBySms');

Route::post('/smsbao/phone/login', [
    SmsBaoApiController::class,
    'loginPhoneByPwd',
])->name('loginBySms.phone_login');

Route::post('/smsbao/register', [
    SmsBaoApiController::class,
    'register',
])->name('loginBySms.register');

Route::get('/smsbao/forgotten', [
    SmsBaoApiController::class,
    'forgotten',
])->name('loginBySms.forgotten');

Route::post('/smsbao/forgotten/update', [
    SmsBaoApiController::class,
    'forgotten_update',
])->name('loginBySms.forgotten_update');
