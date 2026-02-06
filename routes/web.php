<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\IpRestrictionMiddleware;

Route::get('/', function () {
    return redirect('/login');
});

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Registration Routes
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes with Auth and IP Strictness
Route::middleware(['auth', IpRestrictionMiddleware::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

// Admin Routes
Route::middleware(['auth', IpRestrictionMiddleware::class])->prefix('admin')->group(function () {
    Route::get('/logs', [AdminController::class, 'logs']);
});