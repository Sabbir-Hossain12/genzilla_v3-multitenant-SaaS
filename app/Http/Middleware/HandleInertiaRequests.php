<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $tenant = tenant() ?? Tenant::first();
        $customer = auth('customer')->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'customer' => $customer ? [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'first_name' => $customer->first_name,
                    'last_name' => $customer->last_name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'status' => $customer->status,
                    'avatar' => $customer->avatar_url,
                ] : null,
            ],
            'store' => $tenant ? [
                'id' => $tenant->id,
                'name' => $tenant->store_name ?: ($tenant->name ?? 'MediMart'),
                'subdomain' => $tenant->subdomain,
                'custom_domain' => $tenant->custom_domain,
                'logo_url' => $tenant->logo_url,
                'phone' => $tenant->support_phone ?: ($tenant->phone ?? null),
                'email' => $tenant->support_email ?: ($tenant->email ?? null),
                'address' => $tenant->address ?? null,
                'currency' => $tenant->currency ?? 'BDT',
                'timezone' => $tenant->timezone ?? 'UTC',
                'enable_direct_prescriptions' => (bool) ($tenant->enable_direct_prescriptions ?? true),
                'settings' => $tenant->settings ?? [],
                'theme_settings' => $tenant->theme_settings ?? [],
            ] : null,
        ];
    }
}
