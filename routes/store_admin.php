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
use App\Http\Controllers\Store\Admin\Domain\StoreDomainController;
use App\Http\Controllers\Store\Admin\Order\StoreOrderController;
use App\Http\Controllers\Store\Admin\Payment\StorePaymentMethodController;
use App\Http\Controllers\Store\Admin\Promotions\StoreCouponController;
use App\Http\Controllers\Store\Admin\Promotions\StoreDeliveryChargeController;
use App\Http\Controllers\Store\Admin\StoreAdminAdminController;
use App\Http\Controllers\Store\Admin\StoreAdminAuthController;
use App\Http\Controllers\Store\Admin\StoreAdminDashboardController;
use App\Http\Controllers\Store\Admin\StoreAdminPermissionController;
use App\Http\Controllers\Store\Admin\StoreAdminRoleController;
use App\Http\Controllers\Store\Admin\StoreAdminSettingController;
use App\Http\Controllers\Store\Admin\StoreCatalogController;
use App\Http\Controllers\Store\Admin\Prescription\StorePrescriptionController;
use App\Http\Controllers\Store\Admin\Subscription\StoreSubscriptionController;

Route::domain(config('app.base_domain'))
    ->prefix('store')->name('store.')
    ->middleware('web')
    ->group(function () {
        Route::get('/login', [StoreAdminAuthController::class, 'loginPage'])->name('admin.login');
        Route::post('/login', [StoreAdminAuthController::class, 'login'])->name('admin.login.store');

        Route::get('/register', [StoreAdminAuthController::class, 'registerPage'])->name('admin.register');
        Route::post('/register', [StoreAdminAuthController::class, 'register'])->name('admin.register.store');

        Route::middleware('auth.tenant')->group(function () {
            Route::post('/logout', [StoreAdminAuthController::class, 'logout'])->name('admin.logout');
            Route::get('/dashboard', [StoreAdminDashboardController::class, 'dashboard'])->name('admin.dashboard')->middleware('permission:Store Dashboard');

            //..............Admin Management.................//
            Route::get('/admins', [StoreAdminAdminController::class, 'admins'])->name('admin.admins.index')->middleware('permission:Store Admin List');
            Route::get('/admins/create', [StoreAdminAdminController::class, 'createAdmin'])->name('admin.admins.create')->middleware('permission:Store Create Admin');
            Route::post('/admins', [StoreAdminAdminController::class, 'storeAdmin'])->name('admin.admins.store')->middleware('permission:Store Create Admin');
            Route::get('/admins/{admin}/edit', [StoreAdminAdminController::class, 'editAdmin'])->name('admin.admins.edit')->middleware('permission:Store Edit Admin');
            Route::put('/admins/{admin}', [StoreAdminAdminController::class, 'updateAdmin'])->name('admin.admins.update')->middleware('permission:Store Edit Admin');
            Route::delete('/admins/{admin}', [StoreAdminAdminController::class, 'deleteAdmin'])->name('admin.admins.destroy')->middleware('permission:Store Delete Admin');
            Route::patch('/admins/{admin}/status', [StoreAdminAdminController::class, 'statusAdmin'])->name('admin.admins.status')->middleware('permission:Store Status Admin');

            //..............Role Management.................//
            Route::get('/role', [StoreAdminRoleController::class, 'role'])->name('admin.role.index')->middleware('permission:Store Role List');
            Route::get('/role/create', [StoreAdminRoleController::class, 'createRole'])->name('admin.role.create')->middleware('permission:Store Role Create');
            Route::post('/role', [StoreAdminRoleController::class, 'storeRole'])->name('admin.role.store')->middleware('permission:Store Role Create');
            Route::get('/role/{role}/edit', [StoreAdminRoleController::class, 'editRole'])->name('admin.role.edit')->middleware('permission:Store Edit Role');
            Route::put('/role/{role}', [StoreAdminRoleController::class, 'updateRole'])->name('admin.role.update')->middleware('permission:Store Edit Role');
            Route::delete('/role/{role}', [StoreAdminRoleController::class, 'deleteRole'])->name('admin.role.destroy')->middleware('permission:Store Delete Role');
            Route::get('/role/{role}/permission', [StoreAdminRoleController::class, 'assignPermission'])->name('admin.role.permission')->middleware('permission:Store Assign Permission');
            Route::post('/role/{role}/permission', [StoreAdminRoleController::class, 'storePermission'])->name('admin.role.permission.store')->middleware('permission:Store Assign Permission');

            //..............Permission Management.................//
            Route::get('/permission', [StoreAdminPermissionController::class, 'permission'])->name('admin.permission.index')->middleware('permission:Store Permission List');

            //..............Settings Management.................//
            Route::get('/settings', [StoreAdminSettingController::class, 'index'])->name('admin.settings.index')->middleware('permission:Store General Setting');
            Route::match(['post', 'put'], '/settings', [StoreAdminSettingController::class, 'update'])->name('admin.settings.update')->middleware('permission:Store Edit Setting');

            //..............Catalog: Categories.................//
            Route::get('/catalog/categories', [StoreCategoryController::class, 'categories'])->name('admin.categories.index')->middleware('permission:Store Category List');
            Route::get('/catalog/categories/create', [StoreCategoryController::class, 'createCategory'])->name('admin.categories.create')->middleware('permission:Store Category Create');
            Route::post('/catalog/categories', [StoreCategoryController::class, 'storeCategory'])->name('admin.categories.store')->middleware('permission:Store Category Create');
            Route::get('/catalog/categories/{category}/edit', [StoreCategoryController::class, 'editCategory'])->name('admin.categories.edit')->middleware('permission:Store Category Edit');
            Route::put('/catalog/categories/{category}', [StoreCategoryController::class, 'updateCategory'])->name('admin.categories.update')->middleware('permission:Store Category Edit');
            Route::delete('/catalog/categories/{category}', [StoreCategoryController::class, 'deleteCategory'])->name('admin.categories.destroy')->middleware('permission:Store Category Delete');

            //..............Catalog: Brands.................//
            Route::get('/catalog/brands', [StoreBrandController::class, 'brands'])->name('admin.brands.index')->middleware('permission:Store Brand List');
            Route::get('/catalog/brands/create', [StoreBrandController::class, 'createBrand'])->name('admin.brands.create')->middleware('permission:Store Brand Create');
            Route::post('/catalog/brands', [StoreBrandController::class, 'storeBrand'])->name('admin.brands.store')->middleware('permission:Store Brand Create');
            Route::get('/catalog/brands/{brand}/edit', [StoreBrandController::class, 'editBrand'])->name('admin.brands.edit')->middleware('permission:Store Brand Edit');
            Route::put('/catalog/brands/{brand}', [StoreBrandController::class, 'updateBrand'])->name('admin.brands.update')->middleware('permission:Store Brand Edit');
            Route::delete('/catalog/brands/{brand}', [StoreBrandController::class, 'deleteBrand'])->name('admin.brands.destroy')->middleware('permission:Store Brand Delete');

            //..............Catalog: Attributes.................//
            Route::get('/catalog/attributes', [StoreAttributeController::class, 'attributes'])->name('admin.attributes.index')->middleware('permission:Store Attribute List');
            Route::get('/catalog/attributes/create', [StoreAttributeController::class, 'createAttribute'])->name('admin.attributes.create')->middleware('permission:Store Attribute Create');
            Route::post('/catalog/attributes', [StoreAttributeController::class, 'storeAttribute'])->name('admin.attributes.store')->middleware('permission:Store Attribute Create');
            Route::get('/catalog/attributes/{attribute}/edit', [StoreAttributeController::class, 'editAttribute'])->name('admin.attributes.edit')->middleware('permission:Store Attribute Edit');
            Route::put('/catalog/attributes/{attribute}', [StoreAttributeController::class, 'updateAttribute'])->name('admin.attributes.update')->middleware('permission:Store Attribute Edit');
            Route::delete('/catalog/attributes/{attribute}', [StoreAttributeController::class, 'deleteAttribute'])->name('admin.attributes.destroy')->middleware('permission:Store Attribute Delete');

            //..............Catalog: Products.................//
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

            //..............Prescriptions (Direct Uploads Inbox).................//
            Route::get('/prescriptions', [StorePrescriptionController::class, 'index'])->name('admin.prescriptions.index')->middleware('permission:Store Prescription List');
            Route::get('/prescriptions/{prescription}', [StorePrescriptionController::class, 'show'])->name('admin.prescriptions.show')->middleware('permission:Store Prescription View');
            Route::post('/prescriptions/{prescription}/convert', [StorePrescriptionController::class, 'convertToOrder'])->name('admin.prescriptions.convert')->middleware('permission:Store Prescription Convert');
            Route::post('/prescriptions/{prescription}/reject', [StorePrescriptionController::class, 'reject'])->name('admin.prescriptions.reject')->middleware('permission:Store Prescription Reject');
            Route::delete('/prescriptions/{prescription}', [StorePrescriptionController::class, 'destroy'])->name('admin.prescriptions.destroy')->middleware('permission:Store Prescription Delete');

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

            //..............Payment Methods & Gateways.................//
            Route::get('/payments/methods', [StorePaymentMethodController::class, 'paymentMethods'])->name('admin.payments.index')->middleware('permission:Store Payment Method List');
            Route::put('/payments/methods/{payment_method}', [StorePaymentMethodController::class, 'updatePaymentMethod'])->name('admin.payments.update')->middleware('permission:Store Payment Method Edit');
            Route::patch('/payments/methods/{payment_method}/toggle', [StorePaymentMethodController::class, 'toggleStatus'])->name('admin.payments.toggle')->middleware('permission:Store Payment Method Edit');

            //..............Custom Domains.................//
            Route::get('/domains', [StoreDomainController::class, 'domains'])->name('admin.domains.index')->middleware('permission:Store Domain List');
            Route::post('/domains', [StoreDomainController::class, 'storeDomain'])->name('admin.domains.store')->middleware('permission:Store Domain Create');
            Route::post('/domains/{domain}/verify', [StoreDomainController::class, 'verifyDns'])->name('admin.domains.verify')->middleware('permission:Store Domain Edit');
            Route::patch('/domains/{domain}/primary', [StoreDomainController::class, 'makePrimary'])->name('admin.domains.primary')->middleware('permission:Store Domain Edit');
            Route::delete('/domains/{domain}', [StoreDomainController::class, 'deleteDomain'])->name('admin.domains.destroy')->middleware('permission:Store Domain Delete');

            //..............Store Subscription & Billing.................//
            Route::get('/subscription', [StoreSubscriptionController::class, 'index'])->name('admin.subscription.index')->middleware('permission:Store Subscription View');
            Route::post('/subscription/change-plan', [StoreSubscriptionController::class, 'changePlan'])->name('admin.subscription.change_plan')->middleware('permission:Store Subscription Change Plan');
            Route::post('/subscription/cancel', [StoreSubscriptionController::class, 'cancel'])->name('admin.subscription.cancel')->middleware('permission:Store Subscription Cancel');
            Route::post('/subscription/resume', [StoreSubscriptionController::class, 'resume'])->name('admin.subscription.resume')->middleware('permission:Store Subscription Change Plan');
            Route::get('/subscription/invoices/{invoice}', [StoreSubscriptionController::class, 'showInvoice'])->name('admin.subscription.invoice')->middleware('permission:Store Subscription View');
        });
    });
