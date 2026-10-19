<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantInvoice;
use App\Models\TenantSubscription;
use App\Models\TenantUsage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantSubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {

            // ------------------------------------------------------------------
            // 1. Create Default Users (Platform Admin & Store Owner)
            // ------------------------------------------------------------------
            $adminUser = User::create([
                'name' => 'Platform Admin',
                'email' => 'admin@genzilla.com',
                'password' => Hash::make('password'),
                'status' => 1,
            ]);

            $merchantUser = User::create([
                'name' => 'Dr. John Doe',
                'email' => 'john@medicarepharma.com',
                'password' => Hash::make('password'),
                'status' => 1,
            ]);


            // ------------------------------------------------------------------
            // 3. Provision Tenant (Storefront)
            // ------------------------------------------------------------------
            $tenant = Tenant::create([
                'user_id' => $merchantUser->id,
                'store_name' => 'MediCare Pharmacy',
                'support_email' => 'support@medicarepharma.com',
                'support_phone' => '+1 800 555 0199',
                'subdomain' => 'medicare',
                'custom_domain' => null,
                'currency' => 'USD',
                'timezone' => 'UTC',
                'subscription_status' => 'trialing',
                'status' => 'active',
            ]);

            // 3. Create Tenant Domain Entry (*.genzilla.com)
            $fullSubdomain = strtolower($tenant->subdomain) . '.' . config('app.base_domain', 'genzilla.com');

            $tenantDomain = TenantDomain::create([
                'tenant_id' => $tenant->id,
                'domain' => $fullSubdomain,
                'is_custom' => false, // Primary platform subdomain
                'dns_verified' => true,
                'is_primary' => true,
            ]);

            // ------------------------------------------------------------------
            // 4. Create Tenant Subscription (14-Day Free Manual Trial)
            // ------------------------------------------------------------------
            $trialStart = now();
            $trialEnd = now()->addDays(14);

            $subscription = TenantSubscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => 1,
                'billing_interval' => 'monthly',
                'trial_ends_at' => $trialEnd,
                'current_period_starts_at' => $trialStart,
                'current_period_ends_at' => $trialEnd,
                'canceled_at' => null,
                'source' => 'manual',
                'subscription_status' => 'trialing',
            ]);

            // Sync subscription FK back to tenant table
            $tenant->update([
                'current_subscription_id' => $subscription->id,
            ]);

            // ------------------------------------------------------------------
            // 5. Create Initial $0.00 Settled Trial Invoice
            // ------------------------------------------------------------------
            TenantInvoice::create([
                'tenant_id' => $tenant->id,
                'platform_subscription_id' => $subscription->id,
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'stripe_invoice_id' => null,
                'hosted_invoice_url' => null,
                'billing_period_start' => $trialStart,
                'billing_period_end' => $trialEnd,
                'subtotal' => 0.00,
                'tax' => 0.00,
                'total' => 0.00,
                'currency' => 'USD',
                'status' => 'paid',
                'paid_at' => $trialStart,
                'pdf_url' => null,
            ]);

            // ------------------------------------------------------------------
            // 6. Initialize Tenant Usage Metrics
            // ------------------------------------------------------------------
            TenantUsage::create([
                'tenant_id' => $tenant->id,
                'storage_used_bytes' => 0,
                'staff_accounts_count' => 1, // Store owner counts as initial staff
                'products_count' => 0,
            ]);

        });
    }
}
