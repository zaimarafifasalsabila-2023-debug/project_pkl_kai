<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AngkutanController;

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| AUTH (LOGIN & LOGOUT)
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLoginForm'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (HARUS LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware('auth.session')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/input-data', [DashboardController::class, 'inputData'])
        ->name('input.data');
    
    Route::post('/upload/kedatangan', [DashboardController::class, 'uploadKedatangan'])
        ->name('upload.kedatangan');
    
    Route::post('/upload/muat', [DashboardController::class, 'uploadMuat'])
        ->name('upload.muat');
    
    Route::post('/preview/upload', [DashboardController::class, 'previewUpload'])
        ->name('preview.upload')
        ->withoutMiddleware('auth.session');

    Route::get('/preview-data', [DashboardController::class, 'previewData'])
        ->name('preview.data');

    Route::get('/statistik', [DashboardController::class, 'statistik'])
        ->name('statistik');

    Route::get('/angkutan', [AngkutanController::class, 'index'])
        ->name('angkutan.index');
});