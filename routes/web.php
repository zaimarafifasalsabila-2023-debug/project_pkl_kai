<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AngkutanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth.session')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/input-data', [DashboardController::class, 'inputData'])->name('input.data');
    Route::get('/preview-data', [DashboardController::class, 'previewData'])->name('preview.data');
    Route::get('/statistik', [DashboardController::class, 'statistik'])->name('statistik');
    
    // Existing routes
    Route::get('/angkutan', [AngkutanController::class, 'index']);
});