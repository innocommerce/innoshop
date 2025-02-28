<?php

use Illuminate\Support\Facades\Route;
use Plugin\SmsBao\Controllers\SmsBaoController;

Route::post('/smsbao/sms', [
    SmsBaoController::class,
    'postSmsCode',
])->name('postSmsCode');

Route::post('/smsbao/login', [
    SmsBaoController::class,
    'loginBySms',
])->name('loginBySms');

Route::post('/smsbao/phone/login', [
    SmsBaoController::class,
    'loginPhoneByPwd',
])->name('loginBySms.phone_login');

Route::post('/smsbao/register', [
    SmsBaoController::class,
    'register',
])->name('loginBySms.register');

Route::get('/smsbao/forgotten', [
    SmsBaoController::class,
    'forgotten',
])->name('loginBySms.forgotten');

Route::post('/smsbao/forgotten/update', [
    SmsBaoController::class,
    'forgotten_update',
])->name('loginBySms.forgotten_update');
