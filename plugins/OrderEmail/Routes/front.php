<?php

use Illuminate\Support\Facades\Route;
use Plugin\OrderEmail\Controllers\EmailController;

Route::get('/order_email/send/{order_id}/status/{status_str}/token/fffeewwqqqe', [
    EmailController::class,
    'send',
])->name('order_email.send');
