<?php

namespace App\Http\Controllers\Store\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreSliderController extends Controller
{
    public function sliders(Request $request)
    {
        $tenantId = tenantId();

        $query = Slider::where('tenant_id', $tenantId)->orderBy('sort_order')->orderBy('id');

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('alt', 'like', "%{$term}%")
                  ->orWhere('subtitle', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status === 'active');
        }

        $sliders = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Slider::where('tenant_id', $tenantId)->count(),
            'active' => Slider::where('tenant_id', $tenantId)->where('status', true)->count(),
            'inactive' => Slider::where('tenant_id', $tenantId)->where('status', false)->count(),
        ];

        return view('store_admin.pages.sliders.index', compact('sliders', 'stats'));
    }

    public function storeSlider(Request $request)
    {
        $tenantId = tenantId();

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,svg,webp,avif', 'max:10240'],
            'image_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,svg,webp,avif', 'max:10240'],
            'image_url' => ['nullable', 'string', 'max:1000'],
            'alt' => ['nullable', 'string', 'max:255'],
            'link' => ['nullable', 'string', 'max:500'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('image_file') ?? $request->file('image');
        $imagePath = $validated['image_url'] ?? null;

        if ($file) {
            $imagePath = $file->store("tenants/{$tenantId}/sliders", 'public');
        }

        $status = $request->has('status')
            ? $request->boolean('status')
            : ($request->has('is_active') ? $request->boolean('is_active') : true);

        $slider = Slider::create([
            'tenant_id' => $tenantId,
            'title' => $validated['title'] ?? null,
            'subtitle' => $validated['subtitle'] ?? null,
            'image' => $imagePath,
            'alt' => $validated['alt'] ?? ($validated['title'] ?? 'Store Banner Slide'),
            'link' => $validated['link'] ?? null,
            'button_text' => $validated['button_text'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'status' => $status,
        ]);

        if ($file) {
            $slider->media()->create([
                'tenant_id' => $tenantId,
                'model_type' => Slider::class,
                'collection' => 'slider',
                'path' => $imagePath,
                'mime' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'position' => 0,
            ]);
        }

        return redirect()->route('store.admin.sliders.index')->with('success', 'Slide banner created successfully.');
    }

    public function updateSlider(Request $request, int $id)
    {
        $tenantId = tenantId();
        $slider = Slider::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,svg,webp,avif', 'max:10240'],
            'image_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,svg,webp,avif', 'max:10240'],
            'image_url' => ['nullable', 'string', 'max:1000'],
            'alt' => ['nullable', 'string', 'max:255'],
            'link' => ['nullable', 'string', 'max:500'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('image_file') ?? $request->file('image');
        $imagePath = $slider->image;

        if ($file) {
            if ($slider->image && !str_starts_with($slider->image, 'http')) {
                Storage::disk('public')->delete($slider->image);
            }
            $imagePath = $file->store("tenants/{$tenantId}/sliders", 'public');
        } elseif (!empty($validated['image_url'])) {
            $imagePath = $validated['image_url'];
        }

        $status = $request->has('status')
            ? $request->boolean('status')
            : ($request->has('is_active') ? $request->boolean('is_active') : $slider->status);

        $slider->update([
            'title' => $validated['title'] ?? null,
            'subtitle' => $validated['subtitle'] ?? null,
            'image' => $imagePath,
            'alt' => $validated['alt'] ?? ($validated['title'] ?? $slider->alt),
            'link' => $validated['link'] ?? null,
            'button_text' => $validated['button_text'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'status' => $status,
        ]);

        if ($file) {
            $slider->media()->where('collection', 'slider')->delete();
            $slider->media()->create([
                'tenant_id' => $tenantId,
                'model_type' => Slider::class,
                'collection' => 'slider',
                'path' => $imagePath,
                'mime' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'position' => 0,
            ]);
        }

        return redirect()->route('store.admin.sliders.index')->with('success', 'Slide banner updated successfully.');
    }

    public function deleteSlider(int $id)
    {
        $slider = Slider::where('tenant_id', tenantId())->findOrFail($id);

        if ($slider->image && !str_starts_with($slider->image, 'http')) {
            Storage::disk('public')->delete($slider->image);
        }

        $slider->media()->delete();
        $slider->delete();

        return redirect()->route('store.admin.sliders.index')->with('success', 'Slide banner deleted successfully.');
    }

    public function toggleStatus(int $id)
    {
        $slider = Slider::where('tenant_id', tenantId())->findOrFail($id);
        $slider->status = !$slider->status;
        $slider->save();

        $state = $slider->status ? 'activated' : 'deactivated';
        return redirect()->route('store.admin.sliders.index')->with('success', "Slide banner {$state} successfully.");
    }
}
