<?php

namespace App\Http\Controllers\Platform\Concerns;

use App\Models\PlatformSetting;

trait SharesPlatformBrand
{
    /**
     * Brand + contact details sourced from platform settings, shared with the
     * public Inertia pages (logo, footer, socials, meta).
     *
     * @return array<string, mixed>
     */
    protected function brand(): array
    {
        $setting = PlatformSetting::query()->first();

        return [
            'name' => $setting->platform_name ?? config('app.name'),
            'tagline' => $setting->tagline ?? '',
            'short_desc' => $setting->short_desc ?? '',
            'copyright' => $setting->copyright_text ?? '',
            'email' => $setting->email ?? '',
            'phone' => $setting->phone_1 ?? '',
            'address' => $setting->company_address ?? '',
            'meta_title' => $setting->meta_title ?? config('app.name'),
            'meta_description' => $setting->meta_description ?? '',
            'socials' => [
                'facebook' => $setting->fb_link ?: null,
                'x' => $setting->x_link ?: null,
                'youtube' => $setting->youtube_link ?: null,
                'instagram' => $setting->insta_link ?: null,
            ],
        ];
    }

    /**
     * Two-letter initials derived from a person's name.
     */
    protected function initials(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $parts = array_filter($parts);

        if ($parts === []) {
            return '';
        }

        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $initials;
    }
}
