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
            'black_logo' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQRLHWSou-grk0QEK3HWwYQG4cW5--XCihByIjGvsIv0WR9EWh9ao_yN5wK&s=10',
            'light_logo' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQRLHWSou-grk0QEK3HWwYQG4cW5--XCihByIjGvsIv0WR9EWh9ao_yN5wK&s=10',
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
