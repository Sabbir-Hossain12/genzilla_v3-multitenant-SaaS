<?php

use App\Http\Controllers\Store\Admin\Catalog\StoreAttributeController;
use App\Http\Controllers\Store\Admin\Catalog\StoreBrandController;
use App\Http\Controllers\Store\Admin\Catalog\StoreCategoryController;
use App\Http\Controllers\Store\Admin\Catalog\StoreInventoryController;
use App\Http\Controllers\Store\Admin\Catalog\StoreProductController;
use App\Http\Controllers\Store\Admin\StoreAdminAuthController;
use App\Http\Controllers\Store\Admin\StoreAdminDashboardController;
use App\Http\Controllers\Store\Admin\StoreAdminAdminController;
use App\Http\Controllers\Store\Admin\StoreAdminPermissionController;
use App\Http\Controllers\Store\Admin\StoreAdminSettingController;
use App\Http\Controllers\Store\Admin\StoreAdminRoleController;
use App\Http\Controllers\Store\Admin\StoreCatalogController;

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

            // Roles Management (Granular Permissions)
            Route::get('/roles', [StoreAdminRoleController::class, 'index'])->name('admin.role.index')->middleware('permission:Store Role List');
            Route::post('/roles', [StoreAdminRoleController::class, 'store'])->name('admin.role.store')->middleware('permission:Store Role Create');
            Route::put('/roles/{role}', [StoreAdminRoleController::class, 'update'])->name('admin.role.update')->middleware('permission:Store Edit Role');
            Route::delete('/roles/{role}', [StoreAdminRoleController::class, 'destroy'])->name('admin.role.destroy')->middleware('permission:Store Delete Role');

            // Role Permission Assignment
            Route::get('/roles/{role}/permissions', [StoreAdminRoleController::class, 'editPermissions'])->name('admin.role.permission.edit')->middleware('permission:Store Assign Permission');
            Route::put('/roles/{role}/permissions', [StoreAdminRoleController::class, 'updatePermissions'])->name('admin.role.permission.update')->middleware('permission:Store Assign Permission');

            // Store staff accounts
            Route::get('/admins', [StoreAdminAdminController::class, 'index'])->name('admin.admins.index')->middleware('permission:Store Admin List');
            Route::post('/admins', [StoreAdminAdminController::class, 'store'])->name('admin.admins.store')->middleware('permission:Store Create Admin');
            Route::put('/admins/{admin}', [StoreAdminAdminController::class, 'update'])->name('admin.admins.update')->middleware('permission:Store Edit Admin');
            Route::delete('/admins/{admin}', [StoreAdminAdminController::class, 'destroy'])->name('admin.admins.destroy')->middleware('permission:Store Delete Admin');
            Route::patch('/admins/{admin}/status', [StoreAdminAdminController::class, 'changeStatus'])->name('admin.admins.status')->middleware('permission:Store Status Admin');

            // Read-only list of store-panel permissions
            Route::get('/permissions', [StoreAdminPermissionController::class, 'index'])->name('admin.permission.index')->middleware('permission:Store Permission List');

            // Current tenant settings
            Route::get('/settings', [StoreAdminSettingController::class, 'index'])->name('admin.settings.index')->middleware('permission:Store General Setting');
            Route::put('/settings', [StoreAdminSettingController::class, 'update'])->name('admin.settings.update')->middleware('permission:Store Edit Setting');

            // Tenant catalog

            //..............Categories.................//
            Route::get('/catalog/categories', [StoreCategoryController::class, 'categories'])->name('admin.categories.index')->middleware('permission:Store Category List');
            Route::post('/catalog/categories', [StoreCategoryController::class, 'storeCategory'])->name('admin.categories.store')->middleware('permission:Store Category Create');
            Route::put('/catalog/categories/{category}', [StoreCategoryController::class, 'updateCategory'])->name('admin.categories.update')->middleware('permission:Store Category Edit');
            Route::delete('/catalog/categories/{category}', [StoreCategoryController::class, 'deleteCategory'])->name('admin.categories.destroy')->middleware('permission:Store Category Delete');

            //..............Brands.................//
            Route::get('/catalog/brands', [StoreBrandController::class, 'brands'])->name('admin.brands.index')->middleware('permission:Store Brand List');
            Route::post('/catalog/brands', [StoreBrandController::class, 'storeBrand'])->name('admin.brands.store')->middleware('permission:Store Brand Create');
            Route::put('/catalog/brands/{brand}', [StoreBrandController::class, 'updateBrand'])->name('admin.brands.update')->middleware('permission:Store Brand Edit');
            Route::delete('/catalog/brands/{brand}', [StoreBrandController::class, 'deleteBrand'])->name('admin.brands.destroy')->middleware('permission:Store Brand Delete');


            //..............Attributes.................//
            Route::get('/catalog/attributes', [StoreAttributeController::class, 'attributes'])->name('admin.attributes.index')->middleware('permission:Store Attribute List');
            Route::post('/catalog/attributes', [StoreAttributeController::class, 'storeAttribute'])->name('admin.attributes.store')->middleware('permission:Store Attribute Create');
            Route::put('/catalog/attributes/{attribute}', [StoreAttributeController::class, 'updateAttribute'])->name('admin.attributes.update')->middleware('permission:Store Attribute Edit');
            Route::delete('/catalog/attributes/{attribute}', [StoreAttributeController::class, 'deleteAttribute'])->name('admin.attributes.destroy')->middleware('permission:Store Attribute Delete');
            Route::post('/catalog/attributes/{attribute}/values', [StoreAttributeController::class, 'storeAttributeValue'])->name('admin.attributes.values.store')->middleware('permission:Store Attribute Edit');
            Route::put('/catalog/attributes/{attribute}/values/{value}', [StoreAttributeController::class, 'updateAttributeValue'])->name('admin.attributes.values.update')->middleware('permission:Store Attribute Edit');
            Route::delete('/catalog/attributes/{attribute}/values/{value}', [StoreAttributeController::class, 'deleteAttributeValue'])->name('admin.attributes.values.destroy')->middleware('permission:Store Attribute Edit');

            //..............Products.................//
            Route::get('/catalog/products', [StoreProductController::class, 'products'])->name('admin.products.index')->middleware('permission:Store Product List');
            Route::get('/catalog/products/create', [StoreProductController::class, 'createProduct'])->name('admin.products.create')->middleware('permission:Store Product Create');
            Route::post('/catalog/products', [StoreProductController::class, 'storeProduct'])->name('admin.products.store')->middleware('permission:Store Product Create');
            Route::get('/catalog/products/{product}/edit', [StoreProductController::class, 'editProduct'])->name('admin.products.edit')->middleware('permission:Store Product Edit');
            Route::put('/catalog/products/{product}', [StoreProductController::class, 'updateProduct'])->name('admin.products.update')->middleware('permission:Store Product Edit');
            Route::delete('/catalog/products/{product}', [StoreProductController::class, 'deleteProduct'])->name('admin.products.destroy')->middleware('permission:Store Product Delete');

            //..............Inventory.................//
            Route::get('/catalog/inventory', [StoreInventoryController::class, 'inventory'])->name('admin.inventory.index')->middleware('permission:Store Inventory List');
            Route::post('/catalog/inventory/{variant}/receive', [StoreInventoryController::class, 'receiveStock'])->name('admin.inventory.receive')->middleware('permission:Store Inventory Manage');
            Route::patch('/catalog/inventory/batches/{batch}', [StoreInventoryController::class, 'adjustStock'])->name('admin.inventory.adjust')->middleware('permission:Store Inventory Manage');
        });
    });
