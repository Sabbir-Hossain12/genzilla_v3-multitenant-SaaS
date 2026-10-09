<?php

namespace Database\Seeders;

use App\Models\PlatformFaq;
use App\Models\PlatformFeature;
use App\Models\PlatformHero;
use App\Models\PlatformMatrix;
use App\Models\PlatformPage;
use App\Models\PlatformPlan;
use App\Models\PlatformSetting;
use App\Models\PlatformStep;
use App\Models\PlatformUseCase;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Platform Settings
        PlatformSetting::updateOrCreate(['id' => 1], [
            'platform_name' => 'GenZilla',
            'tagline' => 'Next-Gen Multi-Tenant E-Commerce Platform',
            'short_desc' => 'Launch your online store in seconds with built-in AI, custom domain support, and global multi-currency payment processing.',
            'default_currency' => 'USD',
            'email' => 'support@genzilla.io',
            'phone_1' => '+1 (800) 555-0199',
            'company_address' => '100 Innovation Way, Suite 400, San Francisco, CA 94105',
            'working_hours' => 'Mon - Fri: 9:00 AM - 6:00 PM PST',
            'meta_title' => 'GenZilla - Create & Scale Your SaaS Storefront',
            'meta_description' => 'All-in-one e-commerce SaaS platform powering next-generation merchants globally.',
            'meta_keywords' => 'ecommerce, saas, multi-tenant, online store builder, stripe',
            'meta_robots' => 'index, follow',
            'copyright_text' => '© ' . date('Y') . ' GenZilla. All rights reserved.',
        ]);

        // 2. Platform Hero
        PlatformHero::updateOrCreate(['id' => 1], [
            'ai_product_desc' => 'Generate high-converting storefronts & copy instantly using integrated AI engines.',
            'title' => 'Build, Launch, & Scale Your Global Store in Minutes',
            'short_desc' => 'The ultimate multi-tenant e-commerce platform equipped with automated domain management, robust subscription billing, and real-time analytics.',
            'btn_1_text' => 'Start Free Trial',
            'btn_1_link' => '/register',
            'btn_2_text' => 'Explore Demos',
            'btn_2_link' => '#demos',
        ]);

        // 3. Platform Matrix
        PlatformMatrix::updateOrCreate(['id' => 1], [
            'active_merchant' => 1250,
            'gmv_proccessed' => 15500000.00,
            'countries_served' => 45,
            'platform_uptime' => '99.99%',
        ]);

        // 4. Platform Features
        $features = [
            [
                'title' => 'Instant Subdomains & Custom Domains',
                'short_desc' => 'Automatic Caddy SSL provisioning and custom CNAME routing for seamless brand identity.',
                'sort_order' => 1,
            ],
            [
                'title' => 'Multi-Currency & Global Payments',
                'short_desc' => 'Accept payments worldwide via native Stripe Connect and localized checkout flows.',
                'sort_order' => 2,
            ],
            [
                'title' => 'Advanced Role-Based Access Control',
                'short_desc' => 'Fine-grained permissions and staff management tailored to your store operations.',
                'sort_order' => 3,
            ],
        ];
        foreach ($features as $feature) {
            PlatformFeature::updateOrCreate(['title' => $feature['title']], $feature + ['status' => 1]);
        }

        // 5. Platform Steps
        $steps = [
            [
                'title' => 'Create Your Account',
                'short_desc' => 'Sign up in under 60 seconds and pick your unique store subdomain.',
                'sort_order' => 1,
            ],
            [
                'title' => 'Customize Your Storefront',
                'short_desc' => 'Upload products, set up payment methods, and configure custom branding.',
                'sort_order' => 2,
            ],
            [
                'title' => 'Start Selling Globally',
                'short_desc' => 'Connect your custom domain and begin processing orders immediately.',
                'sort_order' => 3,
            ],
        ];
        foreach ($steps as $step) {
            PlatformStep::updateOrCreate(['title' => $step['title']], $step + ['status' => 1]);
        }

        // 6. Platform Plans
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'monthly_price' => 29.00,
                'yearly_price' => 290.00,
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
                'max_products' => 200,
                'max_staff_accounts' => 5,
                'max_storage_mb' => 500,
                'max_digital_file_size_mb' => 50,
                'allow_custom_domain' => true,
                'has_multi_currency' => true,
                'max_currencies_supported' => 3,
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
                'max_products' => 1000,
                'max_staff_accounts' => 20,
                'max_storage_mb' => 2000,
                'max_digital_file_size_mb' => 200,
                'allow_custom_domain' => true,
                'has_multi_currency' => true,
                'max_currencies_supported' => 10,
                'has_advanced_reporting' => true,
                'has_custom_rbac' => true,
                'has_audit_logs' => true,
                'status' => 1,
            ],
        ];
        foreach ($plans as $plan) {
            PlatformPlan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }

        // 7. Platform Use Cases
        $useCases = [
            [
                'title' => 'Digital Products & SaaS Downloads',
                'badge_label' => 'Popular',
                'badge_type' => 'success',
                'description' => 'Sell e-books, software, digital assets, and media with strict file size limits and instant download links.',
                'bg_color' => '#f0fdf4',
                'sort_order' => 1,
                'status' => 1,
            ],
            [
                'title' => 'Physical Goods & Boutique E-Commerce',
                'badge_label' => 'Retail',
                'badge_type' => 'info',
                'description' => 'Complete inventory management, variant tracking, shipping calculations, and order management.',
                'bg_color' => '#eff6ff',
                'sort_order' => 2,
                'status' => 1,
            ],
        ];
        foreach ($useCases as $useCase) {
            PlatformUseCase::updateOrCreate(['title' => $useCase['title']], $useCase);
        }

        // 8. Platform FAQs
        $faqs = [
            [
                'question' => 'How long is the free trial?',
                'answer' => 'Every new merchant gets a 14-day fully-featured free trial with no credit card required upfront.',
                'sort_order' => 1,
                'status' => 1,
            ],
            [
                'question' => 'Can I connect my own custom domain?',
                'answer' => 'Yes! Custom domain support is available on our Growth and Enterprise plans with automated SSL cert issuance.',
                'sort_order' => 2,
                'status' => 1,
            ],
        ];
        foreach ($faqs as $faq) {
            PlatformFaq::updateOrCreate(['question' => $faq['question']], $faq);
        }

        // 9. Platform Pages
        $pages = [
            [
                'slug' => 'terms-of-service',
                'title' => 'Terms of Service',
                'content' => '<h1>Terms of Service</h1><p>Welcome to GenZilla. By using our services, you agree to the following terms...</p>',
                'meta_title' => 'Terms of Service - GenZilla',
                'meta_description' => 'GenZilla Terms of Service and user agreement.',
                'status' => 1,
            ],
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'content' => '<h1>Privacy Policy</h1><p>Your privacy is important to us. Read how we handle your personal data and store security...</p>',
                'meta_title' => 'Privacy Policy - GenZilla',
                'meta_description' => 'GenZilla Privacy Policy and data protection details.',
                'status' => 1,
            ],
        ];
        foreach ($pages as $page) {
            PlatformPage::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }
}
