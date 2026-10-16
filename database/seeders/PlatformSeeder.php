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
            'tagline' => 'Next-Gen Multi-Tenant HealthTech & Pharmacy Platform',
            'short_desc' => 'Launch your compliant online pharmacy, medical store, or healthcare portal in seconds with automated prescription verification, custom domain routing, and multi-currency billing.',
            'default_currency' => 'USD',
            'email' => 'support@genzilla.io',
            'phone_1' => '+1 (800) 555-0199',
            'company_address' => '100 Innovation Way, Suite 400, San Francisco, CA 94105',
            'working_hours' => 'Mon - Fri: 9:00 AM - 6:00 PM PST',
            'meta_title' => 'GenZilla - Create & Scale Your HealthTech E-Commerce Storefront',
            'meta_description' => 'All-in-one multi-tenant e-commerce platform powering next-generation online pharmacies and healthcare merchants globally.',
            'meta_keywords' => 'healthtech, pharmacy ecommerce, saas, multi-tenant, online medicine store, telemedicine, stripe',
            'meta_robots' => 'index, follow',
            'copyright_text' => '© ' . date('Y') . ' GenZilla. All rights reserved.',
        ]);

        // 2. Platform Hero
        PlatformHero::updateOrCreate(['id' => 1], [
            'ai_product_desc' => 'Generate active ingredient guides, compliant dosage copy & medicine categories automatically with integrated AI engines.',
            'title' => 'Build, Launch, & Scale Your Healthcare Storefront in Minutes',
            'short_desc' => 'The ultimate multi-tenant e-commerce platform built for online pharmacies, healthcare suppliers, and diagnostic labs—equipped with prescription uploads, custom domain routing, and compliance features.',
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
                'title' => 'Prescription Upload & Rx Compliance Loops',
                'short_desc' => 'Streamlined verification flows allowing licensed pharmacists to audit customer prescriptions before order fulfillment.',
                'sort_order' => 1,
            ],
            [
                'title' => 'Custom Domains & Automated SSL',
                'short_desc' => 'Automatic Caddy SSL provisioning and CNAME routing to give your pharmacy brand local trust.',
                'sort_order' => 2,
            ],
            [
                'title' => 'Role-Based Access Control & Pharmacist Permissions',
                'short_desc' => 'Granular RBAC to segregate access between store managers, inventory clerks, and licensed verification staff.',
                'sort_order' => 3,
            ],
        ];
        foreach ($features as$feature) {
            PlatformFeature::updateOrCreate(['title' => $feature['title']],$feature + ['status' => 1]);
        }

        // 5. Platform Steps
        $steps = [
            [
                'title' => 'Register Your Healthcare Brand',
                'short_desc' => 'Sign up in under 60 seconds and claim your unique online pharmacy subdomain.',
                'sort_order' => 1,
            ],
            [
                'title' => 'Configure Inventory & Rx Rules',
                'short_desc' => 'Add OTC products, medical SKUs, set up prescription requirements, and configure payment methods.',
                'sort_order' => 2,
            ],
            [
                'title' => 'Start Processing Orders Globally',
                'short_desc' => 'Connect your custom domain, accept multi-currency payments, and manage fulfillment seamlessly.',
                'sort_order' => 3,
            ],
        ];
        foreach ($steps as$step) {
            PlatformStep::updateOrCreate(['title' => $step['title']],$step + ['status' => 1]);
        }



        // 7. Platform Use Cases
        $useCases = [
            [
                'title' => 'Digital Pharmacy & Prescription Fulfillment',
                'badge_label' => 'Popular',
                'badge_type' => 'success',
                'description' => 'Sell prescription meds, OTC pharmaceuticals, and health supplements with active ingredient lookup and secure prescription attachment management.',
                'bg_color' => '#f0fdf4',
                'sort_order' => 1,
                'status' => 1,
            ],
            [
                'title' => 'Medical Equipment & Diagnostic Test Services',
                'badge_label' => 'Healthcare',
                'badge_type' => 'info',
                'description' => 'Manage specialized medical devices, wellness products, and home sample collection test bookings with custom role permissions.',
                'bg_color' => '#eff6ff',
                'sort_order' => 2,
                'status' => 1,
            ],
        ];
        foreach ($useCases as$useCase) {
            PlatformUseCase::updateOrCreate(['title' => $useCase['title']],$useCase);
        }

        // 8. Platform FAQs
        $faqs = [
            [
                'question' => 'How long is the free trial?',
                'answer' => 'Every new healthcare merchant gets a 14-day fully-featured free trial with no credit card required upfront.',
                'sort_order' => 1,
                'status' => 1,
            ],
            [
                'question' => 'Can I connect a custom domain for my pharmacy?',
                'answer' => 'Yes! Custom domain support is available on our Growth and Enterprise plans with automated SSL certificate issuance.',
                'sort_order' => 2,
                'status' => 1,
            ],
            [
                'question' => 'How are staff roles managed for audit compliance?',
                'answer' => 'Enterprise plans feature custom RBAC and detailed audit logs (`tenant_audit_logs`) to track medicine inventory changes and staff operations.',
                'sort_order' => 3,
                'status' => 1,
            ],
        ];
        foreach ($faqs as$faq) {
            PlatformFaq::updateOrCreate(['question' => $faq['question']],$faq);
        }

        // 9. Platform Pages
        $pages = [
            [
                'slug' => 'terms-of-service',
                'title' => 'Terms of Service',
                'content' => '<h1>Terms of Service</h1><p>Welcome to GenZilla. By using our multi-tenant HealthTech e-commerce platform, you agree to the following terms and medical merchant compliance standards...</p>',
                'meta_title' => 'Terms of Service - GenZilla',
                'meta_description' => 'GenZilla Terms of Service, compliance standards, and user agreement.',
                'status' => 1,
            ],
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'content' => '<h1>Privacy Policy</h1><p>Your privacy and healthcare data isolation are paramount to us. Read how we handle store security, audit trails, and multi-tenant isolation...</p>',
                'meta_title' => 'Privacy Policy - GenZilla',
                'meta_description' => 'GenZilla Privacy Policy and health data protection details.',
                'status' => 1,
            ],
        ];
        foreach ($pages as$page) {
            PlatformPage::updateOrCreate(['slug' => $page['slug']],$page);
        }
    }
}
