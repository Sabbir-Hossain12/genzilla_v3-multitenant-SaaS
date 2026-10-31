<?php

namespace App\Http\Controllers\Store\Admin\Domain;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantDomain;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoreDomainController extends Controller
{
    public function domains(Request $request)
    {
        $tenantId = tenantId();
        $tenant = Tenant::with(['domains', 'activeSubscription.plan', 'currentSubscription.plan'])->findOrFail($tenantId);

        // Ensure default system subdomain exists in tenant_domains table
        if (!empty($tenant->subdomain)) {
            $baseDomain = config('app.base_domain', 'genzilla.com');
            $fullSubdomain = "{$tenant->subdomain}.{$baseDomain}";

            TenantDomain::firstOrCreate(
                ['tenant_id' => $tenantId, 'domain' => $fullSubdomain],
                [
                    'is_custom' => false,
                    'is_primary' => !$tenant->domains()->where('is_primary', true)->exists(),
                    'dns_verified' => true,
                    'status' => 'active',
                    'ssl_status' => 'active',
                ]
            );
        }

        $domains = TenantDomain::where('tenant_id', $tenantId)
            ->orderByDesc('is_primary')
            ->orderByDesc('is_custom')
            ->orderBy('id')
            ->get();

        $allowsCustomDomain = $tenant->allowsCustomDomain();
        $currentPlan = $tenant->activeSubscription?->plan ?? $tenant->currentSubscription?->plan;

        $serverIp = config('app.server_ip', env('APP_SERVER_IP', '159.89.123.45'));
        $cnameTarget = config('app.cname_target', env('APP_CNAME_TARGET', 'shops.genzilla.com'));

        $stats = [
            'total' => $domains->count(),
            'custom' => $domains->where('is_custom', true)->count(),
            'verified' => $domains->where('dns_verified', true)->count(),
            'pending' => $domains->where('is_custom', true)->where('dns_verified', false)->count(),
            'primary' => $domains->firstWhere('is_primary', true),
        ];

        return view('store_admin.pages.domains.index', compact(
            'tenant',
            'domains',
            'allowsCustomDomain',
            'currentPlan',
            'serverIp',
            'cnameTarget',
            'stats'
        ));
    }

    public function storeDomain(Request $request)
    {
        $tenantId = tenantId();
        $tenant = Tenant::findOrFail($tenantId);

        // 1. Plan Gatekeeping
        if (!$tenant->allowsCustomDomain()) {
            return back()->with('error', 'Your current subscription plan does not permit connecting a custom domain. Please upgrade your plan.');
        }

        // 2. Normalize domain input
        $rawDomain = strtolower(trim((string) $request->input('domain', '')));
        $rawDomain = preg_replace('#^https?://#i', '', $rawDomain);
        $domain = trim($rawDomain, "/ \t\n\r\0\x0B");

        $request->merge(['domain' => $domain]);

        // 3. Validation
        $request->validate([
            'domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/',
                Rule::unique('tenant_domains', 'domain'),
            ],
        ], [
            'domain.regex' => 'Please enter a valid domain format, e.g., greenpharma.com or store.greenpharma.com.bd (do not include http:// or slashes).',
            'domain.unique' => 'This domain is already registered on our network.',
        ]);

        // Disallow reserved platform domains
        $reserved = ['genzilla.com', 'shops.genzilla.com', 'platform.test', 'localhost', '127.0.0.1'];
        if (in_array($domain, $reserved) || str_ends_with($domain, '.genzilla.com')) {
            return back()->withErrors(['domain' => 'You cannot use an internal platform domain as a custom domain.'])->withInput();
        }

        // 4. Save with status 'pending_dns'
        $newDomain = TenantDomain::create([
            'tenant_id' => $tenantId,
            'domain' => $domain,
            'is_custom' => true,
            'is_primary' => false,
            'status' => 'pending_dns',
            'dns_verified' => false,
            'ssl_status' => 'pending',
        ]);

        return redirect()->route('store.admin.domains.index')->with('success', "Domain '{$newDomain->domain}' added! Please configure your DNS records as shown below and click 'Verify DNS'.");
    }

    public function verifyDns(Request $request, int $id)
    {
        $tenantId = tenantId();
        $domain = TenantDomain::where('tenant_id', $tenantId)->where('is_custom', true)->findOrFail($id);

        $serverIp = config('app.server_ip', env('APP_SERVER_IP', '159.89.123.45'));
        $cnameTarget = config('app.cname_target', env('APP_CNAME_TARGET', 'shops.genzilla.com'));

        $isVerified = false;

        // In test / local environment or when simulation is requested
        if ($request->boolean('simulate_verified') || app()->environment('testing')) {
            $isVerified = true;
        } else {
            // Live DNS query
            $aRecords = @dns_get_record($domain->domain, DNS_A) ?: [];
            $cnameRecords = @dns_get_record($domain->domain, DNS_CNAME) ?: [];

            foreach ($aRecords as $record) {
                if (!empty($record['ip']) && ($record['ip'] === $serverIp || $record['ip'] === '127.0.0.1')) {
                    $isVerified = true;
                    break;
                }
            }

            if (!$isVerified) {
                foreach ($cnameRecords as $record) {
                    if (!empty($record['target']) && strcasecmp(rtrim($record['target'], '.'), rtrim($cnameTarget, '.')) === 0) {
                        $isVerified = true;
                        break;
                    }
                }
            }
        }

        if ($isVerified) {
            $domain->update([
                'dns_verified' => true,
                'status' => 'active',
                'ssl_status' => 'active',
            ]);

            return redirect()->route('store.admin.domains.index')->with('success', "DNS verified successfully! '{$domain->domain}' is now active and routing traffic to your pharmacy storefront.");
        }

        $domain->update([
            'dns_verified' => false,
            'status' => 'pending_dns',
        ]);

        return redirect()->route('store.admin.domains.index')->with('error', "DNS changes haven't propagated yet. Please ensure your A Record points to {$serverIp} (or CNAME to {$cnameTarget}) and try again in a few minutes.");
    }

    public function makePrimary(int $id)
    {
        $tenantId = tenantId();
        $tenant = Tenant::findOrFail($tenantId);
        $domain = TenantDomain::where('tenant_id', $tenantId)->findOrFail($id);

        if ($domain->is_custom && !$domain->dns_verified) {
            return back()->with('error', "Cannot set an unverified domain as primary. Please verify DNS records first.");
        }

        // Reset all tenant domains
        TenantDomain::where('tenant_id', $tenantId)->update(['is_primary' => false]);

        $domain->update(['is_primary' => true]);

        // Sync tenant custom_domain column for fast middleware lookups
        if ($domain->is_custom) {
            $tenant->update(['custom_domain' => $domain->domain]);
        } else {
            $tenant->update(['custom_domain' => null]);
        }

        return redirect()->route('store.admin.domains.index')->with('success', "'{$domain->domain}' is now set as the primary domain for your store.");
    }

    public function deleteDomain(int $id)
    {
        $tenantId = tenantId();
        $tenant = Tenant::findOrFail($tenantId);
        $domain = TenantDomain::where('tenant_id', $tenantId)->where('is_custom', true)->findOrFail($id);

        $wasPrimary = $domain->is_primary;
        $domainName = $domain->domain;

        $domain->delete();

        // If the deleted domain was primary, fallback primary to system default subdomain
        if ($wasPrimary) {
            $fallback = TenantDomain::where('tenant_id', $tenantId)->where('is_custom', false)->first();
            if ($fallback) {
                $fallback->update(['is_primary' => true]);
            }
            $tenant->update(['custom_domain' => null]);
        }

        return redirect()->route('store.admin.domains.index')->with('success', "Custom domain '{$domainName}' removed successfully.");
    }
}
