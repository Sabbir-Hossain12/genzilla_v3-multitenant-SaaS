<?php

namespace App\Http\Controllers\Store\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCategoryController extends Controller
{
    public function categories()
    {
        $categories = Category::where('tenant_id', tenantId())
            ->withCount('products')
            ->with(['parent', 'media'])
            ->orderBy('position')
            ->orderBy('name')
            ->paginate(20);
        $parents = Category::where('tenant_id', tenantId())->orderBy('name')->get();
        return view('store_admin.pages.catalog.categories', compact('categories', 'parents'));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('categories', 'slug')->where('tenant_id', tenantId())],
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->where('tenant_id', tenantId())],
            'position' => ['nullable', 'integer', 'min:0'],
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
        assertSlugAvailable(Category::class, $data['slug']);

        $imageFile = null;
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $data['image'] = $imageFile->store('tenants/' . tenantId() . '/categories', 'public');
        }

        $category = Category::create($data);

        if ($imageFile && !empty($data['image'])) {
            $category->media()->create([
                'tenant_id' => tenantId(),
                'model_type' => Category::class,
                'collection' => 'cover',
                'path' => $data['image'],
                'mime' => $imageFile->getMimeType(),
                'size_bytes' => $imageFile->getSize(),
                'position' => 0,
                'alt' => $category->name,
                'created_at' => now(),
            ]);
        }

        return back()->with('success', 'Category created.');
    }

    public function updateCategory(Request $request, int $category)
    {
        $row = Category::where('tenant_id', tenantId())->findOrFail($category);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('categories', 'slug')->where('tenant_id', tenantId())->ignore($row->id)],
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->where('tenant_id', tenantId()), Rule::notIn([$row->id])],
            'position' => ['nullable', 'integer', 'min:0'],
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
        assertSlugAvailable(Category::class, $data['slug'], $row->id);

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
            $data['image'] = $imageFile->store('tenants/' . tenantId() . '/categories', 'public');

            $row->media()->create([
                'tenant_id' => tenantId(),
                'model_type' => Category::class,
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
        return back()->with('success', 'Category updated.');
    }

    public function deleteCategory(int $category)
    {
        $row = Category::where('tenant_id', tenantId())->findOrFail($category);
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
        return back()->with('success', 'Category deleted.');
    }
}
