<?php

//Admin Auth Routes
use App\Http\Controllers\Platform\Admin\AuthController;
use App\Http\Controllers\Platform\Admin\DashboardController;

Route::domain(config('app.base_domain'))->prefix('admin')->group(function () {
    Route::get('login', [AuthController::class, 'loginPage'])
        ->name('admin.login');

    Route::post('login', [AuthController::class, 'login'])->name('admin.login.store');

//______ Admin Panel Starts _____//

        Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');

//______ Dashboard _____//
        Route::resource('/dashboard', DashboardController::class)->names('admin.dashboard');
//
////______ Admins _____//
//    Route::resource('/admins', AdminController::class)->names('admin.admins');
//    Route::post('/change-admin-status', [AdminController::class, 'changeAdminStatus'])->name('admin.status');
//    Route::get('/data', [AdminController::class, 'getData'])->name('admin.data');
//
////______ Role and Permission _____//
//    Route::resource('/roles', AdminRoleController::class)->names('admin.role');
//    Route::resource('/permissions', AdminPermissionController::class)->names('admin.permission');
//    Route::get('/roles-data', [AdminRoleController::class, 'getData'])->name('admin.role.data');
//    Route::get('/permissions-data', [AdminPermissionController::class, 'getData'])->name('admin.permission.data');
//
//    Route::get('/assign-permission-page/{id}', [AdminRoleController::class, 'assignPermissionsToRolePage'])->name('role.permission.edit');
//    Route::put('role/{id}/permission/update', [AdminRoleController::class, 'assignPermissionsToRole'])->name('role.permission.update');

});
