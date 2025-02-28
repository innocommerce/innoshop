<?php

Route::get('/auto_currency_rate/task/{token}', [
    \Plugin\AutoCurrencyRate\Controllers\AutoCurrencyRateController::class,
    'task',
])->name('auto_currency_rate.task');
