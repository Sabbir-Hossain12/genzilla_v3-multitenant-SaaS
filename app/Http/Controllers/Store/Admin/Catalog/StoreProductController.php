<?php

namespace App\Http\Controllers\Store\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductController extends Controller
{
    public function products(Request $request)
    {
        $products = Product::where('tenant_id', tenantId())->with(['category', 'brand', 'variants.batches'])->when($request->filled('search'), fn($q) => $q->where(fn($sub) => $sub->where('name', 'like', '%' . $request->search . '%')->orWhere('generic_name', 'like', '%' . $request->search . '%')))->orderByDesc('created_at')->paginate(20)->withQueryString();
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
            $product = Product::create($data['product'] + ['tenant_id' => tenantId()]);
            $variant = $product->variants()->create($data['variant'] + [
                    'tenant_id' => tenantId(),
                    'title' => $data['variant']['title'] ?: 'Default', 'is_default' => true, 'options_hash' => $data['variant']['options_hash'],
                ]);
            $this->syncVariantOptions($variant, $data['options']);
            if (($data['initial_quantity'] ?? 0) > 0) $this->receiveIntoBatch($variant, (int)$data['initial_quantity'], 'DEFAULT', null, $data['variant']['cost_price'] ?? null, 'Opening stock');
            $this->saveProductImage($request, $product);
        });
        return redirect()->route('store.admin.products.index')->with('success', 'Product created.');
    }

    private function receiveIntoBatch(ProductVariant $variant, int $quantity, string $batchNo, ?string $expiry, ?float $cost, string $note): void
    {
        $batch = InventoryBatch::firstOrCreate(
            ['tenant_id' => tenantId(), 'variant_id' => $variant->id, 'batch_no' => $batchNo],
            ['quantity' => 0, 'reserved' => 0, 'expiry_date' => $expiry, 'cost_price' => $cost, 'received_at' => now(), 'status' => 'active']
        );
        $batch->increment('quantity', $quantity);
        $batch->update(['received_at' => now(), 'expiry_date' => $expiry ?? $batch->expiry_date, 'cost_price' => $cost ?? $batch->cost_price]);
        StockMovement::create(['tenant_id' => tenantId(), 'variant_id' => $variant->id, 'batch_id' => $batch->id, 'type' => 'receive', 'qty_delta' => $quantity, 'reserved_delta' => 0, 'user_id' => auth()->id(), 'note' => $note]);
    }

    public function editProduct(int $product)
    {
        $product = Product::where('tenant_id', tenantId())->with(['variants.batches', 'media'])->findOrFail($product);
        return view('store_admin.pages.catalog.products.form', $this->productFormData($product, $product->variants->first() ?? new ProductVariant()));
    }

    public function updateProduct(Request $request, int $product)
    {
        $product = Product::where('tenant_id', tenantId())->with('variants.batches')->findOrFail($product);
        $data = $this->validateProduct($request, $product);
        DB::transaction(function () use ($request, $product, $data) {
            $product->update($data['product']);
            $variant = $product->variants->first();
            if (!$variant) {
                $data['variant']['sku'] = $data['variant']['sku'] ?: strtoupper(Str::random(10));
                $variant = $product->variants()->create(['tenant_id' => tenantId(), 'sku' => $data['variant']['sku'] ?: strtoupper(Str::random(10)), 'title' => 'Default', 'is_default' => true] + $data['variant']);
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
            $product = Product::where('tenant_id', tenantId())->findOrFail($product);
            $product->variants()->update(['status' => 'archived']);
            $product->delete();
        });
        return back()->with('success', 'Product archived from the catalog.');
    }

    private function saveProductImage(Request $request, Product $product): void
    {
        if (!$request->hasFile('image')) return;
        foreach ($product->media()->where('collection', 'cover')->get() as $oldMedia) {
            Storage::disk('public')->delete($oldMedia->path);
            $oldMedia->delete();
        }
        $file = $request->file('image');
        $path = $file->store('tenants/' . tenantId() . '/products', 'public');
        $product->media()->create(['tenant_id' => tenantId(), 'model_type' => Product::class, 'collection' => 'cover', 'path' => $path, 'mime' => $file->getMimeType(), 'size_bytes' => $file->getSize(), 'position' => 0, 'alt' => $product->name, 'created_at' => now()]);
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $tenant = tenantId();
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
        assertSlugAvailable(Product::class, $slug, $product?->id);
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
            'categories' => Category::where('tenant_id', tenantId())->where('status', true)->orderBy('name')->get(),
            'brands' => Brand::where('tenant_id', tenantId())->where('status', true)->orderBy('name')->get(),
            'attributes' => ProductAttribute::where('tenant_id', tenantId())->with(['values' => fn($q) => $q->where('status', true)])->orderBy('position')->get(),
        ];
    }


    private function syncVariantOptions(ProductVariant $variant, array $valueIds): void
    {
        $sync = [];
        foreach ($valueIds as $valueId) {
            $value = ProductAttributeValue::where('tenant_id', tenantId())->findOrFail($valueId);
            $sync[$value->id] = ['tenant_id' => tenantId(), 'attribute_id' => $value->attribute_id];
        }
        $variant->attributeValues()->sync($sync);
    }
}
