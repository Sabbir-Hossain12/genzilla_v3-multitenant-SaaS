<?php

use App\Http\Controllers\Store\Admin\Catalog\StoreAttributeController;
use App\Http\Controllers\Store\Admin\Catalog\StoreBrandController;
use App\Http\Controllers\Store\Admin\Catalog\StoreCategoryController;
use App\Http\Controllers\Store\Admin\Catalog\StoreInventoryController;
use App\Http\Controllers\Store\Admin\Catalog\StoreProductController;
use App\Http\Controllers\Store\Admin\Content\StoreBannerController;
use App\Http\Controllers\Store\Admin\Content\StorePageController;
use App\Http\Controllers\Store\Admin\Content\StoreSliderController;
use App\Http\Controllers\Store\Admin\Customer\StoreCustomerController;
use App\Http\Controllers\Store\Admin\Order\StoreOrderController;
use App\Http\Controllers\Store\Admin\Promotions\StoreCouponController;
use App\Http\Controllers\Store\Admin\Promotions\StoreDeliveryChargeController;
use App\Http\Controllers\Store\Admin\StoreAdminAdminController;
use App\Http\Controllers\Store\Admin\StoreAdminAuthController;
use App\Http\Controllers\Store\Admin\StoreAdminDashboardController;
use App\Http\Controllers\Store\Admin\StoreAdminPermissionController;
use App\Http\Controllers\Store\Admin\StoreAdminRoleController;
use App\Http\Controllers\Store\Admin\StoreAdminSettingController;
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

            //..............Orders.................//
            Route::get('/orders', [StoreOrderController::class, 'orders'])->name('admin.orders.index')->middleware('permission:Store Order List');
            Route::get('/orders/create', [StoreOrderController::class, 'createOrder'])->name('admin.orders.create')->middleware('permission:Store Order Create');
            Route::post('/orders', [StoreOrderController::class, 'storeOrder'])->name('admin.orders.store')->middleware('permission:Store Order Create');
            Route::get('/orders/{order}', [StoreOrderController::class, 'showOrder'])->name('admin.orders.show')->middleware('permission:Store Order View');
            Route::patch('/orders/{order}/status', [StoreOrderController::class, 'updateStatus'])->name('admin.orders.status')->middleware('permission:Store Order Edit');
            Route::patch('/orders/{order}/payment-status', [StoreOrderController::class, 'updateStatus'])->name('admin.orders.payment_status')->middleware('permission:Store Order Edit');
            Route::patch('/orders/{order}/prescription-status', [StoreOrderController::class, 'updateStatus'])->name('admin.orders.prescription_status')->middleware('permission:Store Order Edit');
            Route::patch('/orders/{order}/cancel', [StoreOrderController::class, 'updateStatus'])->name('admin.orders.cancel')->middleware('permission:Store Order Edit');
            Route::delete('/orders/{order}', [StoreOrderController::class, 'deleteOrder'])->name('admin.orders.destroy')->middleware('permission:Store Order Delete');

            //..............Customers & CRM.................//
            Route::get('/customers', [StoreCustomerController::class, 'customers'])->name('admin.customers.index')->middleware('permission:Store Customer List');
            Route::get('/customers/create', [StoreCustomerController::class, 'createCustomer'])->name('admin.customers.create')->middleware('permission:Store Customer Create');
            Route::post('/customers', [StoreCustomerController::class, 'storeCustomer'])->name('admin.customers.store')->middleware('permission:Store Customer Create');
            Route::get('/customers/{customer}', [StoreCustomerController::class, 'showCustomer'])->name('admin.customers.show')->middleware('permission:Store Customer View');
            Route::get('/customers/{customer}/edit', [StoreCustomerController::class, 'editCustomer'])->name('admin.customers.edit')->middleware('permission:Store Customer Edit');
            Route::put('/customers/{customer}', [StoreCustomerController::class, 'updateCustomer'])->name('admin.customers.update')->middleware('permission:Store Customer Edit');
            Route::delete('/customers/{customer}', [StoreCustomerController::class, 'deleteCustomer'])->name('admin.customers.destroy')->middleware('permission:Store Customer Delete');
            Route::patch('/customers/{customer}/toggle', [StoreCustomerController::class, 'toggleStatus'])->name('admin.customers.toggle')->middleware('permission:Store Customer Edit');
            Route::post('/customers/{customer}/addresses', [StoreCustomerController::class, 'storeAddress'])->name('admin.customers.addresses.store')->middleware('permission:Store Customer Edit');
            Route::delete('/customers/{customer}/addresses/{address}', [StoreCustomerController::class, 'deleteAddress'])->name('admin.customers.addresses.destroy')->middleware('permission:Store Customer Edit');

            //..............Promotions: Coupons.................//
            Route::get('/promotions/coupons', [StoreCouponController::class, 'coupons'])->name('admin.coupons.index')->middleware('permission:Store Coupon List');
            Route::post('/promotions/coupons', [StoreCouponController::class, 'storeCoupon'])->name('admin.coupons.store')->middleware('permission:Store Coupon Create');
            Route::put('/promotions/coupons/{coupon}', [StoreCouponController::class, 'updateCoupon'])->name('admin.coupons.update')->middleware('permission:Store Coupon Edit');
            Route::delete('/promotions/coupons/{coupon}', [StoreCouponController::class, 'deleteCoupon'])->name('admin.coupons.destroy')->middleware('permission:Store Coupon Delete');
            Route::patch('/promotions/coupons/{coupon}/toggle', [StoreCouponController::class, 'toggleStatus'])->name('admin.coupons.toggle')->middleware('permission:Store Coupon Edit');

            //..............Promotions: Delivery Charges.................//
            Route::get('/promotions/delivery-charges', [StoreDeliveryChargeController::class, 'deliveryCharges'])->name('admin.delivery_charges.index')->middleware('permission:Store Delivery Charge List');
            Route::post('/promotions/delivery-charges', [StoreDeliveryChargeController::class, 'storeDeliveryCharge'])->name('admin.delivery_charges.store')->middleware('permission:Store Delivery Charge Create');
            Route::put('/promotions/delivery-charges/{delivery_charge}', [StoreDeliveryChargeController::class, 'updateDeliveryCharge'])->name('admin.delivery_charges.update')->middleware('permission:Store Delivery Charge Edit');
            Route::delete('/promotions/delivery-charges/{delivery_charge}', [StoreDeliveryChargeController::class, 'deleteDeliveryCharge'])->name('admin.delivery_charges.destroy')->middleware('permission:Store Delivery Charge Delete');
            Route::patch('/promotions/delivery-charges/{delivery_charge}/toggle', [StoreDeliveryChargeController::class, 'toggleStatus'])->name('admin.delivery_charges.toggle')->middleware('permission:Store Delivery Charge Edit');

            //..............Content: Custom Pages.................//
            Route::get('/content/pages', [StorePageController::class, 'pages'])->name('admin.pages.index')->middleware('permission:Store Page List');
            Route::get('/content/pages/create', [StorePageController::class, 'createPage'])->name('admin.pages.create')->middleware('permission:Store Page Create');
            Route::post('/content/pages', [StorePageController::class, 'storePage'])->name('admin.pages.store')->middleware('permission:Store Page Create');
            Route::get('/content/pages/{page}/edit', [StorePageController::class, 'editPage'])->name('admin.pages.edit')->middleware('permission:Store Page Edit');
            Route::put('/content/pages/{page}', [StorePageController::class, 'updatePage'])->name('admin.pages.update')->middleware('permission:Store Page Edit');
            Route::delete('/content/pages/{page}', [StorePageController::class, 'deletePage'])->name('admin.pages.destroy')->middleware('permission:Store Page Delete');
            Route::patch('/content/pages/{page}/toggle', [StorePageController::class, 'toggleStatus'])->name('admin.pages.toggle')->middleware('permission:Store Page Edit');

            //..............Content: Hero Sliders.................//
            Route::get('/content/sliders', [StoreSliderController::class, 'sliders'])->name('admin.sliders.index')->middleware('permission:Store Slider List');
            Route::post('/content/sliders', [StoreSliderController::class, 'storeSlider'])->name('admin.sliders.store')->middleware('permission:Store Slider Create');
            Route::put('/content/sliders/{slider}', [StoreSliderController::class, 'updateSlider'])->name('admin.sliders.update')->middleware('permission:Store Slider Edit');
            Route::delete('/content/sliders/{slider}', [StoreSliderController::class, 'deleteSlider'])->name('admin.sliders.destroy')->middleware('permission:Store Slider Delete');
            Route::patch('/content/sliders/{slider}/toggle', [StoreSliderController::class, 'toggleStatus'])->name('admin.sliders.toggle')->middleware('permission:Store Slider Edit');

            //..............Content: Promotional Banners.................//
            Route::get('/content/banners', [StoreBannerController::class, 'banners'])->name('admin.banners.index')->middleware('permission:Store Banner List');
            Route::post('/content/banners', [StoreBannerController::class, 'storeBanner'])->name('admin.banners.store')->middleware('permission:Store Banner Create');
            Route::put('/content/banners/{banner}', [StoreBannerController::class, 'updateBanner'])->name('admin.banners.update')->middleware('permission:Store Banner Edit');
            Route::delete('/content/banners/{banner}', [StoreBannerController::class, 'deleteBanner'])->name('admin.banners.destroy')->middleware('permission:Store Banner Delete');
            Route::patch('/content/banners/{banner}/toggle', [StoreBannerController::class, 'toggleStatus'])->name('admin.banners.toggle')->middleware('permission:Store Banner Edit');
        });
    });
