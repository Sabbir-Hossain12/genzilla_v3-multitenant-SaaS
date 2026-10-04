<?php

namespace Database\Seeders;

use App\Models\BasicInfo;
use Illuminate\Database\Seeder;

class BasicInfoSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        BasicInfo::updateOrCreate(['id' => 1], [
            'black_logo' => 'public/backend/images/logo/1722452305Screenshot_2024-08-01_005602-removebg-preview (1).png',
            'light_logo' => 'public/backend/images/logo/1722452305Screenshot_2024-08-01_005602-removebg-preview (1).png',
            'email' => 'admin@admin.com',
            'platform_name' => 'Genzilla_v3',
            'phone_1' => '+8801700000000',
            'fb_link' => 'https://facebook.com/',
            'x_link' => 'https://x.com/',
            'p_link' => 'https://pinterest.com/',
            'youtube_link' => 'https://youtube.com/',
            'insta_link' => 'https://instagram.com/',
            'inside_dhaka_charge' => 60,
            'outside_dhaka_charge' => 120,
            'store_location' => 'Dhaka, Bangladesh',
            'short_desc' => 'Your short store description goes here.',
            'currency_symbol' => '৳',
            'fb_pixel' => null,
            'google_analytics' => null,
            'chatbox_script' => null,
            'marquee_text' => 'Welcome to our store!',
        ]);
    }
}
