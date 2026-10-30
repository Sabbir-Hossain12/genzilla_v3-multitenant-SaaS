<?php

namespace App\Http\Controllers\Store\Admin\Catalog;

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

class StoreProductController extends Controller
{
    public function products(Request $request)
    {
        $products = Product::where('tenant_id', tenantId())
            ->with(['category', 'brand', 'variants.batches', 'media'])
            ->when($request->filled('search'), fn($q) => $q->where(fn($sub) => $sub->where('name', 'like', '%' . $request->search . '%')->orWhere('generic_name', 'like', '%' . $request->search . '%')))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('store_admin.pages.catalog.products.index', compact('products'));
    }

    public function createProduct()
    {
        $product = new Product();
        $product->setRelation('media', collect());
        return view('store_admin.pages.catalog.products.form', $this->productFormData($product));
    }

    public function storeProduct(Request $request)
    {
        $data = $this->validateProduct($request);

        DB::transaction(function () use ($request, $data) {
            $product = Product::create($data['product'] + ['tenant_id' => tenantId()]);

            foreach ($data['variants'] as $index => $varData) {
                $variant = $product->variants()->create([
                    'tenant_id' => tenantId(),
                    'sku' => $varData['sku'],
                    'barcode' => $varData['barcode'] ?? null,
                    'title' => $varData['title'],
                    'price' => $varData['price'],
                    'compare_at_price' => $varData['compare_at_price'] ?? null,
                    'mrp' => $varData['mrp'] ?? null,
                    'cost_price' => $varData['cost_price'] ?? null,
                    'weight_grams' => $varData['weight_grams'] ?? null,
                    'track_inventory' => $varData['track_inventory'],
                    'is_default' => $varData['is_default'],
                    'position' => $index,
                    'status' => 'active',
                    'options_hash' => $varData['options_hash'],
                ]);

                if (!empty($varData['options'])) {
                    $this->syncVariantOptions($variant, $varData['options']);
                }

                if ($varData['initial_quantity'] > 0) {
                    $this->receiveIntoBatch(
                        $variant,
                        $varData['initial_quantity'],
                        'DEFAULT',
                        null,
                        $varData['cost_price'] ?? null,
                        'Opening stock'
                    );
                }
            }

            $this->syncProductMedia($request, $product);
        });

        $variantCount = count($data['variants']);
        $msg = $variantCount > 1
            ? "Product created with {$variantCount} variants."
            : 'Product created.';

        return redirect()->route('store.admin.products.index')->with('success', $msg);
    }

    public function editProduct(int $product)
    {
        $product = Product::where('tenant_id', tenantId())
            ->with(['variants.batches', 'variants.attributeValues', 'media'])
            ->findOrFail($product);

        return view('store_admin.pages.catalog.products.form', $this->productFormData($product));
    }

    public function updateProduct(Request $request, int $product)
    {
        $product = Product::where('tenant_id', tenantId())
            ->with(['variants.batches', 'variants.attributeValues', 'media'])
            ->findOrFail($product);

        $data = $this->validateProduct($request, $product);

        DB::transaction(function () use ($request, $product, $data) {
            $product->update($data['product']);

            // 1. Release options_hash on any variants not submitted (removed from form) and mark them archived
            $submittedIds = array_filter(array_column($data['variants'], 'id'));
            $product->variants()->whereNotIn('id', $submittedIds)->get()->each(function ($unkept) {
                $unkept->update([
                    'status' => 'archived',
                    'options_hash' => md5('archived_' . $unkept->id . '_' . $unkept->options_hash),
                ]);
            });

            // 2. Also ensure existing archived variants don't block the new active options_hashes
            $newHashes = array_column($data['variants'], 'options_hash');
            $product->variants()->where('status', 'archived')->whereIn('options_hash', $newHashes)->get()->each(function ($conflict) {
                $conflict->update([
                    'options_hash' => md5('archived_' . $conflict->id . '_' . $conflict->options_hash),
                ]);
            });

            // 3. Temporarily set distinct hashes on existing variants being updated to avoid transient unique constraint collisions
            foreach ($data['variants'] as $vItem) {
                if (!empty($vItem['id'])) {
                    $product->variants()->where('id', $vItem['id'])->update([
                        'options_hash' => md5('tmp_' . $vItem['id'] . '_' . microtime(true)),
                    ]);
                }
            }

            // 4. Update existing variants or create new variants
            $keptVariantIds = [];

            foreach ($data['variants'] as $index => $varData) {
                $variant = null;
                if (!empty($varData['id'])) {
                    $variant = $product->variants()->where('id', $varData['id'])->first();
                }

                if ($variant) {
                    $variant->update([
                        'sku' => $varData['sku'],
                        'barcode' => $varData['barcode'] ?? null,
                        'title' => $varData['title'],
                        'price' => $varData['price'],
                        'compare_at_price' => $varData['compare_at_price'] ?? null,
                        'mrp' => $varData['mrp'] ?? null,
                        'cost_price' => $varData['cost_price'] ?? null,
                        'weight_grams' => $varData['weight_grams'] ?? null,
                        'track_inventory' => $varData['track_inventory'],
                        'is_default' => $varData['is_default'],
                        'position' => $index,
                        'options_hash' => $varData['options_hash'],
                        'status' => 'active',
                    ]);
                } else {
                    $variant = $product->variants()->create([
                        'tenant_id' => tenantId(),
                        'sku' => $varData['sku'],
                        'barcode' => $varData['barcode'] ?? null,
                        'title' => $varData['title'],
                        'price' => $varData['price'],
                        'compare_at_price' => $varData['compare_at_price'] ?? null,
                        'mrp' => $varData['mrp'] ?? null,
                        'cost_price' => $varData['cost_price'] ?? null,
                        'weight_grams' => $varData['weight_grams'] ?? null,
                        'track_inventory' => $varData['track_inventory'],
                        'is_default' => $varData['is_default'],
                        'position' => $index,
                        'status' => 'active',
                        'options_hash' => $varData['options_hash'],
                    ]);

                    if ($varData['initial_quantity'] > 0) {
                        $this->receiveIntoBatch(
                            $variant,
                            $varData['initial_quantity'],
                            'DEFAULT',
                            null,
                            $varData['cost_price'] ?? null,
                            'Opening stock'
                        );
                    }
                }

                $keptVariantIds[] = $variant->id;
                $this->syncVariantOptions($variant, $varData['options'] ?? []);
            }

            $this->syncProductMedia($request, $product);
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

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $tenant = tenantId();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:220', Rule::unique('products', 'slug')->where('tenant_id', $tenant)->ignore($product?->id)],
            'kind' => ['required', Rule::in(['general', 'medicine'])],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'active', 'archived'])],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('tenant_id', $tenant)],
            'brand_id' => ['nullable', Rule::exists('brands', 'id')->where('tenant_id', $tenant)],
            'generic_name' => ['nullable', 'string', 'max:180'],
            'requires_prescription' => ['nullable', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:1000'],
            'specs_json' => ['nullable', 'json'],
            'image' => ['nullable', 'mimes:jpeg,png,jpg,gif,svg,webp,avif', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'slider_images' => ['nullable', 'array'],
            'slider_images.*' => ['nullable', 'mimes:jpeg,png,jpg,gif,svg,webp,avif', 'max:5120'],
            'remove_slider_images' => ['nullable', 'array'],
            'remove_slider_images.*' => ['integer'],

            'default_variant_index' => ['nullable'],

            'variants' => ['nullable', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.title' => ['nullable', 'string', 'max:150'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.barcode' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.mrp' => ['nullable', 'numeric', 'min:0'],
            'variants.*.cost_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.weight_grams' => ['nullable', 'integer', 'min:0'],
            'variants.*.initial_quantity' => ['nullable', 'integer', 'min:0'],
            'variants.*.track_inventory' => ['nullable', 'boolean'],
            'variants.*.options' => ['nullable', 'array'],
            'variants.*.options.*' => ['nullable', 'integer', Rule::exists('attributes_values', 'id')->where('tenant_id', $tenant)],

            // Fallback legacy fields if variants array not submitted
            'sku' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'variant_title' => ['nullable', 'string', 'max:150'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'mrp' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'weight_grams' => ['nullable', 'integer', 'min:0'],
            'track_inventory' => ['nullable', 'boolean'],
            'initial_quantity' => ['nullable', 'integer', 'min:0'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'integer', Rule::exists('attributes_values', 'id')->where('tenant_id', $tenant)],
        ]);

        $slug = Str::slug($validated['slug'] ?: $validated['name']);
        assertSlugAvailable(Product::class, $slug, $product?->id);

        $specs = null;
        if (!empty($validated['specs_json'])) {
            $specs = json_decode($validated['specs_json'], true);
            if (!is_array($specs)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['specs_json' => 'Specs must be a JSON object or array.']);
            }
        }

        $productData = [
            'name' => $validated['name'],
            'slug' => $slug,
            'kind' => $validated['kind'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'category_id' => $validated['category_id'] ?? null,
            'brand_id' => $validated['brand_id'] ?? null,
            'generic_name' => $validated['kind'] === 'medicine' ? ($validated['generic_name'] ?? null) : null,
            'requires_prescription' => $validated['kind'] === 'medicine' ? (bool)($validated['requires_prescription'] ?? false) : false,
            'specs' => $specs,
            'seo_title' => $validated['seo_title'] ?? null,
            'seo_description' => $validated['seo_description'] ?? null,
        ];

        // Process variants payload
        $rawVariants = $validated['variants'] ?? [];
        if (empty($rawVariants)) {
            $existingDefault = $product?->variants()->where('status', 'active')->where('is_default', true)->first()
                ?? $product?->variants()->where('status', 'active')->first();
            $rawVariants = [[
                'id' => $existingDefault?->id,
                'title' => $validated['variant_title'] ?? 'Default',
                'sku' => $validated['sku'] ?? null,
                'barcode' => $validated['barcode'] ?? null,
                'price' => $validated['price'] ?? 0,
                'compare_at_price' => $validated['compare_at_price'] ?? null,
                'mrp' => $validated['mrp'] ?? null,
                'cost_price' => $validated['cost_price'] ?? null,
                'weight_grams' => $validated['weight_grams'] ?? null,
                'track_inventory' => $validated['track_inventory'] ?? true,
                'initial_quantity' => $validated['initial_quantity'] ?? 0,
                'options' => $validated['options'] ?? [],
            ]];
        }

        $defaultIndex = $request->input('default_variant_index', 0);
        $processedVariants = [];
        $usedSkus = [];
        $usedHashes = [];

        foreach ($rawVariants as $index => $item) {
            $cleanOptions = [];
            if (!empty($item['options']) && is_array($item['options'])) {
                foreach ($item['options'] as $attrId => $valId) {
                    if (!empty($valId)) {
                        $cleanOptions[(int)$attrId] = (int)$valId;
                    }
                }
                ksort($cleanOptions);
            }

            $sku = trim((string)($item['sku'] ?? ''));
            if ($sku === '') {
                $baseSku = strtoupper(Str::slug($productData['name'], '-'));
                $sku = count($rawVariants) > 1 ? "{$baseSku}-" . ($index + 1) : $baseSku;
            }

            // Ensure unique SKU in this submission
            $uniqueSku = $sku;
            $counter = 1;
            while (in_array($uniqueSku, $usedSkus, true)) {
                $uniqueSku = "{$sku}-{$counter}";
                $counter++;
            }
            $usedSkus[] = $uniqueSku;

            // Ensure unique SKU in tenant DB
            $existingSkuQuery = ProductVariant::where('tenant_id', $tenant)->where('sku', $uniqueSku);
            if (!empty($item['id'])) {
                $existingSkuQuery->where('id', '!=', $item['id']);
            }
            if ($existingSkuQuery->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "variants.{$index}.sku" => "The SKU '{$uniqueSku}' is already in use by another variant.",
                ]);
            }

            // Compute unique options hash for this product's variant
            if (!empty($cleanOptions)) {
                $optionsHash = md5(json_encode($cleanOptions));
            } elseif (count($rawVariants) === 1) {
                $optionsHash = md5('default');
            } else {
                $titleKey = Str::slug($item['title'] ?? '') ?: 'var';
                $optionsHash = md5("v:{$titleKey}:{$uniqueSku}");
            }

            if (in_array($optionsHash, $usedHashes, true)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "variants.{$index}.title" => "Variant " . ($index + 1) . " has the exact same attribute combination as another variant. Each variant must have unique options.",
                ]);
            }
            $usedHashes[] = $optionsHash;

            $isDefault = ((string)$defaultIndex === (string)$index);

            $processedVariants[] = [
                'id' => !empty($item['id']) ? (int)$item['id'] : null,
                'title' => !empty($item['title']) ? trim($item['title']) : (count($rawVariants) === 1 ? 'Default' : 'Variant ' . ($index + 1)),
                'sku' => $uniqueSku,
                'barcode' => !empty($item['barcode']) ? trim($item['barcode']) : null,
                'price' => (float)($item['price'] ?? 0),
                'compare_at_price' => !empty($item['compare_at_price']) ? (float)$item['compare_at_price'] : null,
                'mrp' => !empty($item['mrp']) ? (float)$item['mrp'] : null,
                'cost_price' => !empty($item['cost_price']) ? (float)$item['cost_price'] : null,
                'weight_grams' => !empty($item['weight_grams']) ? (int)$item['weight_grams'] : null,
                'track_inventory' => isset($item['track_inventory']) ? (bool)$item['track_inventory'] : true,
                'initial_quantity' => !empty($item['initial_quantity']) ? (int)$item['initial_quantity'] : 0,
                'is_default' => $isDefault,
                'options_hash' => $optionsHash,
                'options' => $cleanOptions,
            ];
        }

        $hasDefault = collect($processedVariants)->contains('is_default', true);
        if (!$hasDefault && !empty($processedVariants)) {
            $processedVariants[0]['is_default'] = true;
        }

        return [
            'product' => $productData,
            'variants' => $processedVariants,
        ];
    }

    private function productFormData(Product $product): array
    {
        $variants = $product->exists
            ? $product->variants()->where('status', 'active')->with(['attributeValues', 'batches'])->orderBy('position')->get()
            : collect([new ProductVariant([
                'title' => 'Default',
                'price' => 0,
                'is_default' => true,
                'track_inventory' => true,
            ])]);

        if ($variants->isEmpty()) {
            $variants = collect([new ProductVariant([
                'title' => 'Default',
                'price' => 0,
                'is_default' => true,
                'track_inventory' => true,
            ])]);
        }

        return [
            'product' => $product,
            'variants' => $variants,
            'variant' => $variants->first() ?? new ProductVariant(),
            'categories' => Category::where('tenant_id', tenantId())->where('status', true)->orderBy('name')->get(),
            'brands' => Brand::where('tenant_id', tenantId())->where('status', true)->orderBy('name')->get(),
            'attributes' => ProductAttribute::where('tenant_id', tenantId())->with(['values' => fn($q) => $q->where('status', true)])->orderBy('position')->get(),
        ];
    }

    private function syncVariantOptions(ProductVariant $variant, array $valueIds): void
    {
        $sync = [];
        foreach ($valueIds as $attributeId => $valueId) {
            if (!$valueId) continue;
            $value = ProductAttributeValue::where('tenant_id', tenantId())->find($valueId);
            if ($value) {
                $sync[$value->id] = ['tenant_id' => tenantId(), 'attribute_id' => $value->attribute_id];
            }
        }
        $variant->attributeValues()->sync($sync);
    }

    private function receiveIntoBatch(ProductVariant $variant, int $quantity, string $batchNo, ?string $expiry, ?float $cost, string $note): void
    {
        $batch = InventoryBatch::firstOrCreate(
            ['tenant_id' => tenantId(), 'variant_id' => $variant->id, 'batch_no' => $batchNo],
            ['quantity' => 0, 'reserved' => 0, 'expiry_date' => $expiry, 'cost_price' => $cost, 'received_at' => now(), 'status' => 'active']
        );
        $batch->increment('quantity', $quantity);
        $batch->update(['received_at' => now(), 'expiry_date' => $expiry ?? $batch->expiry_date, 'cost_price' => $cost ?? $batch->cost_price]);
        StockMovement::create([
            'tenant_id' => tenantId(),
            'variant_id' => $variant->id,
            'batch_id' => $batch->id,
            'type' => 'receive',
            'qty_delta' => $quantity,
            'reserved_delta' => 0,
            'user_id' => auth()->id(),
            'note' => $note,
            'created_at' => now(),
        ]);
    }

    private function syncProductMedia(Request $request, Product $product): void
    {
        // 1. Single Thumbnail image handling
        if ($request->hasFile('image')) {
            foreach ($product->media()->whereIn('collection', ['thumbnail', 'cover'])->get() as $oldMedia) {
                if (Storage::disk('public')->exists($oldMedia->path)) {
                    Storage::disk('public')->delete($oldMedia->path);
                }
                $oldMedia->delete();
            }
            $file = $request->file('image');
            $path = $file->store('tenants/' . tenantId() . '/products/thumbnail', 'public');
            $product->media()->create([
                'tenant_id' => tenantId(),
                'model_type' => Product::class,
                'collection' => 'thumbnail',
                'path' => $path,
                'mime' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'position' => 0,
                'alt' => $product->name,
                'created_at' => now(),
            ]);
        } elseif ($request->boolean('remove_image')) {
            foreach ($product->media()->whereIn('collection', ['thumbnail', 'cover'])->get() as $oldMedia) {
                if (Storage::disk('public')->exists($oldMedia->path)) {
                    Storage::disk('public')->delete($oldMedia->path);
                }
                $oldMedia->delete();
            }
        } else {
            $product->media()->whereIn('collection', ['thumbnail', 'cover'])->update(['alt' => $product->name]);
        }

        // 2. Remove slider images if requested
        if ($request->filled('remove_slider_images')) {
            $removeIds = array_filter(array_map('intval', (array)$request->input('remove_slider_images')));
            if (!empty($removeIds)) {
                $sliderMediaToDelete = $product->media()->where('collection', 'slider')->whereIn('id', $removeIds)->get();
                foreach ($sliderMediaToDelete as $oldSlider) {
                    if (Storage::disk('public')->exists($oldSlider->path)) {
                        Storage::disk('public')->delete($oldSlider->path);
                    }
                    $oldSlider->delete();
                }
            }
        }

        // 3. Upload new slider images (multiple)
        if ($request->hasFile('slider_images')) {
            $currentPos = (int)($product->media()->where('collection', 'slider')->max('position') ?? 0);
            foreach ($request->file('slider_images') as $sliderFile) {
                $currentPos++;
                $path = $sliderFile->store('tenants/' . tenantId() . '/products/slider', 'public');
                $product->media()->create([
                    'tenant_id' => tenantId(),
                    'model_type' => Product::class,
                    'collection' => 'slider',
                    'path' => $path,
                    'mime' => $sliderFile->getMimeType(),
                    'size_bytes' => $sliderFile->getSize(),
                    'position' => $currentPos,
                    'alt' => $product->name . ' - Slide ' . $currentPos,
                    'created_at' => now(),
                ]);
            }
        }
    }
}
