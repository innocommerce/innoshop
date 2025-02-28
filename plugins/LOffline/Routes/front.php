<?php

use Illuminate\Support\Facades\Route;

Route::post('/l_offline/upload_payment_data', [
    \Plugin\LOffline\Controllers\OfflineController::class,
    'imgUpload',
])->name('l_offline.upload_payment_data');

Route::post('/l_offline', [
    \Plugin\LOffline\Controllers\OfflineController::class,
    'pay_result',
])->name('l_offline.submit');
