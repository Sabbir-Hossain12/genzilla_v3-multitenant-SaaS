<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Slider;
use App\Models\Tenant;
use App\Models\TenantPage;
use Illuminate\Database\Seeder;

class TenantContentSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        if (!$tenant) {
            return;
        }

        $tenantId = $tenant->id;

        // 1. Seed Tenant Pages (as linked in Footer.vue)
        $pages = [
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '<h2>Privacy Policy</h2><p>Your privacy is critically important to us. This policy details how we collect, protect, and process your personal and medical information in accordance with national healthcare compliance standards.</p>',
                'meta_title' => 'Privacy Policy - Customer Data Protection',
                'meta_description' => 'Learn how MediMart protects your health data and personal privacy.',
                'status' => true,
                'is_system' => true,
            ],
            [
                'title' => 'Terms & Conditions',
                'slug' => 'terms-conditions',
                'content' => '<h2>Terms and Conditions of Use</h2><p>Please read these terms and conditions carefully before ordering prescription medications, wellness products, or lab testing packages through our portal.</p>',
                'meta_title' => 'Terms & Conditions - Online Pharmacy Rules',
                'meta_description' => 'Legal terms and conditions governing purchases and telehealth consultations.',
                'status' => true,
                'is_system' => true,
            ],
            [
                'title' => 'Return & Refund Policy',
                'slug' => 'return-refund',
                'content' => '<h2>Return and Refund Policy</h2><p>Unopened OTC products and medical devices may be returned within 7 days of delivery. Prescription medications require verification prior to replacement due to strict health regulations.</p>',
                'meta_title' => 'Return & Refund Guidelines',
                'meta_description' => 'Our 7-day hassle-free return and instant refund policy for eligible healthcare goods.',
                'status' => true,
                'is_system' => true,
            ],
            [
                'title' => 'Careers',
                'slug' => 'careers',
                'content' => '<h2>Join Our Healthcare Team</h2><p>We are actively looking for certified pharmacists, logistics heroes, and software developers to build the future of accessible healthcare in Bangladesh.</p>',
                'meta_title' => 'Careers at MediMart',
                'meta_description' => 'Explore current openings and careers at Bangladesh’s leading digital pharmacy.',
                'status' => true,
                'is_system' => false,
            ],
            [
                'title' => 'Frequently Asked Questions',
                'slug' => 'faq',
                'content' => '<h2>Frequently Asked Questions</h2><p>Find quick answers about doctor prescriptions, delivery timelines, payment options, and diagnostic sample collections.</p>',
                'meta_title' => 'Help & FAQ - MediMart Support',
                'meta_description' => 'Answers to common questions regarding delivery, prescriptions, and lab tests.',
                'status' => true,
                'is_system' => false,
            ],
        ];

        foreach ($pages as $p) {
            TenantPage::updateOrCreate(
                ['tenant_id' => $tenantId, 'slug' => $p['slug']],
                $p
            );
        }

        // 2. Seed Sliders (Direct reference from store/index.js & BannerSlider.vue)
        $slides = [
            [
                'title' => 'Medicine Flash Sale',
                'subtitle' => 'Save up to 30% on everyday essentials and chronic care medications',
                'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMFwvMTEzLVdFQl9CQU5ORVItMS01YXlqa3YucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
                'alt' => 'Medicine flash sale',
                'link' => '/catalog/products',
                'button_text' => 'Order Now',
                'sort_order' => 1,
                'status' => true,
            ],
            [
                'title' => 'Storewide Health & Wellness',
                'subtitle' => 'Authentic vitamins, skincare and daily supplements delivered in 4 hours',
                'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMFwvMTEzLVNIT1JPVC1XRUItQkFOTkVSLTBnOXhnai5wbmciLCJlZGl0cyI6W119&w=1280&q=75',
                'alt' => 'Store wide banner',
                'link' => '/catalog/products',
                'button_text' => 'Explore Deals',
                'sort_order' => 2,
                'status' => true,
            ],
            [
                'title' => 'BOGO Healthcare Specials',
                'subtitle' => 'Buy One Get One offers on selected diagnostic and maternal care supplies',
                'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMFwvMTEzLUJPR09CSEFOREFSX1dFQl9CQU5ORVIyMi1mc2RrNWMucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
                'alt' => 'Health and wellness banner',
                'link' => '/catalog/products',
                'button_text' => 'View Specials',
                'sort_order' => 3,
                'status' => true,
            ],
        ];

        foreach ($slides as $slide) {
            Slider::updateOrCreate(
                ['tenant_id' => $tenantId, 'title' => $slide['title']],
                $slide
            );
        }

        // 3. Seed Promotional Banners (Direct reference from promos in home.vue)
        $banners = [
            [
                'name' => 'Home Lab Tests Promo',
                'eyebrow' => 'Limited Time',
                'title' => 'Lab Tests at Home',
                'subtitle' => 'Certified collection team directly at your doorstep',
                'cta_text' => 'Book Now →',
                'link' => '/services/lab-test',
                'cta_color' => 'text-teal-600',
                'gradient' => 'from-teal-500 to-cyan-600',
                'image' => 'https://loremflickr.com/300/300/laboratory,testtube?lock=304',
                'position' => 'promo_grid',
                'sort_order' => 1,
                'status' => true,
            ],
            [
                'name' => 'Online Doctor Consultation Promo',
                'eyebrow' => 'Online Consultation',
                'title' => 'Talk to a Doctor',
                'subtitle' => 'BMDC registered physicians available 24/7',
                'cta_text' => 'Consult Now →',
                'link' => '/services/doctor-consultation',
                'cta_color' => 'text-violet-600',
                'gradient' => 'from-violet-500 to-purple-600',
                'image' => 'https://loremflickr.com/300/300/doctor,healthcare?lock=305',
                'position' => 'promo_grid',
                'sort_order' => 2,
                'status' => true,
            ],
            [
                'name' => 'Mobile App Download Promo',
                'eyebrow' => 'Download App',
                'title' => 'Order Medicine Anytime, Anywhere',
                'subtitle' => 'Join 1M+ customers who trust our verified medication delivery service',
                'cta_text' => 'Get Mobile App',
                'link' => '#',
                'cta_color' => 'text-gray-900',
                'gradient' => 'from-gray-900 to-gray-800',
                'image' => null,
                'position' => 'app_download',
                'sort_order' => 1,
                'status' => true,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::updateOrCreate(
                ['tenant_id' => $tenantId, 'name' => $banner['name']],
                $banner
            );
        }
    }
}
