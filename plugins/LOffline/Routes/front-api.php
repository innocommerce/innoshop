<?php

use Illuminate\Support\Facades\Route;
use Plugin\LOffline\Controllers\OfflineController;

Route::post('/l_offline/upload_payment_data', [
    OfflineController::class,
    'imgUpload',
])->name('l_offline.upload_payment_data');

Route::post('/l_offline', [
    OfflineController::class,
    'pay_result',
])->name('l_offline.submit');
