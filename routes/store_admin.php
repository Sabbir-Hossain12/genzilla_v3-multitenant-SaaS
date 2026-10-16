<?php

use App\Http\Controllers\Platform\Admin\AdminPermissionController;
use App\Http\Controllers\Platform\Admin\AdminRoleController;
use App\Http\Controllers\Platform\Admin\AuthController;
use App\Http\Controllers\Platform\Admin\DashboardController;
use App\Http\Controllers\Platform\Admin\PlatformAdminController;
use App\Http\Controllers\Platform\Admin\PlatformBlogCategoryController;
use App\Http\Controllers\Platform\Admin\PlatformBlogPostController;
use App\Http\Controllers\Platform\Admin\PlatformContactController;
use App\Http\Controllers\Platform\Admin\PlatformFaqController;
use App\Http\Controllers\Platform\Admin\PlatformFeatureController;
use App\Http\Controllers\Platform\Admin\PlatformHeroController;
use App\Http\Controllers\Platform\Admin\PlatformMatrixController;
use App\Http\Controllers\Platform\Admin\PlatformNewsletterSubscriberController;
use App\Http\Controllers\Platform\Admin\PlatformPageController;
use App\Http\Controllers\Platform\Admin\PlatformPlanController;
use App\Http\Controllers\Platform\Admin\PlatformProfileController;
use App\Http\Controllers\Platform\Admin\PlatformSettingController;
use App\Http\Controllers\Platform\Admin\PlatformStepController;
use App\Http\Controllers\Platform\Admin\PlatformStoreDemoController;
use App\Http\Controllers\Platform\Admin\PlatformTestimonialController;
use App\Http\Controllers\Platform\Admin\PlatformUseCaseController;
use App\Http\Controllers\Platform\Admin\TenantDomainController;
use App\Http\Controllers\Platform\Admin\TenantInvoiceController;
use App\Http\Controllers\Platform\Admin\TenantSubscriptionController;
use App\Http\Controllers\Platform\Admin\TenantUsageController;
use App\Http\Controllers\Store\Admin\StoreAdminAuthController;

Route::domain(config('app.base_domain'))->prefix('store')->name('store.')->group(function () {
    Route::get('login', [StoreAdminAuthController::class, 'loginPage'])->name('admin.login');
    Route::post('login', [StoreAdminAuthController::class, 'login'])->name('admin.login.store');

    Route::get('/register', [StoreAdminAuthController::class, 'registerPage'])->name('admin.register');
    Route::post('/register', [StoreAdminAuthController::class, 'register'])->name('admin.register.store');

    // Admin Panel Protected Routes
    Route::middleware(['auth', 'permission:Platform Admin Dashboard'])->group(function () {
        Route::post('logout', [StoreAdminAuthController::class, 'logout'])->name('admin.logout');

        // Dashboard
        Route::resource('/dashboard', DashboardController::class)->names('admin.dashboard');

        // Profile
        Route::get('/profile', [PlatformProfileController::class, 'edit'])->name('admin.profile.edit');
        Route::put('/profile', [PlatformProfileController::class, 'update'])->name('admin.profile.update');
        Route::put('/profile/password', [PlatformProfileController::class, 'updatePassword'])->name('admin.profile.password');

        // Admins
        Route::get('/admins', [PlatformAdminController::class, 'index'])->name('admin.admins.index')->middleware('permission:Admin List');
        Route::post('/admins', [PlatformAdminController::class, 'store'])->name('admin.admins.store')->middleware('permission:Create Admin');
        Route::get('/admins/{admin}/edit', [PlatformAdminController::class, 'edit'])->name('admin.admins.edit')->middleware('permission:Edit Admin');
        Route::put('/admins/{admin}', [PlatformAdminController::class, 'update'])->name('admin.admins.update')->middleware('permission:Edit Admin');
        Route::delete('/admins/{admin}', [PlatformAdminController::class, 'destroy'])->name('admin.admins.destroy')->middleware('permission:Delete Admin');
        Route::post('/change-admin-status', [PlatformAdminController::class, 'changeAdminStatus'])->name('admin.status')->middleware('permission:Status Admin');

        // Roles
        Route::get('/roles', [AdminRoleController::class, 'index'])->name('admin.role.index')->middleware('permission:Role List');
        Route::post('/roles', [AdminRoleController::class, 'store'])->name('admin.role.store')->middleware('permission:Role Create');
        Route::put('/roles/{role}', [AdminRoleController::class, 'update'])->name('admin.role.update')->middleware('permission:Edit Role');
        Route::delete('/roles/{role}', [AdminRoleController::class, 'destroy'])->name('admin.role.destroy')->middleware('permission:Delete Role');
        Route::get('/roles/{role}/permissions', [AdminRoleController::class, 'assignPermissionsToRolePage'])->name('admin.role.permission.edit')->middleware('permission:Assign Permission');
        Route::put('/roles/{role}/permissions', [AdminRoleController::class, 'assignPermissionsToRole'])->name('admin.role.permission.update')->middleware('permission:Assign Permission');

        // Permissions
        Route::get('/permissions', [AdminPermissionController::class, 'index'])->name('admin.permission.index')->middleware('permission:Permission List');
        Route::post('/permissions', [AdminPermissionController::class, 'store'])->name('admin.permission.store')->middleware('permission:Create Permission');
        Route::delete('/permissions/bulk', [AdminPermissionController::class, 'bulkDestroy'])->name('admin.permission.bulk-destroy')->middleware('permission:Delete Permission');
        Route::put('/permissions/{permission}', [AdminPermissionController::class, 'update'])->name('admin.permission.update')->middleware('permission:Edit Permission');
        Route::delete('/permissions/{permission}', [AdminPermissionController::class, 'destroy'])->name('admin.permission.destroy')->middleware('permission:Delete Permission');

        // Platform Settings
        Route::get('/settings', [PlatformSettingController::class, 'index'])->name('admin.settings.index')->middleware('permission:General Setting');
        Route::put('/settings', [PlatformSettingController::class, 'update'])->name('admin.settings.update')->middleware('permission:Edit Setting');

    });
});
