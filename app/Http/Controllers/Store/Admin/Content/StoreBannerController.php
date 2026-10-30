<?php

namespace App\Http\Controllers\Store\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreBannerController extends Controller
{
    public function banners(Request $request)
    {
        $tenantId = tenantId();

        $query = Banner::where('tenant_id', $tenantId)->orderBy('position')->orderBy('sort_order')->orderBy('id');

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('name', 'like', "%{$term}%")
                  ->orWhere('eyebrow', 'like', "%{$term}%")
                  ->orWhere('subtitle', 'like', "%{$term}%");
            });
        }

        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status === 'active');
        }

        $banners = $query->paginate(15)->withQueryString();

        $positions = [
            'promo_grid' => 'Homepage Promo Grid',
            'app_download' => 'Mobile App Download Promo',
            'popup' => 'Modal / Popup Banner',
        ];

        $stats = [
            'total' => Banner::where('tenant_id', $tenantId)->count(),
            'active' => Banner::where('tenant_id', $tenantId)->where('status', true)->count(),
            'promo_grid' => Banner::where('tenant_id', $tenantId)->where('position', 'promo_grid')->count(),
            'app_download' => Banner::where('tenant_id', $tenantId)->where('position', 'app_download')->count(),
            'popup' => Banner::where('tenant_id', $tenantId)->where('position', 'popup')->count(),
        ];

        return view('store_admin.pages.banners.index', compact('banners', 'stats', 'positions'));
    }

    public function storeBanner(Request $request)
    {
        $tenantId = tenantId();

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:150'],
            'eyebrow' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'cta_text' => ['nullable', 'string', 'max:100'],
            'link' => ['nullable', 'string', 'max:500'],
            'cta_color' => ['nullable', 'string', 'max:100'],
            'gradient' => ['nullable', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:50'],
            'image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,svg,webp,avif', 'max:10240'],
            'image_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,svg,webp,avif', 'max:10240'],
            'image_url' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('image_file') ?? $request->file('image');
        $imagePath = $validated['image_url'] ?? null;

        if ($file) {
            $imagePath = $file->store("tenants/{$tenantId}/banners", 'public');
        }

        $status = $request->has('status')
            ? $request->boolean('status')
            : ($request->has('is_active') ? $request->boolean('is_active') : true);

        $banner = Banner::create([
            'tenant_id' => $tenantId,
            'name' => $validated['name'] ?? null,
            'eyebrow' => $validated['eyebrow'] ?? null,
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'cta_text' => $validated['cta_text'] ?? 'Learn More →',
            'link' => $validated['link'] ?? '#',
            'cta_color' => $validated['cta_color'] ?? 'text-teal-600',
            'gradient' => $validated['gradient'] ?? 'from-teal-500 to-cyan-600',
            'image' => $imagePath,
            'position' => $validated['position'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'status' => $status,
        ]);

        if ($file) {
            $banner->media()->create([
                'tenant_id' => $tenantId,
                'model_type' => Banner::class,
                'collection' => 'banner',
                'path' => $imagePath,
                'mime' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'position' => 0,
            ]);
        }

        return redirect()->route('store.admin.banners.index')->with('success', "Banner '{$banner->title}' created successfully.");
    }

    public function updateBanner(Request $request, int $id)
    {
        $tenantId = tenantId();
        $banner = Banner::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:150'],
            'eyebrow' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'cta_text' => ['nullable', 'string', 'max:100'],
            'link' => ['nullable', 'string', 'max:500'],
            'cta_color' => ['nullable', 'string', 'max:100'],
            'gradient' => ['nullable', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:50'],
            'image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,svg,webp,avif', 'max:10240'],
            'image_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,svg,webp,avif', 'max:10240'],
            'image_url' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('image_file') ?? $request->file('image');
        $imagePath = $banner->image;

        if ($file) {
            if ($banner->image && !str_starts_with($banner->image, 'http')) {
                Storage::disk('public')->delete($banner->image);
            }
            $imagePath = $file->store("tenants/{$tenantId}/banners", 'public');
        } elseif (!empty($validated['image_url'])) {
            $imagePath = $validated['image_url'];
        }

        $status = $request->has('status')
            ? $request->boolean('status')
            : ($request->has('is_active') ? $request->boolean('is_active') : $banner->status);

        $banner->update([
            'name' => $validated['name'] ?? null,
            'eyebrow' => $validated['eyebrow'] ?? null,
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'cta_text' => $validated['cta_text'] ?? $banner->cta_text,
            'link' => $validated['link'] ?? $banner->link,
            'cta_color' => $validated['cta_color'] ?? $banner->cta_color,
            'gradient' => $validated['gradient'] ?? $banner->gradient,
            'image' => $imagePath,
            'position' => $validated['position'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'status' => $status,
        ]);

        if ($file) {
            $banner->media()->where('collection', 'banner')->delete();
            $banner->media()->create([
                'tenant_id' => $tenantId,
                'model_type' => Banner::class,
                'collection' => 'banner',
                'path' => $imagePath,
                'mime' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'position' => 0,
            ]);
        }

        return redirect()->route('store.admin.banners.index')->with('success', "Banner '{$banner->title}' updated successfully.");
    }

    public function deleteBanner(int $id)
    {
        $banner = Banner::where('tenant_id', tenantId())->findOrFail($id);

        if ($banner->image && !str_starts_with($banner->image, 'http')) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->media()->delete();
        $banner->delete();

        return redirect()->route('store.admin.banners.index')->with('success', 'Banner deleted successfully.');
    }

    public function toggleStatus(int $id)
    {
        $banner = Banner::where('tenant_id', tenantId())->findOrFail($id);
        $banner->status = !$banner->status;
        $banner->save();

        $state = $banner->status ? 'activated' : 'deactivated';
        return redirect()->route('store.admin.banners.index')->with('success', "Banner {$state} successfully.");
    }
}
