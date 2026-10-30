<?php

namespace App\Http\Controllers\Store\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreBrandController extends Controller
{
    public function brands()
    {
        $brands = Brand::where('tenant_id', tenantId())
            ->withCount('products')
            ->with('media')
            ->orderBy('name')
            ->paginate(20);
        return view('store_admin.pages.catalog.brands', compact('brands'));
    }

    public function storeBrand(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('brands', 'slug')->where('tenant_id', tenantId())],
            'status' => ['nullable', 'boolean'],
            'image' => ['nullable', 'mimes:jpeg,png,jpg,gif,svg,webp,avif', 'max:5120'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'h1_heading' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'string', 'max:500'],
        ]);
        $data['tenant_id'] = tenantId();
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['status'] = $request->boolean('status', true);
        assertSlugAvailable(Brand::class, $data['slug']);

        $imageFile = null;
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $data['image'] = $imageFile->store('tenants/' . tenantId() . '/brands', 'public');
        }

        $brand = Brand::create($data);

        if ($imageFile && !empty($data['image'])) {
            $brand->media()->create([
                'tenant_id' => tenantId(),
                'model_type' => Brand::class,
                'collection' => 'cover',
                'path' => $data['image'],
                'mime' => $imageFile->getMimeType(),
                'size_bytes' => $imageFile->getSize(),
                'position' => 0,
                'alt' => $brand->name,
                'created_at' => now(),
            ]);
        }

        return back()->with('success', 'Brand created.');
    }

    public function updateBrand(Request $request, int $brand)
    {
        $row = Brand::where('tenant_id', tenantId())->findOrFail($brand);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('brands', 'slug')->where('tenant_id', tenantId())->ignore($row->id)],
            'status' => ['nullable', 'boolean'],
            'image' => ['nullable', 'mimes:jpeg,png,jpg,gif,svg,webp,avif', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'h1_heading' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'string', 'max:500'],
        ]);
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['status'] = $request->boolean('status');
        assertSlugAvailable(Brand::class, $data['slug'], $row->id);

        if ($request->hasFile('image')) {
            if ($row->image && Storage::disk('public')->exists($row->image)) {
                Storage::disk('public')->delete($row->image);
            }
            foreach ($row->media()->where('collection', 'cover')->get() as $oldMedia) {
                if ($oldMedia->path !== $row->image && Storage::disk('public')->exists($oldMedia->path)) {
                    Storage::disk('public')->delete($oldMedia->path);
                }
                $oldMedia->delete();
            }

            $imageFile = $request->file('image');
            $data['image'] = $imageFile->store('tenants/' . tenantId() . '/brands', 'public');

            $row->media()->create([
                'tenant_id' => tenantId(),
                'model_type' => Brand::class,
                'collection' => 'cover',
                'path' => $data['image'],
                'mime' => $imageFile->getMimeType(),
                'size_bytes' => $imageFile->getSize(),
                'position' => 0,
                'alt' => $data['name'],
                'created_at' => now(),
            ]);
        } elseif ($request->boolean('remove_image')) {
            if ($row->image && Storage::disk('public')->exists($row->image)) {
                Storage::disk('public')->delete($row->image);
            }
            foreach ($row->media()->where('collection', 'cover')->get() as $oldMedia) {
                if ($oldMedia->path !== $row->image && Storage::disk('public')->exists($oldMedia->path)) {
                    Storage::disk('public')->delete($oldMedia->path);
                }
                $oldMedia->delete();
            }
            $data['image'] = null;
        } else {
            $row->media()->where('collection', 'cover')->update(['alt' => $data['name']]);
        }

        unset($data['remove_image']);
        $row->update($data);
        return back()->with('success', 'Brand updated.');
    }

    public function deleteBrand(int $brand)
    {
        $row = Brand::where('tenant_id', tenantId())->findOrFail($brand);
        if ($row->image && Storage::disk('public')->exists($row->image)) {
            Storage::disk('public')->delete($row->image);
        }
        foreach ($row->media as $oldMedia) {
            if ($oldMedia->path !== $row->image && Storage::disk('public')->exists($oldMedia->path)) {
                Storage::disk('public')->delete($oldMedia->path);
            }
            $oldMedia->delete();
        }
        $row->delete();
        return back()->with('success', 'Brand deleted.');
    }
}
