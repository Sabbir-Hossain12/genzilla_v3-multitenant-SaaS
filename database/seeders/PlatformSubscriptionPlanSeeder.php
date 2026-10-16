<?php

namespace Database\Seeders;

use App\Models\PlatformPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlatformSubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'monthly_price' => 29.00,
                'yearly_price' => 290.00,
                'stripe_monthly_price_id' => null, // Replace with your Stripe Price ID
                'stripe_yearly_price_id' => null,  // Replace with your Stripe Price ID
                'max_products' => 50,
                'max_staff_accounts' => 1,
                'max_storage_mb' => 100,
                'max_digital_file_size_mb' => 10,
                'allow_custom_domain' => false,
                'has_multi_currency' => false,
                'max_currencies_supported' => 1,
                'has_advanced_reporting' => false,
                'has_custom_rbac' => false,
                'has_audit_logs' => false,
                'status' => 1,
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'monthly_price' => 79.00,
                'yearly_price' => 790.00,
                'stripe_monthly_price_id' => null, // Replace with your Stripe Price ID
                'stripe_yearly_price_id' => null,  // Replace with your Stripe Price ID
                'max_products' => 200,
                'max_staff_accounts' => 5,
                'max_storage_mb' => 500,
                'max_digital_file_size_mb' => 50,
                'allow_custom_domain' => true,
                'has_multi_currency' => false,
                'max_currencies_supported' => 1,
                'has_advanced_reporting' => true,
                'has_custom_rbac' => false,
                'has_audit_logs' => false,
                'status' => 1,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'monthly_price' => 199.00,
                'yearly_price' => 1990.00,
                'stripe_monthly_price_id' => null, // Replace with your Stripe Price ID
                'stripe_yearly_price_id' => null,  // Replace with your Stripe Price ID
                'max_products' => 1000,
                'max_staff_accounts' => 20,
                'max_storage_mb' => 2000,
                'max_digital_file_size_mb' => 200,
                'allow_custom_domain' => true,
                'has_multi_currency' => false,
                'max_currencies_supported' => 1,
                'has_advanced_reporting' => true,
                'has_custom_rbac' => true,
                'has_audit_logs' => true,
                'status' => 1,
            ],
        ];

        foreach ($plans as$plan) {
            PlatformPlan::updateOrCreate(['slug' => $plan['slug']],$plan);
        }
    }
}
