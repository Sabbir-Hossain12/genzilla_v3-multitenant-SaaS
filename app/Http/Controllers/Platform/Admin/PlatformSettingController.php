<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlatformSettingController extends Controller
{
    /**
     * Display the platform settings edit page.
     */
    public function index()
    {
        $setting = PlatformSetting::firstOrNew(['id' => 1]);

        return view('platform_admin.pages.settings.platform-settings', compact('setting'));
    }

    /**
     * Update the platform settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'platform_name' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'short_desc' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'phone_1' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'working_hours' => 'nullable|string|max:255',
            'default_currency' => 'nullable|string|max:10',
            'copyright_text' => 'nullable|string|max:255',
            'announcement_bar_text' => 'nullable|string',

            // SEO & Social Meta
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'meta_robots' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|url|max:255',
            'schema_markup' => 'nullable|string',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string',

            // Analytics & Scripts
            'fb_pixel' => 'nullable|string',
            'google_analytics' => 'nullable|string',
            'chatbox_script' => 'nullable|string',

            // Social Links
            'fb_link' => 'nullable|string|max:255',
            'x_link' => 'nullable|string|max:255',
            'youtube_link' => 'nullable|string|max:255',
            'insta_link' => 'nullable|string|max:255',

            // File uploads
            'black_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'light_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,ico,svg,webp|max:1024',
            'og_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $setting = PlatformSetting::firstOrNew(['id' => 1]);

        // Handle File Uploads
        $fileFields = ['black_logo', 'light_logo', 'favicon', 'og_image'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                if ($setting->$field && Storage::disk('public')->exists($setting->$field)) {
                    Storage::disk('public')->delete($setting->$field);
                }
                $path = $request->file($field)->store('settings', 'public');
                $validated[$field] = $path;
            }
        }

        $setting->fill($validated);
        $setting->save();

        return redirect()->back()->with('success', 'Platform settings updated successfully.');
    }
}
