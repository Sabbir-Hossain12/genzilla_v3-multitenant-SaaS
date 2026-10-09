<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantDomain;
use Illuminate\Http\Request;

class TenantDomainController extends Controller
{
    public function index()
    {
        $domains = TenantDomain::with('tenant')->latest()->get();
        $tenants = Tenant::all();
        return view('platform_admin.pages.domains.index', compact('domains', 'tenants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'domain' => 'required|string|max:255|unique:tenant_domains,domain',
            'is_custom' => 'nullable|boolean',
            'is_primary' => 'nullable|boolean',
            'ssl_status' => 'required|in:pending,active,failed',
            'dns_verified' => 'nullable|boolean',
        ]);

        $validated['is_custom'] = $request->has('is_custom');
        $validated['is_primary'] = $request->has('is_primary');
        $validated['dns_verified'] = $request->has('dns_verified');

        if ($validated['is_primary']) {
            TenantDomain::where('tenant_id', $validated['tenant_id'])->update(['is_primary' => false]);
        }

        TenantDomain::create($validated);

        return redirect()->back()->with('success', 'Domain added successfully.');
    }

    public function update(Request $request, TenantDomain $domain)
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255|unique:tenant_domains,domain,' . $domain->id,
            'is_custom' => 'nullable|boolean',
            'is_primary' => 'nullable|boolean',
            'ssl_status' => 'required|in:pending,active,failed',
            'dns_verified' => 'nullable|boolean',
        ]);

        $validated['is_custom'] = $request->has('is_custom');
        $validated['is_primary'] = $request->has('is_primary');
        $validated['dns_verified'] = $request->has('dns_verified');

        if ($validated['is_primary']) {
            TenantDomain::where('tenant_id', $domain->tenant_id)->where('id', '!=', $domain->id)->update(['is_primary' => false]);
        }

        $domain->update($validated);

        return redirect()->back()->with('success', 'Domain updated successfully.');
    }

    public function destroy(TenantDomain $domain)
    {
        $domain->delete();
        return redirect()->back()->with('success', 'Domain deleted successfully.');
    }
}
