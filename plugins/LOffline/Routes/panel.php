<?php

use Illuminate\Support\Facades\Route;

Route::get('/l_offline/config/index', [
    \Plugin\LOffline\Controllers\AdminOfflineController::class,
    'index',
])->name('l_offline.save_config.index');

Route::post('/l_offline/config', [
    \Plugin\LOffline\Controllers\AdminOfflineController::class,
    'save_config',
])->name('l_offline.save_config');

Route::get('/l_offline/pay_certificate', [
    \Plugin\LOffline\Controllers\AdminOfflineController::class,
    'pay_certificate',
])->name('l_offline.pay_certificate');
