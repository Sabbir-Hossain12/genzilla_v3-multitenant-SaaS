<?php

use App\Http\Controllers\Store\StoreCustomerAuthController;
use App\Http\Controllers\Store\StoreWebviewController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web','tenant.identify','store.status'])->group(function () {
    Route::get('/', [StoreWebviewController::class, 'index'])->name('store.home');
    Route::get('/dashboard', [StoreWebviewController::class, 'dashboard'])->name('store.dashboard');

    // Customer Authentication & Account API routes
    Route::prefix('customer/auth')->group(function () {
        Route::post('check-phone', [StoreCustomerAuthController::class, 'checkPhone'])->name('store.customer.check_phone');
        Route::post('login', [StoreCustomerAuthController::class, 'login'])->name('store.customer.login');
        Route::post('verify-otp', [StoreCustomerAuthController::class, 'verifyOtp'])->name('store.customer.verify_otp');
        Route::post('register', [StoreCustomerAuthController::class, 'register'])->name('store.customer.register');
        Route::post('logout', [StoreCustomerAuthController::class, 'logout'])->name('store.customer.logout');
    });

    Route::prefix('customer')->middleware('auth:customer')->group(function () {
        Route::put('profile', [StoreCustomerAuthController::class, 'updateProfile'])->name('store.customer.profile.update');
        Route::put('password', [StoreCustomerAuthController::class, 'updatePassword'])->name('store.customer.password.update');
    });
});
