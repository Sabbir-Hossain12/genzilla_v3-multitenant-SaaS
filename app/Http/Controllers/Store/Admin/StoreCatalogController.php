<?php

namespace App\Http\Controllers\Store\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCatalogController extends Controller
{
    private function tenantId(): int
    {
        return (int)auth()->user()->tenant->id;
    }

    public function categories()
    {
        $categories = Category::where('tenant_id', $this->tenantId())->withCount('products')->with('parent')->orderBy('position')->orderBy('name')->paginate(20);
        $parents = Category::where('tenant_id', $this->tenantId())->orderBy('name')->get();
        return view('store_admin.pages.catalog.categories', compact('categories', 'parents'));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('categories', 'slug')->where('tenant_id', $this->tenantId())],
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->where('tenant_id', $this->tenantId())],
            'position' => ['nullable', 'integer', 'min:0'], 'status' => ['nullable', 'boolean'],
        ]);
        $data['tenant_id'] = $this->tenantId();
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['status'] = $request->boolean('status', true);
        $this->assertSlugAvailable(Category::class, $data['slug']);
        Category::create($data);
        return back()->with('success', 'Category created.');
    }

    public function updateCategory(Request $request, int $category)
    {
        $row = Category::where('tenant_id', $this->tenantId())->findOrFail($category);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('categories', 'slug')->where('tenant_id', $this->tenantId())->ignore($row->id)],
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->where('tenant_id', $this->tenantId()), Rule::notIn([$row->id])],
            'position' => ['nullable', 'integer', 'min:0'], 'status' => ['nullable', 'boolean'],
        ]);
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['status'] = $request->boolean('status');
        $this->assertSlugAvailable(Category::class, $data['slug'], $row->id);
        $row->update($data);
        return back()->with('success', 'Category updated.');
    }

    public function deleteCategory(int $category)
    {
        $row = Category::where('tenant_id', $this->tenantId())->findOrFail($category);
        $row->delete();
        return back()->with('success', 'Category deleted.');
    }

    public function brands()
    {
        $brands = Brand::where('tenant_id', $this->tenantId())->withCount('products')->orderBy('name')->paginate(20);
        return view('store_admin.pages.catalog.brands', compact('brands'));
    }

    public function storeBrand(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:160'], 'slug' => ['nullable', 'string', 'max:180'], 'status' => ['nullable', 'boolean']]);
        $data['tenant_id'] = $this->tenantId();
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['status'] = $request->boolean('status', true);
        $this->assertSlugAvailable(Brand::class, $data['slug']);
        Brand::create($data);
        return back()->with('success', 'Brand created.');
    }

    public function updateBrand(Request $request, int $brand)
    {
        $row = Brand::where('tenant_id', $this->tenantId())->findOrFail($brand);
        $data = $request->validate(['name' => ['required', 'string', 'max:160'], 'slug' => ['nullable', 'string', 'max:180'], 'status' => ['nullable', 'boolean']]);
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['status'] = $request->boolean('status');
        $this->assertSlugAvailable(Brand::class, $data['slug'], $row->id);
        $row->update($data);
        return back()->with('success', 'Brand updated.');
    }

    public function deleteBrand(int $brand)
    {
        Brand::where('tenant_id', $this->tenantId())->findOrFail($brand)->delete();
        return back()->with('success', 'Brand deleted.');
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

    public function products(Request $request)
    {
        $products = Product::where('tenant_id', $this->tenantId())->with(['category', 'brand', 'variants.batches'])->when($request->filled('search'), fn($q) => $q->where(fn($sub) => $sub->where('name', 'like', '%' . $request->search . '%')->orWhere('generic_name', 'like', '%' . $request->search . '%')))->orderByDesc('created_at')->paginate(20)->withQueryString();
        return view('store_admin.pages.catalog.products.index', compact('products'));
    }

    public function createProduct()
    {
        return view('store_admin.pages.catalog.products.form', $this->productFormData(new Product(), new ProductVariant()));
    }

    public function storeProduct(Request $request)
    {
        $data = $this->validateProduct($request);
        DB::transaction(function () use ($request, $data) {
            $data['variant']['sku'] = $data['variant']['sku'] ?: strtoupper(Str::random(10));
            $product = Product::create($data['product'] + ['tenant_id' => $this->tenantId()]);
            $variant = $product->variants()->create($data['variant'] + [
                    'tenant_id' => $this->tenantId(),
                    'title' => $data['variant']['title'] ?: 'Default', 'is_default' => true, 'options_hash' => $data['variant']['options_hash'],
                ]);
            $this->syncVariantOptions($variant, $data['options']);
            if (($data['initial_quantity'] ?? 0) > 0) $this->receiveIntoBatch($variant, (int)$data['initial_quantity'], 'DEFAULT', null, $data['variant']['cost_price'] ?? null, 'Opening stock');
            $this->saveProductImage($request, $product);
        });
        return redirect()->route('store.admin.products.index')->with('success', 'Product created.');
    }

    public function editProduct(int $product)
    {
        $product = Product::where('tenant_id', $this->tenantId())->with(['variants.batches', 'media'])->findOrFail($product);
        return view('store_admin.pages.catalog.products.form', $this->productFormData($product, $product->variants->first() ?? new ProductVariant()));
    }

    public function updateProduct(Request $request, int $product)
    {
        $product = Product::where('tenant_id', $this->tenantId())->with('variants.batches')->findOrFail($product);
        $data = $this->validateProduct($request, $product);
        DB::transaction(function () use ($request, $product, $data) {
            $product->update($data['product']);
            $variant = $product->variants->first();
            if (!$variant) {
                $data['variant']['sku'] = $data['variant']['sku'] ?: strtoupper(Str::random(10));
                $variant = $product->variants()->create(['tenant_id' => $this->tenantId(), 'sku' => $data['variant']['sku'] ?: strtoupper(Str::random(10)), 'title' => 'Default', 'is_default' => true] + $data['variant']);
            } else {
                if (!$data['variant']['sku']) unset($data['variant']['sku']);
                $variant->update($data['variant']);
            }
            $this->syncVariantOptions($variant, $data['options']);
            $this->saveProductImage($request, $product);
        });
        return redirect()->route('store.admin.products.index')->with('success', 'Product updated.');
    }

    public function deleteProduct(int $product)
    {
        DB::transaction(function () use ($product) {
            $product = Product::where('tenant_id', $this->tenantId())->findOrFail($product);
            $product->variants()->update(['status' => 'archived']);
            $product->delete();
        });
        return back()->with('success', 'Product archived from the catalog.');
    }

    public function inventory()
    {
        $variants = ProductVariant::where('tenant_id', $this->tenantId())->whereHas('product')->with(['product', 'batches' => fn($q) => $q->orderBy('expiry_date')])->orderBy('sku')->paginate(25);
        $movements = StockMovement::where('tenant_id', $this->tenantId())->with(['variant.product' => fn($q) => $q->withTrashed(), 'user'])->latest('created_at')->limit(50)->get();
        return view('store_admin.pages.catalog.inventory', compact('variants', 'movements'));
    }

    public function receiveStock(Request $request, int $variant)
    {
        $variant = ProductVariant::where('tenant_id', $this->tenantId())->findOrFail($variant);
        $data = $request->validate([
            'batch_no' => ['required', 'string', 'max:100'], 'quantity' => ['required', 'integer', 'min:1'],
            'expiry_date' => ['nullable', 'date'], 'cost_price' => ['nullable', 'numeric', 'min:0'],
        ]);
        DB::transaction(fn() => $this->receiveIntoBatch($variant, $data['quantity'], $data['batch_no'], $data['expiry_date'] ?? null, $data['cost_price'] ?? null, 'Stock received'));
        return back()->with('success', 'Stock received into batch ' . $data['batch_no'] . '.');
    }

    public function adjustStock(Request $request, int $batch)
    {
        $data = $request->validate(['quantity_delta' => ['nullable', 'integer', 'not_in:0'], 'note' => ['nullable', 'string', 'max:1000'], 'status' => ['nullable', Rule::in(['active', 'quarantined', 'recalled'])]]);
        if (!$request->filled('quantity_delta') && !$request->filled('status')) {
            throw \Illuminate\Validation\ValidationException::withMessages(['quantity_delta' => 'Enter a quantity adjustment or choose a batch status.']);
        }
        $adjusted = DB::transaction(function () use ($batch, $data) {
            $batch = InventoryBatch::where('tenant_id', $this->tenantId())->lockForUpdate()->findOrFail($batch);
            $delta = (int)($data['quantity_delta'] ?? 0);
            $next = $batch->quantity + $delta;
            if ($next < $batch->reserved) return false;
            $batch->update(['quantity' => $next, 'status' => $data['status'] ?? $batch->status]);
            if ($delta !== 0) StockMovement::create(['tenant_id' => $this->tenantId(), 'variant_id' => $batch->variant_id, 'batch_id' => $batch->id, 'type' => 'adjust', 'qty_delta' => $delta, 'reserved_delta' => 0, 'user_id' => auth()->id(), 'note' => $data['note'] ?? null]);
            return true;
        });
        if (!$adjusted) return back()->withErrors(['quantity_delta' => 'Adjustment cannot reduce stock below the reserved quantity.']);
        return back()->with('success', 'Inventory adjusted.');
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $tenant = $this->tenantId();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'], 'slug' => ['nullable', 'string', 'max:220', Rule::unique('products', 'slug')->where('tenant_id', $tenant)->ignore($product?->id)],
            'kind' => ['required', Rule::in(['general', 'medicine'])], 'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'active', 'archived'])],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('tenant_id', $tenant)],
            'brand_id' => ['nullable', Rule::exists('brands', 'id')->where('tenant_id', $tenant)],
            'generic_name' => ['nullable', 'string', 'max:180'], 'requires_prescription' => ['nullable', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'], 'seo_description' => ['nullable', 'string', 'max:1000'],
            'specs_json' => ['nullable', 'json'], 'image' => ['nullable', 'image', 'max:5120'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('product_variants', 'sku')->where('tenant_id', $tenant)->ignore($product?->variants()->value('id'))],
            'barcode' => ['nullable', 'string', 'max:100'], 'variant_title' => ['nullable', 'string', 'max:150'],
            'price' => ['required', 'numeric', 'min:0'], 'compare_at_price' => ['nullable', 'numeric', 'min:0'], 'mrp' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'], 'weight_grams' => ['nullable', 'integer', 'min:0'],
            'track_inventory' => ['nullable', 'boolean'], 'initial_quantity' => ['nullable', 'integer', 'min:0'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'integer', Rule::exists('attributes_values', 'id')->where('tenant_id', $tenant)],
        ]);
        $slug = Str::slug($validated['slug'] ?: $validated['name']);
        $this->assertSlugAvailable(Product::class, $slug, $product?->id);
        $specs = null;
        if (!empty($validated['specs_json'])) {
            $specs = json_decode($validated['specs_json'], true);
            if (!is_array($specs)) throw \Illuminate\Validation\ValidationException::withMessages(['specs_json' => 'Specs must be a JSON object or array.']);
        }
        $productData = collect($validated)->only(['kind', 'name', 'description', 'status', 'category_id', 'brand_id', 'generic_name', 'requires_prescription', 'seo_title', 'seo_description'])->all();
        $productData['slug'] = $slug;
        $productData['requires_prescription'] = $request->boolean('requires_prescription');
        $productData['specs'] = $specs;
        $variant = collect($validated)->only(['sku', 'barcode', 'price', 'compare_at_price', 'mrp', 'cost_price', 'weight_grams'])->all();
        $variant['title'] = $validated['variant_title'] ?: 'Default';
        $variant['track_inventory'] = $request->boolean('track_inventory', true);
        $variant['status'] = 'active';
        $options = collect($validated['options'] ?? [])->filter()->map(fn($value, $attributeId) => (int)$value)->all();
        foreach ($options as $attributeId => $valueId) {
            $valid = ProductAttributeValue::where('tenant_id', $tenant)->where('attribute_id', $attributeId)->whereKey($valueId)->exists();
            if (!$valid) throw \Illuminate\Validation\ValidationException::withMessages(['options' => 'Choose a valid value for each product option.']);
        }
        sort($options);
        $variant['options_hash'] = $options ? md5(implode('-', $options)) : md5('default');
        return ['product' => $productData, 'variant' => $variant, 'options' => $options, 'initial_quantity' => (int)($validated['initial_quantity'] ?? 0)];
    }

    private function productFormData(Product $product, ProductVariant $variant): array
    {
        return [
            'product' => $product, 'variant' => $variant,
            'categories' => Category::where('tenant_id', $this->tenantId())->where('status', true)->orderBy('name')->get(),
            'brands' => Brand::where('tenant_id', $this->tenantId())->where('status', true)->orderBy('name')->get(),
            'attributes' => ProductAttribute::where('tenant_id', $this->tenantId())->with(['values' => fn($q) => $q->where('status', true)])->orderBy('position')->get(),
        ];
    }

    private function syncVariantOptions(ProductVariant $variant, array $valueIds): void
    {
        $sync = [];
        foreach ($valueIds as $valueId) {
            $value = ProductAttributeValue::where('tenant_id', $this->tenantId())->findOrFail($valueId);
            $sync[$value->id] = ['tenant_id' => $this->tenantId(), 'attribute_id' => $value->attribute_id];
        }
        $variant->attributeValues()->sync($sync);
    }

    private function receiveIntoBatch(ProductVariant $variant, int $quantity, string $batchNo, ?string $expiry, ?float $cost, string $note): void
    {
        $batch = InventoryBatch::firstOrCreate(
            ['tenant_id' => $this->tenantId(), 'variant_id' => $variant->id, 'batch_no' => $batchNo],
            ['quantity' => 0, 'reserved' => 0, 'expiry_date' => $expiry, 'cost_price' => $cost, 'received_at' => now(), 'status' => 'active']
        );
        $batch->increment('quantity', $quantity);
        $batch->update(['received_at' => now(), 'expiry_date' => $expiry ?? $batch->expiry_date, 'cost_price' => $cost ?? $batch->cost_price]);
        StockMovement::create(['tenant_id' => $this->tenantId(), 'variant_id' => $variant->id, 'batch_id' => $batch->id, 'type' => 'receive', 'qty_delta' => $quantity, 'reserved_delta' => 0, 'user_id' => auth()->id(), 'note' => $note]);
    }

    private function saveProductImage(Request $request, Product $product): void
    {
        if (!$request->hasFile('image')) return;
        foreach ($product->media()->where('collection', 'cover')->get() as $oldMedia) {
            Storage::disk('public')->delete($oldMedia->path);
            $oldMedia->delete();
        }
        $file = $request->file('image');
        $path = $file->store('tenants/' . $this->tenantId() . '/products', 'public');
        $product->media()->create(['tenant_id' => $this->tenantId(), 'model_type' => Product::class, 'collection' => 'cover', 'path' => $path, 'mime' => $file->getMimeType(), 'size_bytes' => $file->getSize(), 'position' => 0, 'alt' => $product->name, 'created_at' => now()]);
    }

    private function assertSlugAvailable(string $model, string $slug, ?int $ignore = null): void
    {
        $query = $model::where('tenant_id', $this->tenantId())->where('slug', $slug);
        if ($model === Product::class) $query->withTrashed();
        if ($ignore) $query->where('id', '!=', $ignore);
        if ($query->exists()) throw \Illuminate\Validation\ValidationException::withMessages(['slug' => 'This slug is already in use in your store.']);
    }
}
