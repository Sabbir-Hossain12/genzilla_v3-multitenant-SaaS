<?php

namespace App\Http\Controllers\Store\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCategoryController extends Controller
{
    public function categories()
    {
        $categories = Category::where('tenant_id', tenantId())->withCount('products')->with('parent')->orderBy('position')->orderBy('name')->paginate(20);
        $parents = Category::where('tenant_id', tenantId())->orderBy('name')->get();
        return view('store_admin.pages.catalog.categories', compact('categories', 'parents'));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('categories', 'slug')->where('tenant_id', tenantId())],
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->where('tenant_id', tenantId())],
            'position' => ['nullable', 'integer', 'min:0'], 'status' => ['nullable', 'boolean'],
        ]);
        $data['tenant_id'] = tenantId();
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['status'] = $request->boolean('status', true);
        assertSlugAvailable(Category::class, $data['slug']);
        Category::create($data);
        return back()->with('success', 'Category created.');
    }

    public function updateCategory(Request $request, int $category)
    {
        $row = Category::where('tenant_id', tenantId())->findOrFail($category);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('categories', 'slug')->where('tenant_id', tenantId())->ignore($row->id)],
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->where('tenant_id', tenantId()), Rule::notIn([$row->id])],
            'position' => ['nullable', 'integer', 'min:0'], 'status' => ['nullable', 'boolean'],
        ]);
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['status'] = $request->boolean('status');
        assertSlugAvailable(Category::class, $data['slug'], $row->id);
        $row->update($data);
        return back()->with('success', 'Category updated.');
    }

    public function deleteCategory(int $category)
    {
        $row = Category::where('tenant_id', tenantId())->findOrFail($category);
        $row->delete();
        return back()->with('success', 'Category deleted.');
    }
}
