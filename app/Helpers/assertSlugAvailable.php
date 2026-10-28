<?php

use App\Models\Product;

if (!function_exists('assertSlugAvailable')) {
    function assertSlugAvailable(string $model, string $slug, ?int $ignore = null): void
    {
        $query = $model::where('tenant_id', tenantID())->where('slug', $slug);
        if ($model === Product::class) $query->withTrashed();
        if ($ignore) $query->where('id', '!=', $ignore);
        if ($query->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['slug' => 'This slug is already in use in your store.']);
        }
    }
}
