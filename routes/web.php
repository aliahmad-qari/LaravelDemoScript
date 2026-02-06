<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
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

// Public Product Routes (with members-only filtering)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// Protected Routes with Auth and IP Strictness
Route::middleware(['auth', IpRestrictionMiddleware::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Cart Routes
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add/{product}', [CartController::class, 'addItem']);
    Route::delete('/cart/remove/{itemId}', [CartController::class, 'removeItem']);
});

// Admin Routes
Route::middleware(['auth', IpRestrictionMiddleware::class])->prefix('admin')->group(function () {
    // Original logs route
    Route::get('/logs', [AdminController::class, 'logs']);
    
    // Security Dashboard
    Route::get('/security/dashboard', [AdminController::class, 'securityDashboard']);
    
    // Country Restrictions
    Route::get('/security/country-restrictions', [AdminController::class, 'countryRestrictions']);
    Route::post('/security/country-restrictions', [AdminController::class, 'storeCountryRestriction']);
    Route::put('/security/country-restrictions/{restriction}', [AdminController::class, 'updateCountryRestriction']);
    Route::delete('/security/country-restrictions/{restriction}', [AdminController::class, 'deleteCountryRestriction']);
    
    // Blocked IPs
    Route::get('/security/blocked-ips', [AdminController::class, 'blockedIps']);
    Route::post('/security/blocked-ips/block', [AdminController::class, 'blockIpPermanently']);
    Route::delete('/security/blocked-ips/{blockedIp}/unblock', [AdminController::class, 'unblockIp']);
    
    // Security Alerts
    Route::get('/security/alerts', [AdminController::class, 'securityAlerts']);
    
    // Disposable Emails
    Route::get('/security/disposable-emails', [AdminController::class, 'disposableEmails']);
    Route::post('/security/disposable-emails', [AdminController::class, 'storeDisposableEmail']);
    Route::delete('/security/disposable-emails/{domain}', [AdminController::class, 'deleteDisposableEmail']);
    Route::get('/security/disposable-emails/seed', [AdminController::class, 'seedDisposableEmails']);
    
    // Products Management
    Route::get('/products', [AdminController::class, 'products']);
    Route::post('/products', [AdminController::class, 'storeProduct']);
    Route::put('/products/{product}', [AdminController::class, 'updateProduct']);
    Route::delete('/products/{product}', [AdminController::class, 'deleteProduct']);
});
