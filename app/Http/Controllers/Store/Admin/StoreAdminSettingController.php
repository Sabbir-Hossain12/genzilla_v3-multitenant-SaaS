<?php

namespace App\Http\Controllers\Store\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StoreAdminSettingController extends Controller
{
    public function index()
    {
        $tenant = auth()->user()->tenant;
        abort_unless($tenant, 404);

        return view('store_admin.pages.settings.store-settings', compact('tenant'));
    }

    public function update(Request $request)
    {
        $tenant = auth()->user()->tenant;
        abort_unless($tenant, 404);

        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:50'],
            'currency' => ['required', 'string', 'regex:/^[A-Za-z]{3}$/'],
            'timezone' => ['required', 'timezone'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:2000'],
            'meta_keywords' => ['nullable', 'string', 'max:2000'],
            'meta_robots' => ['required', Rule::in(['index, follow', 'noindex, follow', 'index, nofollow', 'noindex, nofollow'])],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'schema_markup' => ['nullable', 'json'],
            'fb_pixel_id' => ['nullable', 'string', 'max:255'],
            'google_analytics_id' => ['nullable', 'string', 'max:255'],
            'gtm_id' => ['nullable', 'string', 'max:255'],
            'custom_head_script' => ['nullable', 'string', 'max:20000'],
        ]);

        $settings = collect($validated)->except(['logo', 'remove_logo'])->all();
        $settings['currency'] = strtoupper($settings['currency']);

        if ($request->hasFile('logo')) {
            if ($tenant->logo_path) Storage::disk('public')->delete($tenant->logo_path);
            $settings['logo_path'] = $request->file('logo')->store('tenants/'.$tenant->id.'/branding', 'public');
        } elseif ($request->boolean('remove_logo')) {
            if ($tenant->logo_path) Storage::disk('public')->delete($tenant->logo_path);
            $settings['logo_path'] = null;
        }

        $tenant->fill($settings)->save();

        return redirect()->route('store.admin.settings.index')->with('success', 'Store settings saved.');
    }
}
