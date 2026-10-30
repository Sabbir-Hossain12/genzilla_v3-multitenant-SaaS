@extends('store_admin.layout.master')
@section('contents')
@php
    $field = 'mt-1 w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white';

    $oldVariants = old('variants');
    $defaultIndex = old('default_variant_index', 0);
    $initialVariants = [];

    if (is_array($oldVariants) && count($oldVariants) > 0) {
        foreach ($oldVariants as $idx => $v) {
            $initialVariants[] = [
                'id' => $v['id'] ?? null,
                'title' => $v['title'] ?? '',
                'sku' => $v['sku'] ?? '',
                'barcode' => $v['barcode'] ?? '',
                'price' => $v['price'] ?? 0,
                'compare_at_price' => $v['compare_at_price'] ?? '',
                'mrp' => $v['mrp'] ?? '',
                'cost_price' => $v['cost_price'] ?? '',
                'weight_grams' => $v['weight_grams'] ?? '',
                'initial_quantity' => $v['initial_quantity'] ?? 0,
                'track_inventory' => !empty($v['track_inventory']),
                'is_default' => (string)$defaultIndex === (string)$idx || !empty($v['is_default']),
                'options' => $v['options'] ?? [],
            ];
        }
    } elseif ($product->exists && $variants->isNotEmpty()) {
        foreach ($variants as $idx => $v) {
            $optMap = [];
            if ($v->relationLoaded('attributeValues')) {
                foreach ($v->attributeValues as $av) {
                    $optMap[$av->pivot->attribute_id] = $av->id;
                }
            }
            $initialVariants[] = [
                'id' => $v->id,
                'title' => $v->title,
                'sku' => $v->sku,
                'barcode' => $v->barcode,
                'price' => $v->price,
                'compare_at_price' => $v->compare_at_price,
                'mrp' => $v->mrp,
                'cost_price' => $v->cost_price,
                'weight_grams' => $v->weight_grams,
                'initial_quantity' => 0,
                'track_inventory' => (bool)$v->track_inventory,
                'is_default' => (bool)$v->is_default,
                'options' => $optMap,
            ];
        }
    } else {
        $initialVariants[] = [
            'id' => null,
            'title' => 'Default',
            'sku' => '',
            'barcode' => '',
            'price' => 0,
            'compare_at_price' => '',
            'mrp' => '',
            'cost_price' => '',
            'weight_grams' => '',
            'initial_quantity' => 0,
            'track_inventory' => true,
            'is_default' => true,
            'options' => [],
        ];
    }

    $thumbnailMedia = $product->exists && $product->relationLoaded('media')
        ? ($product->media->firstWhere('collection', 'thumbnail') ?? $product->media->firstWhere('collection', 'cover') ?? $product->media->first())
        : ($product->exists ? $product->media()->whereIn('collection', ['thumbnail', 'cover'])->first() : null);

    $sliderMedia = $product->exists && $product->relationLoaded('media')
        ? $product->media->where('collection', 'slider')
        : ($product->exists ? $product->media()->where('collection', 'slider')->get() : collect());
@endphp

<div class="mx-auto max-w-5xl space-y-6">
    <div>
        <a href="{{ route('store.admin.products.index') }}" class="text-sm text-indigo-600 hover:underline inline-flex items-center gap-1">
            <i class="fa-solid fa-arrow-left text-xs"></i> Back to Products
        </a>
        <h1 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">{{ $product->exists ? 'Edit product' : 'Create product' }}</h1>
    </div>

    @if($errors->any())
        <div class="rounded-xl bg-rose-50 p-4 text-sm text-rose-700 dark:bg-rose-950/30 dark:text-rose-300 border border-rose-200 dark:border-rose-900">
            <div class="font-semibold mb-1">Please fix the following errors:</div>
            <ul class="list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('store.admin.products.update', $product) : route('store.admin.products.store') }}" class="space-y-6">
        @csrf
        @if($product->exists)
            @method('PUT')
        @endif

        {{-- Product Details Section --}}
        <section class="card grid gap-4 p-5 md:grid-cols-2">
            <h2 class="font-bold text-ink dark:text-white md:col-span-2 text-base">Product details</h2>

            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Name (<span class="text-rose-500">*</span>)
                <input name="name" required value="{{ old('name', $product->name) }}" class="{{ $field }}">
            </label>

            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Slug (optional)
                <input name="slug" value="{{ old('slug', $product->slug) }}" class="{{ $field }}">
            </label>

            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Kind
                <select id="kind" name="kind" class="{{ $field }}">
                    <option value="general" @selected(old('kind', $product->kind ?? 'general') === 'general')>General</option>
                    <option value="medicine" @selected(old('kind', $product->kind) === 'medicine')>Medicine</option>
                </select>
            </label>

            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Status
                <select name="status" required class="{{ $field }}">
                    @foreach(['draft', 'active', 'archived'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $product->status ?? 'draft') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </label>

            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Category
                <select name="category_id" class="{{ $field }}">
                    <option value="">None</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string)old('category_id', $product->category_id) === (string)$category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Brand
                <select name="brand_id" class="{{ $field }}">
                    <option value="">None</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" @selected((string)old('brand_id', $product->brand_id) === (string)$brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="md:col-span-2 text-xs font-medium text-slate-700 dark:text-slate-300">Description
                <textarea name="description" rows="4" class="{{ $field }}">{{ old('description', $product->description) }}</textarea>
            </label>

            {{-- Product Media Subsection: Thumbnail Image (Single) & Slider Images (Multiple) --}}
            <div class="md:col-span-2 space-y-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                <div class="grid gap-6 sm:grid-cols-2">
                    {{-- 1. Thumbnail Image (Single) --}}
                    <div class="space-y-2">
                        <div>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Thumbnail image (Single)</span>
                            <p class="text-[11px] text-slate-400">Primary thumbnail displayed across catalog, search, and cart.</p>
                        </div>

                        <div class="flex items-center gap-3">
                            {{-- Existing Thumbnail Preview --}}
                            @if($thumbnailMedia)
                                <div id="existing-thumbnail-box" class="relative group h-20 w-20 shrink-0 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 shadow-xs">
                                    <img src="{{ asset('storage/' . $thumbnailMedia->path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                    <label for="product_thumbnail_input" class="absolute inset-0 bg-black/50 text-white text-[10px] flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition cursor-pointer">
                                        <i class="fa-solid fa-camera mb-0.5"></i> Change
                                    </label>
                                </div>
                            @endif

                            {{-- Live New Image Preview --}}
                            <div id="new-thumbnail-preview-box" class="relative h-20 w-20 shrink-0 rounded-lg overflow-hidden border border-indigo-300 dark:border-indigo-700 bg-indigo-50/50 dark:bg-indigo-950/30 shadow-xs hidden">
                                <img id="new-thumbnail-preview-img" src="" alt="Thumbnail preview" class="h-full w-full object-cover">
                                <button type="button" id="btn-cancel-thumbnail" class="absolute top-1 right-1 h-5 w-5 rounded-full bg-rose-600 text-white text-[10px] flex items-center justify-center hover:bg-rose-700 shadow-sm cursor-pointer" title="Cancel">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            {{-- Small Icon-like Upload Button --}}
                            <label for="product_thumbnail_input" id="thumbnail-upload-trigger" class="flex flex-col items-center justify-center h-20 w-20 shrink-0 rounded-lg border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-400 bg-slate-50/70 hover:bg-indigo-50/40 dark:bg-slate-800/50 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer transition group">
                                <i class="fa-solid fa-cloud-arrow-up text-lg group-hover:scale-110 transition"></i>
                                <span class="mt-1 text-[10px] font-medium">Thumbnail</span>
                                <input type="file" name="image" id="product_thumbnail_input" accept="image/jpeg,image/png,image/webp,image/gif,image/avif,image/svg+xml,.avif" class="hidden">
                            </label>

                            <div class="space-y-1">
                                <label for="product_thumbnail_input" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer transition shadow-xs">
                                    <i class="fa-solid fa-upload text-slate-400"></i> Choose thumbnail
                                </label>
                                @if($thumbnailMedia)
                                    <div>
                                        <label class="inline-flex items-center gap-1 text-xs text-rose-600 hover:text-rose-700 cursor-pointer">
                                            <input type="checkbox" name="remove_image" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                            Remove thumbnail
                                        </label>
                                    </div>
                                @endif
                                <div class="text-[11px] text-slate-400">AVIF, WEBP, PNG, JPG, GIF, SVG (up to 5MB)</div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Slider Images (Multiple) --}}
                    <div class="space-y-2">
                        <div>
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Slider images (Multiple)</span>
                            <p class="text-[11px] text-slate-400">Additional product gallery and slider showcase images.</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2.5">
                            {{-- Existing Slider Images with Remove Checkbox Badges --}}
                            @if($sliderMedia->isNotEmpty())
                                @foreach($sliderMedia as $sliderImg)
                                    <div class="relative group h-20 w-20 shrink-0 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 shadow-xs existing-slider-item">
                                        <img src="{{ asset('storage/' . $sliderImg->path) }}" alt="{{ $sliderImg->alt ?? $product->name }}" class="h-full w-full object-cover">
                                        <label class="absolute top-1 right-1 h-5 w-5 rounded-full bg-rose-600 text-white text-[10px] flex items-center justify-center hover:bg-rose-700 shadow-sm cursor-pointer" title="Mark for removal">
                                            <input type="checkbox" name="remove_slider_images[]" value="{{ $sliderImg->id }}" class="hidden slider-remove-checkbox">
                                            <i class="fa-solid fa-trash-can text-[9px]"></i>
                                        </label>
                                    </div>
                                @endforeach
                            @endif

                            {{-- Live Previews for Newly Selected Slider Images --}}
                            <div id="new-slider-previews" class="flex flex-wrap items-center gap-2.5"></div>

                            {{-- Small Icon-like Upload Button for Slider --}}
                            <label for="product_slider_input" class="flex flex-col items-center justify-center h-20 w-20 shrink-0 rounded-lg border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-400 bg-slate-50/70 hover:bg-indigo-50/40 dark:bg-slate-800/50 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer transition group">
                                <i class="fa-solid fa-images text-lg group-hover:scale-110 transition"></i>
                                <span class="mt-1 text-[10px] font-medium">+ Slider</span>
                                <input type="file" name="slider_images[]" id="product_slider_input" multiple accept="image/jpeg,image/png,image/webp,image/gif,image/avif,image/svg+xml,.avif" class="hidden">
                            </label>
                        </div>
                        <div class="text-[11px] text-slate-400">AVIF, WEBP, PNG, JPG, GIF, SVG (up to 5MB each).</div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Medicine Specific Section --}}
        <section id="medicine" class="card grid gap-4 p-5 md:grid-cols-2">
            <h2 class="font-bold text-ink dark:text-white md:col-span-2 text-base">Medicine details</h2>

            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Generic name
                <input name="generic_name" value="{{ old('generic_name', $product->generic_name) }}" class="{{ $field }}">
            </label>

            <label class="flex items-center gap-2 self-end text-xs font-medium text-slate-700 dark:text-slate-300 pb-2">
                <input type="checkbox" name="requires_prescription" value="1" @checked(old('requires_prescription', $product->requires_prescription)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                Prescription required
            </label>

            <label class="md:col-span-2 text-xs font-medium text-slate-700 dark:text-slate-300">Specifications JSON
                <textarea name="specs_json" rows="3" placeholder='{"strength":"500mg"}' class="{{ $field }} font-mono">{{ old('specs_json', $product->specs ? json_encode($product->specs, JSON_PRETTY_PRINT) : '') }}</textarea>
            </label>
        </section>

        {{-- Product Variants Section --}}
        <section class="card p-5 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4 dark:border-slate-800">
                <div>
                    <h2 class="font-bold text-ink dark:text-white text-base">Product Variants</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Add one or multiple variants for sizes, colors, pricing, and stock.</p>
                </div>
                <button type="button" id="btn-add-variant" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition cursor-pointer">
                    <i class="fa-solid fa-plus"></i> Add Variant
                </button>
            </div>

            <div id="variants-container" class="space-y-4">
                @foreach($initialVariants as $idx => $item)
                    <div class="variant-card card rounded-xl border border-slate-200 dark:border-slate-800 p-5 space-y-4 bg-slate-50/50 dark:bg-slate-900/40" data-variant-index="{{ $idx }}">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3 dark:border-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 text-xs font-bold variant-number">{{ $idx + 1 }}</span>
                                <span class="font-bold text-sm text-slate-800 dark:text-slate-100 variant-title-display">{{ $item['title'] ?: 'Variant ' . ($idx + 1) }}</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <label class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 cursor-pointer">
                                    <input type="radio" name="default_variant_index" value="{{ $idx }}" class="default-variant-radio text-indigo-600 focus:ring-indigo-500" @checked(!empty($item['is_default']))>
                                    Default variant
                                </label>
                                <button type="button" class="btn-remove-variant text-xs text-rose-600 hover:text-rose-700 font-semibold inline-flex items-center gap-1 cursor-pointer transition">
                                    <i class="fa-solid fa-trash-can"></i> Remove
                                </button>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            @if(!empty($item['id']))
                                <input type="hidden" name="variants[{{ $idx }}][id]\" value="{{ $item['id'] }}">
                            @endif

                            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Variant title
                                <input name="variants[{{ $idx }}][title]" value="{{ $item['title'] }}" placeholder="e.g. Small / Red or Default" class="{{ $field }} variant-title-input">
                            </label>

                            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">SKU
                                <input name="variants[{{ $idx }}][sku]" value="{{ $item['sku'] }}" placeholder="Auto-generated if blank" class="{{ $field }}">
                            </label>

                            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Barcode
                                <input name="variants[{{ $idx }}][barcode]" value="{{ $item['barcode'] }}" placeholder="Optional" class="{{ $field }}">
                            </label>

                            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Price (<span class="text-rose-500">*</span>)
                                <input name="variants[{{ $idx }}][price]" type="number" min="0" step="0.01" required value="{{ $item['price'] }}" class="{{ $field }}">
                            </label>

                            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Compare price
                                <input name="variants[{{ $idx }}][compare_at_price]" type="number" min="0" step="0.01" value="{{ $item['compare_at_price'] }}" class="{{ $field }}">
                            </label>

                            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">MRP
                                <input name="variants[{{ $idx }}][mrp]" type="number" min="0" step="0.01" value="{{ $item['mrp'] }}" class="{{ $field }}">
                            </label>

                            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Cost price
                                <input name="variants[{{ $idx }}][cost_price]" type="number" min="0" step="0.01" value="{{ $item['cost_price'] }}" class="{{ $field }}">
                            </label>

                            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Weight (grams)
                                <input name="variants[{{ $idx }}][weight_grams]" type="number" min="0" value="{{ $item['weight_grams'] }}" class="{{ $field }}">
                            </label>

                            @unless($product->exists)
                                <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Opening stock quantity
                                    <input name="variants[{{ $idx }}][initial_quantity]" type="number" min="0" value="{{ $item['initial_quantity'] ?? 0 }}" class="{{ $field }}">
                                </label>
                            @endunless

                            @foreach($attributes as $attribute)
                                @if($attribute->values->isNotEmpty())
                                    <label class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $attribute->name }}
                                        <select name="variants[{{ $idx }}][options][{{ $attribute->id }}]" class="{{ $field }} variant-option-select">
                                            <option value="">None</option>
                                            @foreach($attribute->values as $value)
                                                <option value="{{ $value->id }}">{{ $value->value }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                @endif
                            @endforeach

                            <label class="flex items-center gap-2 md:col-span-3 text-xs font-medium text-slate-700 dark:text-slate-300 pt-1">
                                <input type="checkbox" name="variants[{{ $idx }}][track_inventory]" value="1" @checked($item['track_inventory']) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                Track inventory for this variant
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- SEO Section --}}
        <section class="card grid gap-4 p-5 md:grid-cols-2">
            <h2 class="font-bold text-ink dark:text-white md:col-span-2 text-base">Search engine optimization (SEO)</h2>

            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">SEO title
                <input name="seo_title" value="{{ old('seo_title', $product->seo_title) }}" class="{{ $field }}">
            </label>

            <label class="md:col-span-2 text-xs font-medium text-slate-700 dark:text-slate-300">SEO description
                <textarea name="seo_description" rows="2" class="{{ $field }}">{{ old('seo_description', $product->seo_description) }}</textarea>
            </label>
        </section>

        {{-- Form Actions --}}
        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('store.admin.products.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800 transition">Cancel</a>
            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition cursor-pointer shadow-sm">
                {{ $product->exists ? 'Save changes' : 'Create product' }}
            </button>
        </div>
    </form>
</div>

{{-- Template for dynamic variant row creation --}}
<template id="variant-card-template">
    <div class="variant-card card rounded-xl border border-slate-200 dark:border-slate-800 p-5 space-y-4 bg-slate-50/50 dark:bg-slate-900/40" data-variant-index="__INDEX__">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 text-xs font-bold variant-number">__INDEX_PLUS_1__</span>
                <span class="font-bold text-sm text-slate-800 dark:text-slate-100 variant-title-display">Variant __INDEX_PLUS_1__</span>
            </div>
            <div class="flex items-center gap-4">
                <label class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 cursor-pointer">
                    <input type="radio" name="default_variant_index" value="__INDEX__" class="default-variant-radio text-indigo-600 focus:ring-indigo-500">
                    Default variant
                </label>
                <button type="button" class="btn-remove-variant text-xs text-rose-600 hover:text-rose-700 font-semibold inline-flex items-center gap-1 cursor-pointer transition">
                    <i class="fa-solid fa-trash-can"></i> Remove
                </button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Variant title
                <input name="variants[__INDEX__][title]" value="" placeholder="e.g. Small / Red or Default" class="{{ $field }} variant-title-input">
            </label>

            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">SKU
                <input name="variants[__INDEX__][sku]" value="" placeholder="Auto-generated if blank" class="{{ $field }}">
            </label>

            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Barcode
                <input name="variants[__INDEX__][barcode]" value="" placeholder="Optional" class="{{ $field }}">
            </label>

            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Price (<span class="text-rose-500">*</span>)
                <input name="variants[__INDEX__][price]" type="number" min="0" step="0.01" required value="0" class="{{ $field }}">
            </label>

            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Compare price
                <input name="variants[__INDEX__][compare_at_price]" type="number" min="0" step="0.01" value="" class="{{ $field }}">
            </label>

            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">MRP
                <input name="variants[__INDEX__][mrp]" type="number" min="0" step="0.01" value="" class="{{ $field }}">
            </label>

            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Cost price
                <input name="variants[__INDEX__][cost_price]" type="number" min="0" step="0.01" value="" class="{{ $field }}">
            </label>

            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Weight (grams)
                <input name="variants[__INDEX__][weight_grams]" type="number" min="0" value="" class="{{ $field }}">
            </label>

            @unless($product->exists)
                <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Opening stock quantity
                    <input name="variants[__INDEX__][initial_quantity]" type="number" min="0" value="0" class="{{ $field }}">
                </label>
            @endunless

            @foreach($attributes as $attribute)
                @if($attribute->values->isNotEmpty())
                    <label class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $attribute->name }}
                        <select name="variants[__INDEX__][options][{{ $attribute->id }}]" class="{{ $field }} variant-option-select">
                            <option value="">None</option>
                            @foreach($attribute->values as $value)
                                <option value="{{ $value->id }}">{{ $value->value }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif
            @endforeach

            <label class="flex items-center gap-2 md:col-span-3 text-xs font-medium text-slate-700 dark:text-slate-300 pt-1">
                <input type="checkbox" name="variants[__INDEX__][track_inventory]" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                Track inventory for this variant
            </label>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Medicine kind toggle
    const kind = document.getElementById('kind');
    const medicine = document.getElementById('medicine');
    function setMedicine() {
        if (medicine && kind) {
            medicine.classList.toggle('hidden', kind.value !== 'medicine');
        }
    }
    if (kind) {
        kind.addEventListener('change', setMedicine);
        setMedicine();
    }

    // Thumbnail image preview
    const thumbInput = document.getElementById('product_thumbnail_input');
    const newThumbBox = document.getElementById('new-thumbnail-preview-box');
    const newThumbImg = document.getElementById('new-thumbnail-preview-img');
    const btnCancelThumb = document.getElementById('btn-cancel-thumbnail');
    const existingThumbBox = document.getElementById('existing-thumbnail-box');

    if (thumbInput) {
        thumbInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (newThumbImg && newThumbBox) {
                        newThumbImg.src = e.target.result;
                        newThumbBox.classList.remove('hidden');
                    }
                    if (existingThumbBox) {
                        existingThumbBox.classList.add('opacity-40');
                    }
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    if (btnCancelThumb) {
        btnCancelThumb.addEventListener('click', function () {
            if (thumbInput) thumbInput.value = '';
            if (newThumbBox) newThumbBox.classList.add('hidden');
            if (existingThumbBox) existingThumbBox.classList.remove('opacity-40');
        });
    }

    // Slider images multi-preview
    const sliderInput = document.getElementById('product_slider_input');
    const sliderPreviews = document.getElementById('new-slider-previews');
    if (sliderInput && sliderPreviews) {
        sliderInput.addEventListener('change', function () {
            sliderPreviews.innerHTML = '';
            if (this.files) {
                Array.from(this.files).forEach(function (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const div = document.createElement('div');
                        div.className = 'h-20 w-20 shrink-0 rounded-lg overflow-hidden border border-indigo-300 dark:border-indigo-700 bg-indigo-50/50 dark:bg-indigo-950/30';
                        div.innerHTML = '<img src="' + e.target.result + '" class="h-full w-full object-cover" alt="New slider preview">';
                        sliderPreviews.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
    }

    // Existing slider removal visual toggle
    document.querySelectorAll('.slider-remove-checkbox').forEach(function (cb) {
        cb.addEventListener('change', function () {
            const card = this.closest('.existing-slider-item');
            if (card) {
                if (this.checked) {
                    card.classList.add('opacity-30', 'ring-2', 'ring-rose-500');
                } else {
                    card.classList.remove('opacity-30', 'ring-2', 'ring-rose-500');
                }
            }
        });
    });

    // Dynamic variant cards handling
    const $container = $('#variants-container');
    const templateHtml = document.getElementById('variant-card-template').innerHTML;
    let variantCounter = {{ count($initialVariants) }};

    function refreshVariantCards() {
        const $cards = $container.find('.variant-card');
        const count = $cards.length;

        $cards.each(function (index) {
            const num = index + 1;
            $(this).find('.variant-number').text(num);
            const titleInputVal = $(this).find('.variant-title-input').val().trim();
            if (!titleInputVal) {
                $(this).find('.variant-title-display').text('Variant ' . concat(num));
            }
        });

        if (count <= 1) {
            $container.find('.btn-remove-variant').addClass('hidden');
        } else {
            $container.find('.btn-remove-variant').removeClass('hidden');
        }

        // Ensure at least one radio is checked
        if ($container.find('.default-variant-radio:checked').length === 0) {
            $container.find('.default-variant-radio:first').prop('checked', true);
        }
    }

    $('#btn-add-variant').on('click', function () {
        const nextIndex = variantCounter++;
        const nextNum = $container.find('.variant-card').length + 1;

        let rendered = templateHtml
            .replace(/__INDEX__/g, nextIndex)
            .replace(/__INDEX_PLUS_1__/g, nextNum);

        const $newCard = $(rendered);
        $container.append($newCard);
        refreshVariantCards();
    });

    $container.on('click', '.btn-remove-variant', function () {
        const $cards = $container.find('.variant-card');
        if ($cards.length <= 1) {
            if (window.toastr) {
                toastr.warning('Product must have at least one variant.');
            } else {
                alert('Product must have at least one variant.');
            }
            return;
        }

        const $card = $(this).closest('.variant-card');
        const wasDefault = $card.find('.default-variant-radio').is(':checked');
        $card.remove();

        if (wasDefault) {
            $container.find('.variant-card:first .default-variant-radio').prop('checked', true);
        }

        refreshVariantCards();
    });

    $container.on('input', '.variant-title-input', function () {
        const val = $(this).val().trim();
        const num = $(this).closest('.variant-card').find('.variant-number').text();
        $(this).closest('.variant-card').find('.variant-title-display').text(val || ('Variant ' + num));
    });

    refreshVariantCards();
});
</script>
@endsection
