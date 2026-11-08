<?php

use App\Http\Controllers\Store\StoreCustomerAuthController;
use App\Http\Controllers\Store\StoreWebviewController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web','tenant.identify','store.status'])->group(function () {
    Route::get('/', [StoreWebviewController::class, 'index'])->name('store.home');
    Route::get('/dashboard', [StoreWebviewController::class, 'dashboard'])->name('store.dashboard');
    Route::get('/api/search/products', [StoreWebviewController::class, 'searchProducts'])->name('store.search.products');

    // Customer Authentication & Account API routes
    Route::prefix('customer/auth')->group(function () {
        Route::post('check-phone', [StoreCustomerAuthController::class, 'checkPhone'])->name('store.customer.check_phone');
        Route::post('login', [StoreCustomerAuthController::class, 'login'])->name('store.customer.login');
        Route::post('verify-otp', [StoreCustomerAuthController::class, 'verifyOtp'])->name('store.customer.verify_otp');
        Route::post('register', [StoreCustomerAuthController::class, 'register'])->name('store.customer.register');
        Route::post('reset-password', [StoreCustomerAuthController::class, 'resetPassword'])->name('store.customer.reset_password');
        Route::post('login-with-otp', [StoreCustomerAuthController::class, 'loginWithOtp'])->name('store.customer.login_with_otp');
        Route::post('logout', [StoreCustomerAuthController::class, 'logout'])->name('store.customer.logout');
    });

    Route::prefix('customer')->group(function () {
        // Profile & Security & Avatar
        Route::put('profile', [StoreCustomerAuthController::class, 'updateProfile'])->name('store.customer.profile.update');
        Route::post('avatar', [StoreCustomerAuthController::class, 'updateAvatar'])->name('store.customer.avatar.update');
        Route::put('password', [StoreCustomerAuthController::class, 'updatePassword'])->name('store.customer.password.update');

        // Address Management (customer_addresses table)
        Route::post('addresses', [StoreCustomerAuthController::class, 'storeAddress'])->name('store.customer.addresses.store');
        Route::post('addresses/{id}/default', [StoreCustomerAuthController::class, 'setDefaultAddress'])->name('store.customer.addresses.default');
        Route::delete('addresses/{id}', [StoreCustomerAuthController::class, 'deleteAddress'])->name('store.customer.addresses.delete');

        // Prescription Management (tenant_prescriptions table)
        Route::post('prescriptions', [StoreCustomerAuthController::class, 'uploadPrescription'])->name('store.customer.prescriptions.upload');

        // Wishlist Management (wishlists table)
        Route::delete('wishlist/{id}', [StoreCustomerAuthController::class, 'removeWishlist'])->name('store.customer.wishlist.remove');
    });
});
