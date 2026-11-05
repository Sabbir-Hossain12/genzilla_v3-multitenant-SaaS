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

        $tenant->ensureCanUpload($request, ['logo']);
        $maxKb = $tenant->maxFileSizeKilobytes();

        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:50'],
            'currency' => ['required', 'string', 'regex:/^[A-Za-z]{3}$/'],
            'timezone' => ['required', 'timezone'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,avif,svg', 'max:' . $maxKb],
            'remove_logo' => ['nullable', 'boolean'],
            'rx_compliance_mode' => ['required', Rule::in(['strict', 'flexible'])],
            'enable_direct_prescriptions' => ['nullable', 'boolean'],
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
        $settings['enable_direct_prescriptions'] = $request->boolean('enable_direct_prescriptions');

        if ($request->hasFile('logo')) {
            if ($tenant->logo_path && Storage::disk('public')->exists($tenant->logo_path)) {
                Storage::disk('public')->delete($tenant->logo_path);
            }
            $logoFile = $request->file('logo');
            $settings['logo_path'] = $logoFile->store('tenants/' . $tenant->id . '/branding', 'public');

            $tenant->media()->where('collection', 'branding')->delete();
            $tenant->media()->create([
                'tenant_id' => $tenant->id,
                'model_type' => \App\Models\Tenant::class,
                'model_id' => $tenant->id,
                'collection' => 'branding',
                'path' => $settings['logo_path'],
                'mime' => $logoFile->getMimeType(),
                'size_bytes' => $logoFile->getSize(),
                'position' => 0,
                'alt' => $tenant->store_name . ' Logo',
                'created_at' => now(),
            ]);
        } elseif ($request->boolean('remove_logo')) {
            if ($tenant->logo_path && Storage::disk('public')->exists($tenant->logo_path)) {
                Storage::disk('public')->delete($tenant->logo_path);
            }
            $settings['logo_path'] = null;
            $tenant->media()->where('collection', 'branding')->delete();
        }

        $tenant->fill($settings)->save();
        $tenant->syncUsage();

        return redirect()->route('store.admin.settings.index')->with('success', 'Store settings saved.');
    }
}
