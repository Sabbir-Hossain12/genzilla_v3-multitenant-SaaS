<?php

use App\Http\Controllers\Platform\Admin\AuthController;
use App\Http\Controllers\Platform\Admin\DashboardController;
use App\Http\Controllers\Platform\Admin\PlatformAdminController;
use App\Http\Controllers\Platform\Admin\PlatformContactController;
use App\Http\Controllers\Platform\Admin\PlatformFaqController;
use App\Http\Controllers\Platform\Admin\PlatformFeatureController;
use App\Http\Controllers\Platform\Admin\PlatformHeroController;
use App\Http\Controllers\Platform\Admin\PlatformMatrixController;
use App\Http\Controllers\Platform\Admin\PlatformNewsletterSubscriberController;
use App\Http\Controllers\Platform\Admin\PlatformPageController;
use App\Http\Controllers\Platform\Admin\PlatformPlanController;
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

        // Admins
        Route::resource('/admins', PlatformAdminController::class)->names('admin.admins');
        Route::post('/change-admin-status', [PlatformAdminController::class, 'changeAdminStatus'])->name('admin.status');
        Route::get('/data', [PlatformAdminController::class, 'getData'])->name('admin.data');

        // Platform Settings
        Route::get('/settings', [PlatformSettingController::class, 'index'])->name('admin.settings.index');
        Route::put('/settings', [PlatformSettingController::class, 'update'])->name('admin.settings.update');

        // Group 1: Landing Page CMS
        Route::get('/hero', [PlatformHeroController::class, 'index'])->name('admin.hero.index');
        Route::put('/hero', [PlatformHeroController::class, 'update'])->name('admin.hero.update');

        Route::resource('/features', PlatformFeatureController::class)->except(['create', 'show', 'edit'])->names('admin.features');
        Route::resource('/steps', PlatformStepController::class)->except(['create', 'show', 'edit'])->names('admin.steps');
        Route::resource('/use-cases', PlatformUseCaseController::class)->except(['create', 'show', 'edit'])->names('admin.use-cases');
        Route::resource('/store-demos', PlatformStoreDemoController::class)->except(['create', 'show', 'edit'])->names('admin.store-demos');
        Route::resource('/testimonials', PlatformTestimonialController::class)->except(['create', 'show', 'edit'])->names('admin.testimonials');
        Route::resource('/faqs', PlatformFaqController::class)->except(['create', 'show', 'edit'])->names('admin.faqs');

        Route::get('/matrix', [PlatformMatrixController::class, 'index'])->name('admin.matrix.index');
        Route::put('/matrix', [PlatformMatrixController::class, 'update'])->name('admin.matrix.update');

        // Group 2: Plans & Subscriptions
        Route::resource('/plans', PlatformPlanController::class)->except(['create', 'show', 'edit'])->names('admin.plans');
        Route::resource('/subscriptions', TenantSubscriptionController::class)->only(['index', 'store', 'update'])->names('admin.subscriptions');

        Route::get('/invoices', [TenantInvoiceController::class, 'index'])->name('admin.invoices.index');
        Route::get('/invoices/{invoice}', [TenantInvoiceController::class, 'show'])->name('admin.invoices.show');
        Route::patch('/invoices/{invoice}/status', [TenantInvoiceController::class, 'updateStatus'])->name('admin.invoices.update-status');

        // Group 3: Tenant Management
        Route::resource('/domains', TenantDomainController::class)->except(['create', 'show', 'edit'])->names('admin.domains');
        Route::get('/usages', [TenantUsageController::class, 'index'])->name('admin.usages.index');

        // Group 4: Content & Support
        Route::resource('/pages', PlatformPageController::class)->names('admin.pages');

        Route::get('/contacts', [PlatformContactController::class, 'index'])->name('admin.contacts.index');
        Route::patch('/contacts/{contact}/status', [PlatformContactController::class, 'updateStatus'])->name('admin.contacts.update-status');
        Route::delete('/contacts/{contact}', [PlatformContactController::class, 'destroy'])->name('admin.contacts.destroy');

        Route::get('/newsletter-subscribers', [PlatformNewsletterSubscriberController::class, 'index'])->name('admin.newsletter.index');
        Route::post('/newsletter-subscribers/{subscriber}/toggle', [PlatformNewsletterSubscriberController::class, 'toggleStatus'])->name('admin.newsletter.toggle');
        Route::delete('/newsletter-subscribers/{subscriber}', [PlatformNewsletterSubscriberController::class, 'destroy'])->name('admin.newsletter.destroy');
    });
});
