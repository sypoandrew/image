<?php

use Illuminate\Support\Facades\Route;
use Sypo\Image\Http\Controllers\ModuleController;

Route::get('image', [ModuleController::class, 'index'])->name('admin.modules.image');
Route::post('image', [ModuleController::class, 'update'])->name('admin.modules.image');
Route::get('image/placeholder_image', [ModuleController::class, 'placeholder_image'])->name('admin.modules.image.placeholder_image');
Route::get('image/replace_default_image', [ModuleController::class, 'replace_default_image'])->name('admin.modules.image.replace_default_image');
Route::get('image/download_image_report', [ModuleController::class, 'download_image_report'])->name('admin.modules.image.download_image_report');
