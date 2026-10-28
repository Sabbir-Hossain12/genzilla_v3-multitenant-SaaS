<?php

namespace App\Http\Controllers\Store\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreBrandController extends Controller
{
    public function brands()
    {
        $brands = Brand::where('tenant_id', tenantId())->withCount('products')->orderBy('name')->paginate(20);
        return view('store_admin.pages.catalog.brands', compact('brands'));
    }

    public function storeBrand(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:160'], 'slug' => ['nullable', 'string', 'max:180'], 'status' => ['nullable', 'boolean']]);
        $data['tenant_id'] = tenantId();
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['status'] = $request->boolean('status', true);
        assertSlugAvailable(Brand::class, $data['slug']);
        Brand::create($data);
        return back()->with('success', 'Brand created.');
    }

    public function updateBrand(Request $request, int $brand)
    {
        $row = Brand::where('tenant_id', tenantId())->findOrFail($brand);
        $data = $request->validate(['name' => ['required', 'string', 'max:160'], 'slug' => ['nullable', 'string', 'max:180'], 'status' => ['nullable', 'boolean']]);
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['status'] = $request->boolean('status');
        assertSlugAvailable(Brand::class, $data['slug'], $row->id);
        $row->update($data);
        return back()->with('success', 'Brand updated.');
    }

    public function deleteBrand(int $brand)
    {
        Brand::where('tenant_id', tenantId())->findOrFail($brand)->delete();
        return back()->with('success', 'Brand deleted.');
    }
}
