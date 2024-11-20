<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use InnoShop\Enterprise\PanelControllers\FileManagerController;

Route::get('file_manager/files', [FileManagerController::class, 'getFiles'])->name('file_manager.get_files');
Route::get('file_manager/directories', [FileManagerController::class, 'getDirectories'])->name('file_manager.get_directories');
Route::post('file_manager/directories', [FileManagerController::class, 'createDirectory'])->name('file_manager.create_directory');
Route::post('file_manager/upload', [FileManagerController::class, 'uploadFiles'])->name('file_manager.upload');
Route::post('file_manager/rename', [FileManagerController::class, 'rename'])->name('file_manager.rename');
Route::delete('file_manager/files', [FileManagerController::class, 'destroyFiles'])->name('file_manager.delete_files');
Route::delete('file_manager/directories', [FileManagerController::class, 'destroyDirectories'])->name('file_manager.delete_directories');
Route::post('file_manager/move_directories', [FileManagerController::class, 'moveDirectories'])->name('file_manager.move_directories');
Route::post('file_manager/move_files', [FileManagerController::class, 'moveFiles'])->name('file_manager.move_files');
Route::get('file_manager/export', [FileManagerController::class, 'exportZip'])->name('file_manager.export');
Route::post('file_manager/copy_files', [FileManagerController::class, 'copyFiles'])->name('file_manager.copy_files');
