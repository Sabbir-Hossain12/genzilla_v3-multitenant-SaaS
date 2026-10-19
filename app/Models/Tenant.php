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

class Tenant extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'theme_settings' => 'array',
        'stripeOnboarded' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class, 'tenant_id');
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
            // 4. Create Tenant Subscription
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
            // 5. Create Initial Settled Invoice
            // ------------------------------------------------------------------
            $invoice = null;
            if ($createInvoice) {
                if ($subscriptionStatus === 'trialing') {
                    $amount = 0.00;
                } elseif ($customInvoiceAmount !== null) {
                    $amount = $customInvoiceAmount;
                } else {
                    $amount = $billingInterval === 'yearly'
                        ? (float) $plan->yearly_price
                        : (float) $plan->monthly_price;
                }

                do {
                    $invoiceNumber = 'INV-' . strtoupper(Str::random(8));
                } while (TenantInvoice::where('invoice_number', $invoiceNumber)->exists());

                $invoice = TenantInvoice::create([
                    'tenant_id' => $tenant->id,
                    'platform_subscription_id' => $subscription->id,
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
     * Assign a manual subscription to an existing tenant following the TenantSubscriptionSeeder sequence.
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

            // Supersede existing active or trialing subscriptions
            $this->subscriptions()
                ->whereIn('subscription_status', ['active', 'trialing'])
                ->update(['subscription_status' => 'superseded']);

            // ------------------------------------------------------------------
            // 4. Create Tenant Subscription
            // ------------------------------------------------------------------
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

            // Sync subscription FK back to tenant table
            $tenantStatus = in_array($subscriptionStatus, ['trialing', 'active', 'past_due', 'expired', 'cancelled'])
                ? $subscriptionStatus
                : 'expired';

            $this->update([
                'current_subscription_id' => $subscription->id,
                'subscription_status' => $tenantStatus,
            ]);

            // ------------------------------------------------------------------
            // 5. Create Initial Settled Invoice
            // ------------------------------------------------------------------
            if ($createInvoice) {
                if ($subscriptionStatus === 'trialing') {
                    $amount = 0.00;
                } elseif ($customInvoiceAmount !== null) {
                    $amount = $customInvoiceAmount;
                } else {
                    $amount = $billingInterval === 'yearly'
                        ? (float) $plan->yearly_price
                        : (float) $plan->monthly_price;
                }

                do {
                    $invoiceNumber = 'INV-' . strtoupper(Str::random(8));
                } while (TenantInvoice::where('invoice_number', $invoiceNumber)->exists());

                TenantInvoice::create([
                    'tenant_id' => $this->id,
                    'platform_subscription_id' => $subscription->id,
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
     * Update a manual subscription following the TenantSubscriptionSeeder sequence.
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
            // 4. Update Tenant Subscription
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
                } elseif ($customInvoiceAmount !== null) {
                    $amount = $customInvoiceAmount;
                } else {
                    $amount = $billingInterval === 'yearly'
                        ? (float) $plan->yearly_price
                        : (float) $plan->monthly_price;
                }

                do {
                    $invoiceNumber = 'INV-' . strtoupper(Str::random(8));
                } while (TenantInvoice::where('invoice_number', $invoiceNumber)->exists());

                TenantInvoice::create([
                    'tenant_id' => $this->id,
                    'platform_subscription_id' => $subscription->id,
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
}
