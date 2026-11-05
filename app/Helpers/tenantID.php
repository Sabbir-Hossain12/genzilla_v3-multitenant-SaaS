<?php

if (!function_exists('tenant')) {
    function tenant(): ?\App\Models\Tenant
    {
        if (app()->bound(\App\Models\Tenant::class)) {
            return app(\App\Models\Tenant::class);
        }

        if (request()?->attributes?->has('store')) {
            return request()->attributes->get('store');
        }

        if (auth()->check() && auth()->user()?->tenant) {
            return auth()->user()->tenant;
        }

        if (session()->has('tenant_id')) {
            return \App\Models\Tenant::find(session('tenant_id'));
        }

        return null;
    }
}

if (!function_exists('tenantID')) {
    function tenantID(): int
    {
        return (int) (tenant()?->id ?? (session('tenant_id') ?? 0));
    }
}
