<?php

namespace App\Http\Controllers\Store\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\TenantPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePageController extends Controller
{
    public function pages(Request $request)
    {
        $tenantId = tenantId();

        $query = TenantPage::where('tenant_id', $tenantId);

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('slug', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status === 'active');
        }

        $pages = $query->latest('id')->paginate(15)->withQueryString();

        $stats = [
            'total' => TenantPage::where('tenant_id', $tenantId)->count(),
            'active' => TenantPage::where('tenant_id', $tenantId)->where('status', true)->count(),
            'inactive' => TenantPage::where('tenant_id', $tenantId)->where('status', false)->count(),
            'published' => TenantPage::where('tenant_id', $tenantId)->where('status', true)->count(),
            'draft' => TenantPage::where('tenant_id', $tenantId)->where('status', false)->count(),
        ];

        return view('store_admin.pages.pages.index', compact('pages', 'stats'));
    }

    public function createPage()
    {
        return view('store_admin.pages.pages.create', [
            'page' => new TenantPage(),
        ]);
    }

    public function storePage(Request $request)
    {
        $tenantId = tenantId();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('tenant_pages', 'slug')->where('tenant_id', $tenantId),
            ],
            'content' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = Str::slug($validated['slug'] ?: $validated['title']);
        // Ensure slug uniqueness within tenant
        $baseSlug = $slug;
        $counter = 1;
        while (TenantPage::where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $status = $request->has('status')
            ? $request->boolean('status')
            : ($request->has('is_active') ? $request->boolean('is_active') : true);

        $page = TenantPage::create([
            'tenant_id' => $tenantId,
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'] ?? null,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'status' => $status,
            'is_system' => false,
        ]);

        return redirect()->route('store.admin.pages.index')->with('success', "Page '{$page->title}' created successfully.");
    }

    public function editPage(int $id)
    {
        $page = TenantPage::where('tenant_id', tenantId())->findOrFail($id);

        return view('store_admin.pages.pages.edit', compact('page'));
    }

    public function updatePage(Request $request, int $id)
    {
        $tenantId = tenantId();
        $page = TenantPage::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('tenant_pages', 'slug')->where('tenant_id', $tenantId)->ignore($page->id),
            ],
            'content' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = Str::slug($validated['slug'] ?: $validated['title']);

        $status = $request->has('status')
            ? $request->boolean('status')
            : ($request->has('is_active') ? $request->boolean('is_active') : $page->status);

        $page->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'] ?? null,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'status' => $status,
        ]);

        return redirect()->route('store.admin.pages.index')->with('success', "Page '{$page->title}' updated successfully.");
    }

    public function deletePage(int $id)
    {
        $page = TenantPage::where('tenant_id', tenantId())->findOrFail($id);

        if ($page->is_system) {
            return redirect()->route('store.admin.pages.index')->with('error', "System page '{$page->title}' cannot be removed.");
        }

        $title = $page->title;
        $page->delete();

        return redirect()->route('store.admin.pages.index')->with('success', "Page '{$title}' deleted successfully.");
    }

    public function toggleStatus(int $id)
    {
        $page = TenantPage::where('tenant_id', tenantId())->findOrFail($id);
        $page->status = !$page->status;
        $page->save();

        $label = $page->status ? 'published' : 'moved to draft';
        return redirect()->route('store.admin.pages.index')->with('success', "Page '{$page->title}' {$label}.");
    }
}
