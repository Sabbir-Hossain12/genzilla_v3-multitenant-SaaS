<?php

namespace Database\Seeders;

use App\Models\PlatformStoreDemo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PlatformStoreDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demos = $this->storeDemos();

        foreach ($demos as $demo) {
            // Generate placeholder image if image file does not exist yet on disk
            if (! empty($demo['image'])) {
                $this->ensureImageExists(
                    $demo['image'],
                    $demo['title'],
                    $demo['category'] ?? 'Healthcare Store',
                    $demo['theme_colors'] ?? ['#1e293b', '#3b82f6', '#f8fafc']
                );
            }

            PlatformStoreDemo::updateOrCreate(
                ['slug' => $demo['slug']],
                [
                    'title' => $demo['title'],
                    'image' => $demo['image'] ?? null,
                    'link' => $demo['link'] ?? null,
                    'status' => $demo['status'] ?? 1,
                    'sort_order' => $demo['sort_order'] ?? 0,
                ]
            );
        }
    }

    /**
     * Demo store definitions (8 rows).
     *
     * @return array<int, array<string, mixed>>
     */
    private function storeDemos(): array
    {
        return [
            [
                'slug' => 'healthcare-ecommerce',
                'title' => 'Healthcare Ecommerce Platform',
                'image' => 'store_demos/0p3ej7ZTDaZAPqbHSsz93qvgjHzSZU4Dj4pCsxJL.png',
                'link' => '/store',
                'status' => 1,
                'sort_order' => 1,
                'category' => 'Flagship Pharmacy',
                'theme_colors' => ['#0f172a', '#2563eb', '#f1f5f9'],
            ],
            [
                'slug' => 'pharmacare-rx-dispensary',
                'title' => 'PharmaCare Rx & OTC Dispensary',
                'image' => 'store_demos/fnnJSoxlXpRdoRnX7y1ik2LThtqVWzcDuhYHQ36W.png',
                'link' => '/store',
                'status' => 1,
                'sort_order' => 2,
                'category' => 'Prescription Medicine',
                'theme_colors' => ['#0c4a6e', '#0284c7', '#f0f9ff'],
            ],
            [
                'slug' => 'vitalis-health-wellness',
                'title' => 'Vitalis Health & Wellness Co.',
                'image' => 'store_demos/vitalis-health-wellness.png',
                'link' => '/store',
                'status' => 1,
                'sort_order' => 3,
                'category' => 'Organic & Wellness',
                'theme_colors' => ['#064e3b', '#10b981', '#ecfdf5'],
            ],
            [
                'slug' => 'curalab-diagnostics',
                'title' => 'CuraLab Diagnostics & Rapid Test Kits',
                'image' => 'store_demos/curalab-diagnostics.png',
                'link' => '/store',
                'status' => 1,
                'sort_order' => 4,
                'category' => 'Diagnostic Testing',
                'theme_colors' => ['#1e1b4b', '#6366f1', '#eef2ff'],
            ],
            [
                'slug' => 'apex-surgical-supplies',
                'title' => 'Apex Surgical & Medical Supplies',
                'image' => 'store_demos/apex-surgical-supplies.png',
                'link' => '/store',
                'status' => 1,
                'sort_order' => 5,
                'category' => 'Medical Equipment',
                'theme_colors' => ['#1e293b', '#0ea5e9', '#f0fdf4'],
            ],
            [
                'slug' => 'dermaglow-clinical-skincare',
                'title' => 'DermaGlow Clinical Skincare',
                'image' => 'store_demos/dermaglow-clinical-skincare.png',
                'link' => '/store',
                'status' => 1,
                'sort_order' => 6,
                'category' => 'Cosmeceuticals & Skincare',
                'theme_colors' => ['#881337', '#f43f5e', '#fff1f2'],
            ],
            [
                'slug' => 'optivision-eyecare-optics',
                'title' => 'OptiVision Eyecare & Optical Lab',
                'image' => 'store_demos/optivision-eyecare-optics.png',
                'link' => '/store',
                'status' => 1,
                'sort_order' => 7,
                'category' => 'Eyecare & Prescription Optics',
                'theme_colors' => ['#4c1d95', '#8b5cf6', '#faf5ff'],
            ],
            [
                'slug' => 'nutripulse-sports-nutrition',
                'title' => 'NutriPulse Sports Nutrition',
                'image' => 'store_demos/nutripulse-sports-nutrition.png',
                'link' => '/store',
                'status' => 1,
                'sort_order' => 8,
                'category' => 'Sports & Dietary Supplements',
                'theme_colors' => ['#7c2d12', '#f97316', '#fff7ed'],
            ],
        ];
    }

    /**
     * Ensure a mock preview graphic exists on disk so the store demo card displays cleanly.
     *
     * @param  array<int, string>  $colors  [headerBg, accent, cardBg]
     */
    private function ensureImageExists(string $relativePath, string $title, string $category, array $colors): void
    {
        $diskPath = storage_path('app/public/' . $relativePath);

        if (File::exists($diskPath)) {
            return;
        }

        File::ensureDirectoryExists(dirname($diskPath));

        if (! extension_loaded('gd')) {
            return;
        }

        $width = 800;
        $height = 600;
        $im = imagecreatetruecolor($width, $height);

        // Convert hex colors to RGB
        [$r1, $g1, $b1] = $this->hexToRgb($colors[0] ?? '#1e293b');
        [$r2, $g2, $b2] = $this->hexToRgb($colors[1] ?? '#3b82f6');
        [$r3, $g3, $b3] = $this->hexToRgb($colors[2] ?? '#f8fafc');

        $bgColor = imagecolorallocate($im, $r3, $g3, $b3);
        $headerColor = imagecolorallocate($im, $r1, $g1, $b1);
        $accentColor = imagecolorallocate($im, $r2, $g2, $b2);
        $chromeBg = imagecolorallocate($im, 226, 232, 240);
        $chromeUrlBg = imagecolorallocate($im, 255, 255, 255);
        $white = imagecolorallocate($im, 255, 255, 255);
        $grayText = imagecolorallocate($im, 100, 116, 139);
        $darkText = imagecolorallocate($im, 30, 41, 59);
        $cardBorder = imagecolorallocate($im, 226, 232, 240);
        $dotRed = imagecolorallocate($im, 239, 68, 68);
        $dotYellow = imagecolorallocate($im, 245, 158, 11);
        $dotGreen = imagecolorallocate($im, 16, 185, 129);

        // Canvas background
        imagefill($im, 0, 0, $bgColor);

        // 1. Browser Chrome Header Bar (top 44px)
        imagefilledrectangle($im, 0, 0, $width, 44, $chromeBg);
        imagefilledellipse($im, 24, 22, 12, 12, $dotRed);
        imagefilledellipse($im, 44, 22, 12, 12, $dotYellow);
        imagefilledellipse($im, 64, 22, 12, 12, $dotGreen);

        // Address bar
        imagefilledrectangle($im, 100, 8, $width - 40, 36, $chromeUrlBg);
        imagerectangle($im, 100, 8, $width - 40, 36, $cardBorder);
        imagestring($im, 3, 120, 15, 'https://demo.genzilla.io/storefront/' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $category)), $grayText);

        // 2. Hero Storefront Banner (44px to 220px)
        imagefilledrectangle($im, 0, 44, $width, 220, $headerColor);

        // Category Badge
        imagefilledrectangle($im, 40, 68, 40 + (strlen($category) * 8) + 16, 92, $accentColor);
        imagestring($im, 3, 48, 73, strtoupper($category), $white);

        // Store Title
        imagestring($im, 5, 40, 110, $title, $white);
        imagestring($im, 4, 40, 140, 'Official Online Pharmacy & Medical Storefront Demo', $accentColor);
        imagestring($im, 3, 40, 172, 'Rx Verification  *  Multi-Currency Billing  *  Same-Day Fulfillment', $white);

        // 3. Product Cards Grid (3 mockup cards)
        $cardWidth = 220;
        $cardHeight = 310;
        $gap = 26;
        $startX = 40;
        $cardY = 245;

        for ($i = 0; $i < 3; $i++) {
            $cx = $startX + ($i * ($cardWidth + $gap));

            // Card background & border
            imagefilledrectangle($im, $cx, $cardY, $cx + $cardWidth, $cardY + $cardHeight, $white);
            imagerectangle($im, $cx, $cardY, $cx + $cardWidth, $cardY + $cardHeight, $cardBorder);

            // Product image placeholder
            imagefilledrectangle($im, $cx + 12, $cardY + 12, $cx + $cardWidth - 12, $cardY + 150, $bgColor);
            imagefilledrectangle($im, $cx + 20, $cardY + 20, $cx + 80, $cardY + 38, $accentColor);
            imagestring($im, 2, $cx + 25, $cardY + 23, 'VERIFIED', $white);

            // Card text placeholders
            imagestring($im, 4, $cx + 16, $cardY + 170, 'Item SKU #' . (1001 + $i), $darkText);
            imagestring($im, 2, $cx + 16, $cardY + 195, 'High-grade laboratory certified', $grayText);
            imagestring($im, 2, $cx + 16, $cardY + 215, 'Fast tracked express delivery', $grayText);

            // Price & CTA Button
            imagestring($im, 4, $cx + 16, $cardY + 265, '$' . (29 + ($i * 15)) . '.99', $headerColor);
            imagefilledrectangle($im, $cx + $cardWidth - 95, $cardY + 258, $cx + $cardWidth - 16, $cardY + 288, $accentColor);
            imagestring($im, 2, $cx + $cardWidth - 85, $cardY + 267, 'Add to cart', $white);
        }

        imagepng($im, $diskPath);
        imagedestroy($im);
    }

    /**
     * Convert hexadecimal color code into [r, g, b].
     *
     * @return array{0: int, 1: int, 2: int}
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $val = hexdec($hex);

        return [
            ($val >> 16) & 0xFF,
            ($val >> 8) & 0xFF,
            $val & 0xFF,
        ];
    }
}
