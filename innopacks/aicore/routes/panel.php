<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use InnoShop\Aicore\Http\Controllers\Panel\ChatController;
use InnoShop\Aicore\Http\Controllers\Panel\ContentAIController;

Route::post('/content-ai/generate', [ContentAIController::class, 'generate'])->name('content_ai.generate');
Route::post('/content-ai/generate-batch', [ContentAIController::class, 'generateBatch'])->name('content_ai.generate_batch');
Route::post('/content-ai/list-models', [ContentAIController::class, 'listModels'])->name('content_ai.list_models');
Route::post('/content-ai/chat', [ChatController::class, 'chat'])->name('content_ai.chat');
