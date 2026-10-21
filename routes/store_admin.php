<?php


use App\Http\Controllers\Platform\Admin\PlatformSettingController;
use App\Http\Controllers\Store\Admin\StoreAdminAuthController;
use App\Http\Controllers\Store\Admin\StoreAdminDashboardController;

Route::domain(config('app.base_domain'))
    ->prefix('store')->name('store.')
    ->middleware('web')
    ->group(function () {
    Route::get('/login', [StoreAdminAuthController::class, 'loginPage'])->name('admin.login');
    Route::post('/login', [StoreAdminAuthController::class, 'login'])->name('admin.login.store');

    Route::get('/register', [StoreAdminAuthController::class, 'registerPage'])->name('admin.register');
    Route::post('/register', [StoreAdminAuthController::class, 'register'])->name('admin.register.store');

    // Admin Panel Protected Routes
    Route::middleware(['auth.tenant'])->group(function () {
        Route::post('logout', [StoreAdminAuthController::class, 'logout'])->name('admin.logout');

        // Dashboard
        Route::get('/dashboard', [StoreAdminDashboardController::class,'index'])->name('admin.dashboard');


        // Platform Settings
        Route::get('/settings', [PlatformSettingController::class, 'index'])->name('admin.settings.index')->middleware('permission:General Setting');
        Route::put('/settings', [PlatformSettingController::class, 'update'])->name('admin.settings.update')->middleware('permission:Edit Setting');

    });
});
