<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\WebBuilder\PanelControllers\WebBuilderController;

Route::group(['prefix' => 'web_builder'], function () {
    // 设计器主页面
    Route::get('/', [WebBuilderController::class, 'index'])->name('web_builder.index');

    // 上传图片
    Route::post('upload/images', [WebBuilderController::class, 'uploadImages'])->name('web_builder.upload.images');

    // 获取设计数据
    Route::get('design', [WebBuilderController::class, 'getDesign'])->name('web_builder.design');

    // 保存设计数据
    Route::put('design', [WebBuilderController::class, 'saveDesign'])->name('web_builder.design.update');
});
