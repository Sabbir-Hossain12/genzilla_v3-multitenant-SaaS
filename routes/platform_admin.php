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

Route::domain(config('app.base_domain'))->prefix('admin')->group(function () {
    Route::get('login', [AuthController::class, 'loginPage'])->name('admin.login');
    Route::post('login', [AuthController::class, 'login'])->name('admin.login.store');

    // Admin Panel Protected Routes
    Route::middleware(['auth', 'permission:Platform Admin Dashboard'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');

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
        Route::get('/data', [PlatformAdminController::class, 'getData'])->name('admin.data')->middleware('permission:Admin List');

        // Roles
        Route::get('/roles', [AdminRoleController::class, 'index'])->name('admin.role.index')->middleware('permission:Role List');
        Route::get('/roles/data', [AdminRoleController::class, 'getData'])->name('admin.role.data')->middleware('permission:Role List');
        Route::post('/roles', [AdminRoleController::class, 'store'])->name('admin.role.store')->middleware('permission:Role Create');
        Route::get('/roles/{role}/edit', [AdminRoleController::class, 'edit'])->name('admin.role.edit')->middleware('permission:Edit Role');
        Route::put('/roles/{role}', [AdminRoleController::class, 'update'])->name('admin.role.update')->middleware('permission:Edit Role');
        Route::delete('/roles/{role}', [AdminRoleController::class, 'destroy'])->name('admin.role.destroy')->middleware('permission:Delete Role');
        Route::get('/roles/{role}/permissions', [AdminRoleController::class, 'assignPermissionsToRolePage'])->name('admin.role.permission.edit')->middleware('permission:Assign Permission');
        Route::put('/roles/{role}/permissions', [AdminRoleController::class, 'assignPermissionsToRole'])->name('admin.role.permission.update')->middleware('permission:Assign Permission');

        // Permissions
        Route::get('/permissions', [AdminPermissionController::class, 'index'])->name('admin.permission.index')->middleware('permission:Permission List');
        Route::get('/permissions/data', [AdminPermissionController::class, 'getData'])->name('admin.permission.data')->middleware('permission:Permission List');
        Route::post('/permissions', [AdminPermissionController::class, 'store'])->name('admin.permission.store')->middleware('permission:Create Permission');
        Route::get('/permissions/{permission}/edit', [AdminPermissionController::class, 'edit'])->name('admin.permission.edit')->middleware('permission:Edit Permission');
        Route::put('/permissions/{permission}', [AdminPermissionController::class, 'update'])->name('admin.permission.update')->middleware('permission:Edit Permission');
        Route::delete('/permissions/{permission}', [AdminPermissionController::class, 'destroy'])->name('admin.permission.destroy')->middleware('permission:Delete Permission');

        // Platform Settings
        Route::get('/settings', [PlatformSettingController::class, 'index'])->name('admin.settings.index')->middleware('permission:General Setting');
        Route::put('/settings', [PlatformSettingController::class, 'update'])->name('admin.settings.update')->middleware('permission:Edit Setting');

        // Group 1: Landing Page CMS
        Route::get('/hero', [PlatformHeroController::class, 'index'])->name('admin.hero.index')->middleware('permission:Manage Hero');
        Route::put('/hero', [PlatformHeroController::class, 'update'])->name('admin.hero.update')->middleware('permission:Manage Hero');

        Route::resource('/features', PlatformFeatureController::class)->except(['create', 'show', 'edit'])->names('admin.features')->middleware('permission:Manage Features');
        Route::resource('/steps', PlatformStepController::class)->except(['create', 'show', 'edit'])->names('admin.steps')->middleware('permission:Manage Steps');
        Route::resource('/use-cases', PlatformUseCaseController::class)->except(['create', 'show', 'edit'])->names('admin.use-cases')->middleware('permission:Manage Use Cases');
        Route::resource('/store-demos', PlatformStoreDemoController::class)->except(['create', 'show', 'edit'])->names('admin.store-demos')->middleware('permission:Manage Store Demos');
        Route::resource('/testimonials', PlatformTestimonialController::class)->except(['create', 'show', 'edit'])->names('admin.testimonials')->middleware('permission:Manage Testimonials');
        Route::resource('/faqs', PlatformFaqController::class)->except(['create', 'show', 'edit'])->names('admin.faqs')->middleware('permission:Manage Faqs');

        Route::get('/matrix', [PlatformMatrixController::class, 'index'])->name('admin.matrix.index')->middleware('permission:Manage Matrix');
        Route::put('/matrix', [PlatformMatrixController::class, 'update'])->name('admin.matrix.update')->middleware('permission:Manage Matrix');

        // Group 2: Plans & Subscriptions
        Route::resource('/plans', PlatformPlanController::class)->except(['create', 'show', 'edit'])->names('admin.plans')->middleware('permission:Manage Plans');
        Route::resource('/subscriptions', TenantSubscriptionController::class)->only(['index', 'store', 'update'])->names('admin.subscriptions')->middleware('permission:Manage Subscriptions');

        Route::get('/invoices', [TenantInvoiceController::class, 'index'])->name('admin.invoices.index')->middleware('permission:Manage Invoices');
        Route::get('/invoices/{invoice}', [TenantInvoiceController::class, 'show'])->name('admin.invoices.show')->middleware('permission:Manage Invoices');
        Route::patch('/invoices/{invoice}/status', [TenantInvoiceController::class, 'updateStatus'])->name('admin.invoices.update-status')->middleware('permission:Manage Invoices');

        // Group 3: Tenant Management
        Route::resource('/domains', TenantDomainController::class)->except(['create', 'show', 'edit'])->names('admin.domains')->middleware('permission:Manage Domains');
        Route::get('/usages', [TenantUsageController::class, 'index'])->name('admin.usages.index')->middleware('permission:Manage Usages');

        // Group 4: Content & Support
        Route::resource('/pages', PlatformPageController::class)->names('admin.pages')->middleware('permission:Manage Pages');

        // Blog
        Route::resource('/blog', PlatformBlogPostController::class)
            ->parameters(['blog' => 'blogPost'])
            ->names('admin.blog')
            ->except('show')
            ->middleware('permission:Manage Blog');
        Route::get('/blog-categories', [PlatformBlogCategoryController::class, 'index'])->name('admin.blog-categories.index')->middleware('permission:Manage Blog Categories');
        Route::post('/blog-categories', [PlatformBlogCategoryController::class, 'store'])->name('admin.blog-categories.store')->middleware('permission:Manage Blog Categories');
        Route::put('/blog-categories/{blogCategory}', [PlatformBlogCategoryController::class, 'update'])->name('admin.blog-categories.update')->middleware('permission:Manage Blog Categories');
        Route::delete('/blog-categories/{blogCategory}', [PlatformBlogCategoryController::class, 'destroy'])->name('admin.blog-categories.destroy')->middleware('permission:Manage Blog Categories');

        Route::get('/contacts', [PlatformContactController::class, 'index'])->name('admin.contacts.index')->middleware('permission:Manage Contacts');
        Route::patch('/contacts/{contact}/status', [PlatformContactController::class, 'updateStatus'])->name('admin.contacts.update-status')->middleware('permission:Manage Contacts');
        Route::delete('/contacts/{contact}', [PlatformContactController::class, 'destroy'])->name('admin.contacts.destroy')->middleware('permission:Manage Contacts');

        Route::get('/newsletter-subscribers', [PlatformNewsletterSubscriberController::class, 'index'])->name('admin.newsletter.index')->middleware('permission:Manage Newsletter');
        Route::post('/newsletter-subscribers/{subscriber}/toggle', [PlatformNewsletterSubscriberController::class, 'toggleStatus'])->name('admin.newsletter.toggle')->middleware('permission:Manage Newsletter');
        Route::delete('/newsletter-subscribers/{subscriber}', [PlatformNewsletterSubscriberController::class, 'destroy'])->name('admin.newsletter.destroy')->middleware('permission:Manage Newsletter');
    });
});
