<?php


use App\Http\Controllers\Store\Admin\StoreAdminAuthController;
use App\Http\Controllers\Store\Admin\StoreAdminDashboardController;
use App\Http\Controllers\Store\Admin\StoreAdminRoleController;

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
        Route::get('/dashboard', [StoreAdminDashboardController::class,'index'])->name('admin.dashboard')->middleware('permission:Store Dashboard');

        Route::get('/roles', [StoreAdminRoleController::class, 'index'])->name('admin.role.index')->middleware('permission:Manage Store Roles');
        Route::post('/roles', [StoreAdminRoleController::class, 'store'])->name('admin.role.store')->middleware('permission:Manage Store Roles');
        Route::put('/roles/{role}', [StoreAdminRoleController::class, 'update'])->name('admin.role.update')->middleware('permission:Manage Store Roles');
        Route::delete('/roles/{role}', [StoreAdminRoleController::class, 'destroy'])->name('admin.role.destroy')->middleware('permission:Manage Store Roles');
        Route::get('/roles/{role}/permissions', [StoreAdminRoleController::class, 'editPermissions'])->name('admin.role.permission.edit')->middleware('permission:Manage Store Roles');
        Route::put('/roles/{role}/permissions', [StoreAdminRoleController::class, 'updatePermissions'])->name('admin.role.permission.update')->middleware('permission:Manage Store Roles');
    });
});
