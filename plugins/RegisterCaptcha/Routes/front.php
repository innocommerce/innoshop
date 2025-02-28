<?php

use Illuminate\Support\Facades\Route;
use Plugin\RegisterCaptcha\Controllers\CaptchaController;

Route::post('/register/captcha', [
    CaptchaController::class,
    'checkCaptcha',
])->name('regCheckCaptcha');
