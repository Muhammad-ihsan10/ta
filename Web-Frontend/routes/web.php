<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root redirect
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Auth routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected dashboard routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/history',   [DashboardController::class, 'history'])->name('history');

    // AJAX API proxy endpoints
    Route::get('/sensor-data',          [DashboardController::class, 'sensorData'])->name('sensor.data');
    Route::get('/gps-history/{limit}',  [DashboardController::class, 'gpsHistory'])->name('gps.history');
    Route::get('/mpu-history/{limit}',  [DashboardController::class, 'mpuHistory'])->name('mpu.history');

    // Telegram Notification endpoints
    Route::post('/notify/fall',         [NotificationController::class, 'notifyFall'])->name('notify.fall');
    Route::get('/notify/get-chat-id',   [NotificationController::class, 'getChatId'])->name('notify.get-chat-id');
});
