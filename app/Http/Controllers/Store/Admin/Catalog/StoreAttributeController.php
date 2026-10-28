<?php

namespace App\Http\Controllers\Store\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreAttributeController extends Controller
{
    private function tenantId(): int
    {
        return (int)auth()->user()->tenant->id;
    }

    public function attributes()
    {
        $attributes = ProductAttribute::where('tenant_id', $this->tenantId())->withCount('values')->with('values')->orderBy('position')->orderBy('name')->paginate(20);
        return view('store_admin.pages.catalog.attributes', compact('attributes'));
    }

    public function storeAttribute(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'slug' => ['nullable', 'string', 'max:150'], 'position' => ['nullable', 'integer', 'min:0']]);
        $data['tenant_id'] = $this->tenantId();
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $this->assertSlugAvailable(ProductAttribute::class, $data['slug']);
        ProductAttribute::create($data);
        return back()->with('success', 'Attribute created.');
    }

    public function storeAttributeValue(Request $request, int $attribute)
    {
        $attribute = ProductAttribute::where('tenant_id', $this->tenantId())->findOrFail($attribute);
        $data = $request->validate(['value' => ['required', 'string', 'max:120'], 'position' => ['nullable', 'integer', 'min:0'], 'status' => ['nullable', 'boolean']]);
        $slug = Str::slug($data['value']);
        $exists = ProductAttributeValue::where('tenant_id', $this->tenantId())->where('attribute_id', $attribute->id)->where('slug', $slug)->exists();
        if ($exists) return back()->withErrors(['value' => 'That value already exists for this attribute.']);
        ProductAttributeValue::create(['tenant_id' => $this->tenantId(), 'attribute_id' => $attribute->id, 'value' => $data['value'], 'slug' => $slug, 'position' => $data['position'] ?? 0, 'status' => $request->boolean('status', true)]);
        return back()->with('success', 'Attribute value added.');
    }

    public function updateAttributeValue(Request $request, int $attribute, int $value)
    {
        $attribute = ProductAttribute::where('tenant_id', $this->tenantId())->findOrFail($attribute);
        $row = ProductAttributeValue::where('tenant_id', $this->tenantId())->where('attribute_id', $attribute->id)->findOrFail($value);
        $data = $request->validate(['value' => ['required', 'string', 'max:120'], 'position' => ['nullable', 'integer', 'min:0'], 'status' => ['nullable', 'boolean']]);
        $slug = Str::slug($data['value']);
        $exists = ProductAttributeValue::where('tenant_id', $this->tenantId())->where('attribute_id', $attribute->id)->where('slug', $slug)->where('id', '!=', $row->id)->exists();
        if ($exists) return back()->withErrors(['value' => 'That value already exists for this attribute.']);
        $row->update(['value' => $data['value'], 'slug' => $slug, 'position' => $data['position'] ?? 0, 'status' => $request->boolean('status')]);
        return back()->with('success', 'Attribute value updated.');
    }

    public function deleteAttributeValue(int $attribute, int $value)
    {
        $attribute = ProductAttribute::where('tenant_id', $this->tenantId())->findOrFail($attribute);
        ProductAttributeValue::where('tenant_id', $this->tenantId())->where('attribute_id', $attribute->id)->findOrFail($value)->delete();
        return back()->with('success', 'Attribute value deleted.');
    }

    public function updateAttribute(Request $request, int $attribute)
    {
        $row = ProductAttribute::where('tenant_id', $this->tenantId())->findOrFail($attribute);
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'position' => ['nullable', 'integer', 'min:0']]);
        $slug = Str::slug($data['name']);
        $this->assertSlugAvailable(ProductAttribute::class, $slug, $row->id);
        $row->update(['name' => $data['name'], 'slug' => $slug, 'position' => $data['position'] ?? 0]);
        return back()->with('success', 'Attribute updated.');
    }

    public function deleteAttribute(int $attribute)
    {
        ProductAttribute::where('tenant_id', $this->tenantId())->findOrFail($attribute)->delete();
        return back()->with('success', 'Attribute deleted.');
    }

    private function assertSlugAvailable(string $model, string $slug, ?int $ignore = null): void
    {
        $query = $model::where('tenant_id', $this->tenantId())->where('slug', $slug);
        if ($model === Product::class) $query->withTrashed();
        if ($ignore) $query->where('id', '!=', $ignore);
        if ($query->exists()) throw \Illuminate\Validation\ValidationException::withMessages(['slug' => 'This slug is already in use in your store.']);
    }
}
