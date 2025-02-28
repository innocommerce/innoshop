<?php

use Illuminate\Support\Facades\Route;
use Plugin\LQuestionAnswer\Controllers\ShopController;

Route::get('/ask_answer', [
    ShopController::class,
    'ask_answer',
])->name('ask_answer.list');

Route::post('/ask_answer', [
    ShopController::class,
    'store',
])->name('ask_answer.add');

Route::post('/ask_answer/agree', [
    ShopController::class,
    'agree',
])->name('ask_answer.agree');
