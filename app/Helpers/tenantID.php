<?php

if (!function_exists('tenantID')) {
    function tenantID(): int
    {
        // Safe check using null-safe operators in case auth user or tenant is missing
        return (int) (auth()->user()->tenant->id ?? 0);
    }
}

if (!function_exists('tenant')) {
    function tenant(): ?\App\Models\Tenant
    {
        return auth()->user()->tenant ?? null;
    }
}
