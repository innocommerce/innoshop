<?php

use Illuminate\Support\Facades\Route;
use Plugin\LQuestionAnswer\Controllers\AdminController;

Route::middleware('can:ask_answer')->get('/ask_answer', [
    AdminController::class,
    'index',
])->name('ask_answer');

Route::middleware('can:ask_answer')->get('/ask_answer/list', [
    AdminController::class,
    'ask_answer',
])->name('ask_answer.list');

Route::middleware('can:ask_answer_update')->post('/ask_answer/add', [
    AdminController::class,
    'store',
])->name('ask_answer.store');

Route::middleware('can:ask_answer_update')->put('/ask_answer/update', [
    AdminController::class,
    'store',
])->name('ask_answer.update');

Route::middleware('can:ask_answer_update')->put('/ask_answer/status', [
    AdminController::class,
    'updateStatus',
])->name('ask_answer.status');
