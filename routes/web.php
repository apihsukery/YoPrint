<?php

use App\Http\Controllers\FileUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FileUploadController::class, 'index']);
Route::post('/upload', [FileUploadController::class, 'upload'])->name('upload');
Route::get('/upload/status/{id}', [FileUploadController::class, 'status'])->name('upload.status');
