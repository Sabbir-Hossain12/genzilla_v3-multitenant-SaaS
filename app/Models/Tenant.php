<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Database\Seeders\PermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Http\Request;

class Tenant extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'theme_settings' => 'array',
        'stripeOnboarded' => 'boolean',
        'enable_direct_prescriptions' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class, 'tenant_id');
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(TenantSubscription::class, 'tenant_id')->latestOfMany();
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(TenantSubscription::class, 'tenant_id')->ofMany([
            'id' => 'max',
        ], function ($query) {
            $query->whereIn('subscription_status', ['active', 'trialing']);
        });
    }

    public function currentSubscription(): BelongsTo
    {
        return $this->belongsTo(TenantSubscription::class, 'current_subscription_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(TenantInvoice::class, 'tenant_id');
    }

    public function usage(): HasOne
    {
        return $this->hasOne(TenantUsage::class, 'tenant_id');
    }

    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class, 'tenant_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(TenantPrescription::class, 'tenant_id');
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(TenantPaymentMethod::class, 'tenant_id');
    }

    public function allowsCustomDomain(): bool
    {
        if ($this->activeSubscription && $this->activeSubscription->plan) {
            return (bool) $this->activeSubscription->plan->allow_custom_domain;
        }

        if ($this->currentSubscription && $this->currentSubscription->plan) {
            return (bool) $this->currentSubscription->plan->allow_custom_domain;
        }

        $sub = $this->subscriptions()->with('plan')->latest('id')->first();
        if ($sub && $sub->plan) {
            return (bool) $sub->plan->allow_custom_domain;
        }

        return false;
    }

    /**
     * Resolve the current platform subscription plan for the tenant.
     */
    public function currentPlan(): ?PlatformPlan
    {
        return $this->activeSubscription?->plan
            ?? $this->currentSubscription?->plan
            ?? $this->subscriptions()->with('plan')->latest('id')->first()?->plan;
    }

    /**
     * Check if the tenant has an active or trialing subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return in_array($this->subscription_status, ['active', 'trialing'], true);
    }

    /**
     * Get maximum allowed products from plan.
     */
    public function maxProducts(): int
    {
        return (int) ($this->currentPlan()?->max_products ?? 50);
    }

    /**
     * Get current products count.
     */
    public function currentProductsCount(): int
    {
        return (int) $this->products()->count();
    }

    /**
     * Check if tenant has reached the product limit.
     */
    public function hasReachedProductLimit(): bool
    {
        return $this->currentProductsCount() >= $this->maxProducts();
    }

    /**
     * Check if tenant can create more products.
     */
    public function canCreateProduct(): bool
    {
        return $this->hasActiveSubscription() && !$this->hasReachedProductLimit();
    }

    /**
     * Get maximum allowed staff accounts from plan.
     */
    public function maxStaff(): int
    {
        return (int) ($this->currentPlan()?->max_staff_accounts ?? 1);
    }

    /**
     * Get current staff accounts count.
     */
    public function currentStaffCount(): int
    {
        $staffCount = User::whereHas('roles', function ($q) {
            $q->where('roles.tenant_id', $this->id);
        })->count();

        return max(1, $staffCount);
    }

    /**
     * Check if tenant has reached the staff account limit.
     */
    public function hasReachedStaffLimit(): bool
    {
        return $this->currentStaffCount() >= $this->maxStaff();
    }

    /**
     * Check if tenant can create more staff accounts.
     */
    public function canCreateStaff(): bool
    {
        return $this->hasActiveSubscription() && !$this->hasReachedStaffLimit();
    }

    /**
     * Get maximum allowed storage in megabytes.
     */
    public function maxStorageMB(): int
    {
        return (int) ($this->currentPlan()?->max_storage_mb ?? 100);
    }

    /**
     * Get maximum allowed storage in bytes.
     */
    public function maxStorageBytes(): int
    {
        return $this->maxStorageMB() * 1024 * 1024;
    }

    /**
     * Calculate current storage used in bytes.
     */
    /**
     * All media items belonging to this tenant.
     */
    public function media(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Media::class, 'tenant_id');
    }

    /**
     * Calculate total bytes recorded in the media table for this tenant.
     */
    public function mediaStorageBytes(): int
    {
        return (int) $this->media()->sum('size_bytes');
    }

    /**
     * Calculate current storage used in bytes.
     * Evaluates both physical disk directory and media database table.
     */
    public function storageUsedBytes(): int
    {
        $diskBytes = 0;
        $tenantStoragePath = storage_path('app/public/tenants/' . $this->id);

        if (file_exists($tenantStoragePath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tenantStoragePath, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $diskBytes += $file->getSize();
                }
            }
        }

        return max($diskBytes, $this->mediaStorageBytes());
    }

    /**
     * Reconcile and clean unreferenced orphaned files in this tenant's directory.
     */
    public function cleanOrphanFiles(): array
    {
        $validPaths = collect();
        $validPaths = $validPaths->merge($this->media()->pluck('path'));
        if ($this->logo_path) {
            $validPaths->push($this->logo_path);
        }
        $validPaths = $validPaths->merge(\App\Models\Order::where('tenant_id', $this->id)->whereNotNull('prescription_image')->pluck('prescription_image'));
        $validPaths = $validPaths->merge(\App\Models\TenantPrescription::where('tenant_id', $this->id)->whereNotNull('prescription_image')->pluck('prescription_image'));

        $validSet = array_flip($validPaths->filter()->all());

        $tenantDir = storage_path('app/public/tenants/' . $this->id);
        $deleted = [];

        if (file_exists($tenantDir)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tenantDir, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $fullPath = $file->getPathname();
                    $relPath = str_replace(storage_path('app/public/'), '', $fullPath);
                    $relPath = str_replace('\\', '/', $relPath);
                    if (!isset($validSet[$relPath])) {
                        $size = $file->getSize();
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($relPath);
                        $deleted[] = ['path' => $relPath, 'size_bytes' => $size];
                    }
                }
            }
        }

        $this->syncUsage();

        return $deleted;
    }

    /**
     * Calculate current storage used in megabytes.
     */
    public function storageUsedMB(): float
    {
        return round($this->storageUsedBytes() / (1024 * 1024), 2);
    }

    /**
     * Storage percentage used.
     */
    public function storagePercent(): int
    {
        $max = max(1, $this->maxStorageBytes());
        return min(100, (int) round(($this->storageUsedBytes() / $max) * 100));
    }

    /**
     * Check if tenant can upload a given number of bytes without exceeding quota.
     */
    public function canUploadBytes(int $bytes): bool
    {
        return ($this->storageUsedBytes() + $bytes) <= $this->maxStorageBytes();
    }

    /**
     * Get maximum single file size in megabytes.
     */
    public function maxFileSizeMB(): int
    {
        return (int) ($this->currentPlan()?->max_digital_file_size_mb ?? 10);
    }

    /**
     * Get maximum single file size in kilobytes (for Laravel validation rules).
     */
    public function maxFileSizeKilobytes(): int
    {
        return $this->maxFileSizeMB() * 1024;
    }

    /**
     * Get maximum single file size in bytes.
     */
    public function maxFileSizeBytes(): int
    {
        return $this->maxFileSizeMB() * 1024 * 1024;
    }

    /**
     * Validate an incoming request's files against plan file size and storage quota limits.
     * Returns an error message string if a limit is exceeded, or null if allowed.
     */
    public function validateUploadQuota(\Illuminate\Http\Request $request, array $fileKeys = ['image', 'image_file', 'slider_images']): ?string
    {
        if (!$this->hasActiveSubscription()) {
            return 'Your store subscription is not currently active. Please review your billing in Store Subscription & Billing.';
        }

        $maxSingleBytes = $this->maxFileSizeBytes();
        $maxSingleMB = $this->maxFileSizeMB();
        $planName = $this->currentPlan()?->name ?? 'Current';

        $totalIncomingBytes = 0;

        foreach ($fileKeys as $key) {
            if ($request->hasFile($key)) {
                $fileOrArray = $request->file($key);
                $files = is_array($fileOrArray) ? $fileOrArray : [$fileOrArray];

                foreach ($files as $file) {
                    if (!$file instanceof \Illuminate\Http\UploadedFile) {
                        continue;
                    }

                    $fileSize = $file->getSize();

                    // Check single file size limit
                    if ($fileSize > $maxSingleBytes) {
                        $actualMB = round($fileSize / (1024 * 1024), 2);
                        return "The file '{$file->getClientOriginalName()}' is {$actualMB}MB, which exceeds the {$maxSingleMB}MB limit allowed on your {$planName} plan. Please upload a smaller file or upgrade your plan.";
                    }

                    $totalIncomingBytes += $fileSize;
                }
            }
        }

        // Check total storage quota limit
        if ($totalIncomingBytes > 0 && !$this->canUploadBytes($totalIncomingBytes)) {
            $usedMB = $this->storageUsedMB();
            $maxMB = $this->maxStorageMB();
            $incomingMB = round($totalIncomingBytes / (1024 * 1024), 2);
            return "Storage quota exceeded for your {$planName} plan ({$usedMB}MB used of {$maxMB}MB limit). Uploading {$incomingMB}MB more would exceed your quota. Please remove existing files or upgrade your plan.";
        }

        return null;
    }

    /**
     * Throw a ValidationException if product catalog limit is reached.
     */
    public function ensureCanCreateProduct(): void
    {
        if (!$this->canCreateProduct()) {
            $planName = $this->currentPlan()?->name ?? 'Current';
            throw \Illuminate\Validation\ValidationException::withMessages([
                'name' => "Product catalog limit reached ({$this->currentProductsCount()}/{$this->maxProducts()}) for your {$planName} plan. Please upgrade your subscription to add more products."
            ]);
        }
    }

    /**
     * Throw a ValidationException if staff account limit is reached.
     */
    public function ensureCanCreateStaff(): void
    {
        if (!$this->canCreateStaff()) {
            $planName = $this->currentPlan()?->name ?? 'Current';
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => "Staff account limit reached ({$this->currentStaffCount()}/{$this->maxStaff()}) for your {$planName} plan. Please upgrade your subscription to add more staff members."
            ]);
        }
    }

    /**
     * Throw a ValidationException if file size or storage limit is exceeded.
     */
    public function ensureCanUpload(\Illuminate\Http\Request $request, array $fileKeys = ['image', 'image_file', 'slider_images']): void
    {
        if ($error = $this->validateUploadQuota($request, $fileKeys)) {
            $firstKey = $fileKeys[0] ?? 'image';
            throw \Illuminate\Validation\ValidationException::withMessages([
                $firstKey => $error,
            ]);
        }
    }

    /**
     * Synchronize and save current usage metrics in tenant_usages table.
     */
    public function syncUsage(): TenantUsage
    {
        $storageBytes = $this->storageUsedBytes();
        $staffCount = $this->currentStaffCount();
        $productsCount = $this->currentProductsCount();

        return TenantUsage::updateOrCreate(
            ['tenant_id' => $this->id],
            [
                'storage_used_bytes' => $storageBytes,
                'staff_accounts_count' => $staffCount,
                'products_count' => $productsCount,
            ]
        );
    }

    public function categories(): HasMany { return $this->hasMany(Category::class); }
    public function brands(): HasMany { return $this->hasMany(Brand::class); }
    public function products(): HasMany { return $this->hasMany(Product::class); }
    public function productAttributes(): HasMany { return $this->hasMany(ProductAttribute::class, 'tenant_id'); }

    public function initializeCatalogDefaults(): void
    {
        foreach (['Size' => ['Small', 'Medium', 'Large'], 'Color' => ['Black', 'White']] as $name => $values) {
            $attribute = ProductAttribute::firstOrCreate(
                ['tenant_id' => $this->id, 'slug' => Str::slug($name)],
                ['name' => $name, 'position' => 0]
            );
            foreach ($values as $position => $value) {
                ProductAttributeValue::firstOrCreate(
                    ['tenant_id' => $this->id, 'attribute_id' => $attribute->id, 'slug' => Str::slug($value)],
                    ['value' => $value, 'position' => $position, 'status' => true]
                );
            }
        }
    }

    public function primaryDomain(): HasOne
    {
        return $this->hasOne(TenantDomain::class, 'tenant_id')->where('is_primary', true);
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['store_name'] ?? null;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['store_name'] = $value;
    }

    /**
     * Provision a brand new User, Tenant, Domain, Subscription, Invoice, and Usage metrics
     * following the exact TenantSubscriptionSeeder sequence.
     */
    public static function provisionWithManualSubscription(
        array $userData,
        array $tenantData,
        PlatformPlan $plan,
        string $billingInterval = 'monthly',
        string $subscriptionStatus = 'trialing',
        ?Carbon $periodStart = null,
        ?Carbon $periodEnd = null,
        ?Carbon $trialEnd = null,
        bool $createInvoice = true,
        ?float $customInvoiceAmount = null
    ): array {
        return DB::transaction(function () use (
            $userData,
            $tenantData,
            $plan,
            $billingInterval,
            $subscriptionStatus,
            $periodStart,
            $periodEnd,
            $trialEnd,
            $createInvoice,
            $customInvoiceAmount
        ) {
            // ------------------------------------------------------------------
            // 1. Create Default User (Store Owner / Merchant)
            // ------------------------------------------------------------------
            $password = !empty($userData['password']) ? $userData['password'] : 'password';
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($password),
                'status' => $userData['status'] ?? 1,
            ]);

            // ------------------------------------------------------------------
            // 2. Provision Tenant (Storefront)
            // ------------------------------------------------------------------
            $tenant = static::create([
                'user_id' => $user->id,
                'store_name' => $tenantData['store_name'],
                'support_email' => $tenantData['support_email'] ?? $user->email,
                'support_phone' => $tenantData['support_phone'] ?? null,
                'subdomain' => strtolower($tenantData['subdomain']),
                'custom_domain' => $tenantData['custom_domain'] ?? null,
                'currency' => strtoupper($tenantData['currency'] ?? 'USD'),
                'timezone' => $tenantData['timezone'] ?? 'UTC',
                'subscription_status' => in_array($subscriptionStatus, ['trialing', 'active', 'past_due', 'expired', 'cancelled'])
                    ? $subscriptionStatus
                    : 'trialing',
                'status' => $tenantData['status'] ?? 'active',
            ]);
            $tenant->initializeCatalogDefaults();

            // Give each newly onboarded tenant a tenant-scoped owner role with
            // the full storefront permission catalog.
            $permissionRegistrar = app(PermissionRegistrar::class);
            $previousTeamId = $permissionRegistrar->getPermissionsTeamId();
            $permissionRegistrar->setPermissionsTeamId($tenant->id);

            try {
                $ownerRole = Role::firstOrCreate([
                    'tenant_id' => $tenant->id,
                    'name' => 'Store Owner',
                    'guard_name' => 'web',
                ]);
                $storePermissions = [];
                foreach (PermissionSeeder::STOREFRONT_GROUPS as $groupName => $permissionNames) {
                    foreach ($permissionNames as $permissionName) {
                        $storePermissions[] = Permission::updateOrCreate(
                            ['name' => $permissionName, 'guard_name' => 'web'],
                            ['group_name' => $groupName, 'panel' => 'store']
                        );
                    }
                }
                $ownerRole->syncPermissions($storePermissions);
                $user->assignRole($ownerRole);
            } finally {
                $permissionRegistrar->setPermissionsTeamId($previousTeamId);
            }

            // ------------------------------------------------------------------
            // 3. Create Tenant Domain Entry (*.genzilla.com)
            // ------------------------------------------------------------------
            $fullSubdomain = strtolower($tenant->subdomain) . '.' . config('app.base_domain', 'genzilla.com');
            $tenantDomain = TenantDomain::create([
                'tenant_id' => $tenant->id,
                'domain' => $fullSubdomain,
                'is_custom' => false,
                'dns_verified' => true,
                'is_primary' => true,
                'ssl_status' => 'active',
            ]);

            // ------------------------------------------------------------------
            // 4. Create Tenant Subscription (Single record per Tenant)
            // ------------------------------------------------------------------
            $start = $periodStart ? $periodStart->copy() : now();
            if ($subscriptionStatus === 'trialing') {
                $trial = $trialEnd ? $trialEnd->copy() : ($periodEnd ? $periodEnd->copy() : $start->copy()->addDays(14));
                $end = $periodEnd ? $periodEnd->copy() : $trial->copy();
            } else {
                $trial = $trialEnd ? $trialEnd->copy() : null;
                $end = $periodEnd ? $periodEnd->copy() : ($billingInterval === 'yearly' ? $start->copy()->addYear() : $start->copy()->addMonth());
            }

            $subscription = TenantSubscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'billing_interval' => $billingInterval,
                'trial_ends_at' => $trial,
                'current_period_starts_at' => $start,
                'current_period_ends_at' => $end,
                'canceled_at' => $subscriptionStatus === 'cancelled' ? now() : null,
                'source' => 'manual',
                'subscription_status' => $subscriptionStatus,
            ]);

            // Sync subscription FK back to tenant table
            $tenant->update([
                'current_subscription_id' => $subscription->id,
            ]);

            // ------------------------------------------------------------------
            // 5. Create Initial Settled Invoice (Billing & Onboarding Record)
            // ------------------------------------------------------------------
            $invoice = null;
            if ($createInvoice) {
                if ($subscriptionStatus === 'trialing') {
                    $amount = 0.00;
                    $description = "Initial Trial Subscription: {$plan->name} (14-Day Free Trial)";
                } elseif ($customInvoiceAmount !== null) {
                    $amount = $customInvoiceAmount;
                    $description = "Initial Manual Subscription: {$plan->name} (" . ucfirst($billingInterval) . ")";
                } else {
                    $amount = $billingInterval === 'yearly'
                        ? (float) $plan->yearly_price
                        : (float) $plan->monthly_price;
                    $description = "Initial Subscription: {$plan->name} (" . ucfirst($billingInterval) . ")";
                }

                do {
                    $invoiceNumber = 'INV-' . strtoupper(Str::random(8));
                } while (TenantInvoice::where('invoice_number', $invoiceNumber)->exists());

                $invoice = TenantInvoice::create([
                    'tenant_id' => $tenant->id,
                    'platform_subscription_id' => $subscription->id,
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'billing_interval' => $billingInterval,
                    'description' => $description,
                    'invoice_number' => $invoiceNumber,
                    'stripe_invoice_id' => null,
                    'hosted_invoice_url' => null,
                    'billing_period_start' => $start,
                    'billing_period_end' => $end,
                    'subtotal' => $amount,
                    'tax' => 0.00,
                    'total' => $amount,
                    'currency' => $tenant->currency ?: 'USD',
                    'status' => 'paid',
                    'paid_at' => now(),
                    'pdf_url' => null,
                ]);
            }

            // ------------------------------------------------------------------
            // 6. Initialize Tenant Usage Metrics
            // ------------------------------------------------------------------
            $usage = TenantUsage::create([
                'tenant_id' => $tenant->id,
                'storage_used_bytes' => 0,
                'staff_accounts_count' => 1,
                'products_count' => 0,
            ]);

            return [
                'user' => $user,
                'tenant' => $tenant,
                'domain' => $tenantDomain,
                'subscription' => $subscription,
                'invoice' => $invoice,
                'usage' => $usage,
            ];
        });
    }

    /**
     * Assign / Upgrade manual subscription to an existing tenant.
     * Maintains ONE subscription record per tenant (updated in place) and registers every
     * plan assignment, upgrade, or renewal in tenant_invoices as an immutable billing record.
     */
    public function assignManualSubscription(
        PlatformPlan $plan,
        string $billingInterval = 'monthly',
        string $subscriptionStatus = 'trialing',
        ?Carbon $periodStart = null,
        ?Carbon $periodEnd = null,
        ?Carbon $trialEnd = null,
        bool $createInvoice = true,
        ?float $customInvoiceAmount = null
    ): TenantSubscription {
        return DB::transaction(function () use (
            $plan,
            $billingInterval,
            $subscriptionStatus,
            $periodStart,
            $periodEnd,
            $trialEnd,
            $createInvoice,
            $customInvoiceAmount
        ) {
            $start = $periodStart ? $periodStart->copy() : now();

            if ($subscriptionStatus === 'trialing') {
                $trial = $trialEnd ? $trialEnd->copy() : ($periodEnd ? $periodEnd->copy() : $start->copy()->addDays(14));
                $end = $periodEnd ? $periodEnd->copy() : $trial->copy();
            } else {
                $trial = $trialEnd ? $trialEnd->copy() : null;
                $end = $periodEnd ? $periodEnd->copy() : ($billingInterval === 'yearly' ? $start->copy()->addYear() : $start->copy()->addMonth());
            }

            // ------------------------------------------------------------------
            // 3. Ensure Tenant Domain Entry (*.genzilla.com)
            // ------------------------------------------------------------------
            if ($this->subdomain) {
                $fullSubdomain = strtolower($this->subdomain) . '.' . config('app.base_domain', 'genzilla.com');
                TenantDomain::firstOrCreate(
                    [
                        'tenant_id' => $this->id,
                        'domain' => $fullSubdomain,
                    ],
                    [
                        'is_custom' => false,
                        'dns_verified' => true,
                        'is_primary' => true,
                        'ssl_status' => 'active',
                    ]
                );
            }

            // ------------------------------------------------------------------
            // 4. Resolve Existing Subscription or Create One (1 Subscription Record per Tenant)
            // ------------------------------------------------------------------
            $subscription = $this->currentSubscription
                ?? $this->activeSubscription
                ?? $this->subscriptions()->latest('id')->first();

            $isPlanChange = false;
            $oldPlanName = null;

            if ($subscription) {
                $isPlanChange = ($subscription->plan_id !== $plan->id || $subscription->billing_interval !== $billingInterval);
                $oldPlanName = $subscription->plan?->name ?? 'Previous Plan';

                $subscription->update([
                    'plan_id' => $plan->id,
                    'billing_interval' => $billingInterval,
                    'trial_ends_at' => $trial,
                    'current_period_starts_at' => $start,
                    'current_period_ends_at' => $end,
                    'canceled_at' => $subscriptionStatus === 'cancelled' ? now() : null,
                    'source' => 'manual',
                    'subscription_status' => $subscriptionStatus,
                ]);
            } else {
                $subscription = TenantSubscription::create([
                    'tenant_id' => $this->id,
                    'plan_id' => $plan->id,
                    'billing_interval' => $billingInterval,
                    'trial_ends_at' => $trial,
                    'current_period_starts_at' => $start,
                    'current_period_ends_at' => $end,
                    'canceled_at' => $subscriptionStatus === 'cancelled' ? now() : null,
                    'source' => 'manual',
                    'subscription_status' => $subscriptionStatus,
                ]);
            }

            // Sync subscription FK back to tenant table
            $tenantStatus = in_array($subscriptionStatus, ['trialing', 'active', 'past_due', 'expired', 'cancelled'])
                ? $subscriptionStatus
                : 'expired';

            $this->update([
                'current_subscription_id' => $subscription->id,
                'subscription_status' => $tenantStatus,
            ]);

            // ------------------------------------------------------------------
            // 5. Create Settled Invoice (Billing & Upgrade Ledger)
            // ------------------------------------------------------------------
            if ($createInvoice) {
                if ($subscriptionStatus === 'trialing') {
                    $amount = 0.00;
                    $description = "Trial Subscription: {$plan->name} (14-Day Free Trial)";
                } elseif ($customInvoiceAmount !== null) {
                    $amount = $customInvoiceAmount;
                    $description = $isPlanChange
                        ? "Plan Upgrade/Change: {$oldPlanName} → {$plan->name} (" . ucfirst($billingInterval) . ")"
                        : "Manual Subscription: {$plan->name} (" . ucfirst($billingInterval) . ")";
                } else {
                    $amount = $billingInterval === 'yearly'
                        ? (float) $plan->yearly_price
                        : (float) $plan->monthly_price;
                    $description = $isPlanChange
                        ? "Plan Upgrade/Change: {$oldPlanName} → {$plan->name} (" . ucfirst($billingInterval) . ")"
                        : "Subscription Plan: {$plan->name} (" . ucfirst($billingInterval) . ")";
                }

                do {
                    $invoiceNumber = 'INV-' . strtoupper(Str::random(8));
                } while (TenantInvoice::where('invoice_number', $invoiceNumber)->exists());

                TenantInvoice::create([
                    'tenant_id' => $this->id,
                    'platform_subscription_id' => $subscription->id,
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'billing_interval' => $billingInterval,
                    'description' => $description,
                    'invoice_number' => $invoiceNumber,
                    'stripe_invoice_id' => null,
                    'hosted_invoice_url' => null,
                    'billing_period_start' => $start,
                    'billing_period_end' => $end,
                    'subtotal' => $amount,
                    'tax' => 0.00,
                    'total' => $amount,
                    'currency' => $this->currency ?: 'USD',
                    'status' => 'paid',
                    'paid_at' => now(),
                    'pdf_url' => null,
                ]);
            }

            // ------------------------------------------------------------------
            // 6. Initialize Tenant Usage Metrics
            // ------------------------------------------------------------------
            TenantUsage::firstOrCreate(
                ['tenant_id' => $this->id],
                [
                    'storage_used_bytes' => 0,
                    'staff_accounts_count' => 1,
                    'products_count' => 0,
                ]
            );

            return $subscription;
        });
    }

    /**
     * Update an existing manual subscription directly and issue an invoice if requested.
     */
    public function updateManualSubscription(
        TenantSubscription $subscription,
        PlatformPlan $plan,
        string $billingInterval,
        string $subscriptionStatus,
        ?Carbon $periodStart = null,
        ?Carbon $periodEnd = null,
        ?Carbon $trialEnd = null,
        bool $createInvoice = false,
        ?float $customInvoiceAmount = null
    ): TenantSubscription {
        return DB::transaction(function () use (
            $subscription,
            $plan,
            $billingInterval,
            $subscriptionStatus,
            $periodStart,
            $periodEnd,
            $trialEnd,
            $createInvoice,
            $customInvoiceAmount
        ) {
            $canceledAt = $subscription->canceled_at;
            if ($subscriptionStatus === 'cancelled' && !$canceledAt) {
                $canceledAt = now();
            } elseif ($subscriptionStatus !== 'cancelled') {
                $canceledAt = null;
            }

            $start = $periodStart ? $periodStart->copy() : ($subscription->current_period_starts_at ? $subscription->current_period_starts_at->copy() : now());
            $trial = $trialEnd ? $trialEnd->copy() : ($subscription->trial_ends_at ? $subscription->trial_ends_at->copy() : null);
            $end = $periodEnd ? $periodEnd->copy() : ($subscription->current_period_ends_at ? $subscription->current_period_ends_at->copy() : ($billingInterval === 'yearly' ? $start->copy()->addYear() : $start->copy()->addMonth()));

            // ------------------------------------------------------------------
            // 3. Ensure Tenant Domain Entry (*.genzilla.com)
            // ------------------------------------------------------------------
            if ($this->subdomain) {
                $fullSubdomain = strtolower($this->subdomain) . '.' . config('app.base_domain', 'genzilla.com');
                TenantDomain::firstOrCreate(
                    [
                        'tenant_id' => $this->id,
                        'domain' => $fullSubdomain,
                    ],
                    [
                        'is_custom' => false,
                        'dns_verified' => true,
                        'is_primary' => true,
                        'ssl_status' => 'active',
                    ]
                );
            }

            // ------------------------------------------------------------------
            // 4. Update Tenant Subscription (Single record in place)
            // ------------------------------------------------------------------
            $subscription->update([
                'plan_id' => $plan->id,
                'billing_interval' => $billingInterval,
                'subscription_status' => $subscriptionStatus,
                'trial_ends_at' => $trial,
                'current_period_starts_at' => $start,
                'current_period_ends_at' => $end,
                'canceled_at' => $canceledAt,
            ]);

            // Sync subscription FK back to tenant table
            $tenantStatus = in_array($subscriptionStatus, ['trialing', 'active', 'past_due', 'expired', 'cancelled'])
                ? $subscriptionStatus
                : 'expired';

            $this->update([
                'current_subscription_id' => $subscription->id,
                'subscription_status' => $tenantStatus,
            ]);

            // ------------------------------------------------------------------
            // 5. Create Settled Invoice (if requested)
            // ------------------------------------------------------------------
            if ($createInvoice) {
                if ($subscriptionStatus === 'trialing') {
                    $amount = 0.00;
                    $description = "Subscription Update: {$plan->name} (Trialing)";
                } elseif ($customInvoiceAmount !== null) {
                    $amount = $customInvoiceAmount;
                    $description = "Subscription Update: {$plan->name} (" . ucfirst($billingInterval) . ")";
                } else {
                    $amount = $billingInterval === 'yearly'
                        ? (float) $plan->yearly_price
                        : (float) $plan->monthly_price;
                    $description = "Subscription Update: {$plan->name} (" . ucfirst($billingInterval) . ")";
                }

                do {
                    $invoiceNumber = 'INV-' . strtoupper(Str::random(8));
                } while (TenantInvoice::where('invoice_number', $invoiceNumber)->exists());

                TenantInvoice::create([
                    'tenant_id' => $this->id,
                    'platform_subscription_id' => $subscription->id,
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'billing_interval' => $billingInterval,
                    'description' => $description,
                    'invoice_number' => $invoiceNumber,
                    'stripe_invoice_id' => null,
                    'hosted_invoice_url' => null,
                    'billing_period_start' => $start,
                    'billing_period_end' => $end,
                    'subtotal' => $amount,
                    'tax' => 0.00,
                    'total' => $amount,
                    'currency' => $this->currency ?: 'USD',
                    'status' => 'paid',
                    'paid_at' => now(),
                    'pdf_url' => null,
                ]);
            }

            // ------------------------------------------------------------------
            // 6. Initialize Tenant Usage Metrics if missing
            // ------------------------------------------------------------------
            TenantUsage::firstOrCreate(
                ['tenant_id' => $this->id],
                [
                    'storage_used_bytes' => 0,
                    'staff_accounts_count' => 1,
                    'products_count' => 0,
                ]
            );

            return $subscription;
        });
    }

    public function getLogoUrlAttribute(): ?string
    {
        $path = $this->logo_path;
        if (!$path) {
            $branding = $this->relationLoaded('media')
                ? ($this->media->firstWhere('collection', 'branding') ?? $this->media->first())
                : $this->media()->where('collection', 'branding')->first();
            $path = $branding?->path;
        }

        if (!$path) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://'])
            ? $path
            : asset('storage/' . $path);
    }

    public function getPhoneAttribute(): ?string
    {
        return $this->support_phone ?: ($this->attributes['phone'] ?? null);
    }

    public function getEmailAttribute(): ?string
    {
        return $this->support_email ?: ($this->attributes['email'] ?? null);
    }
}
