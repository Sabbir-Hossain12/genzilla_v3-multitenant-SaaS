<?php
if (!function_exists('tenantID')) {
    function tenantID(): int
    {
        // Safe check using null-safe operators in case auth user or tenant is missing
        return (int) (auth()->user()->tenant->id ?? 0);
    }
}
