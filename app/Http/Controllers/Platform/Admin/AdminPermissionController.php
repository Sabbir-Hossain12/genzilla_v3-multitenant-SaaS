<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class AdminPermissionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->toString();
        $group = $request->string('group')->toString();

        $sortable = ['name', 'group_name', 'guard_name'];
        $sort = $request->string('sort')->toString();
        if (! in_array($sort, $sortable, true)) {
            $sort = 'group_name';
        }
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';

        $perPage = (int) $request->input('per_page', 20);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $permissions = Permission::query()
            ->where('panel','platform')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('group_name', 'like', "%{$search}%");
                });
            })
            ->when($group !== '', fn ($query) => $query->where('group_name', $group))
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        $groups = Permission::query()
            ->select('group_name')
            ->distinct()
            ->orderBy('group_name')
            ->pluck('group_name');

        $filters = [
            'search' => $search,
            'group' => $group,
            'sort' => $sort,
            'direction' => $direction,
            'per_page' => $perPage,
        ];

        return view('platform_admin.pages.admin_permissions.index', compact('permissions', 'groups', 'filters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')],
            'group_name' => 'required|string|max:255',
        ]);

        Permission::create([
            'name' => $validated['name'],
            'group_name' => $validated['group_name'],
            'guard_name' => 'web',
        ]);

        return back()->with('success', 'Permission created successfully.');
    }

    public function update(Request $request, string $id)
    {
        $permission = Permission::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($permission->id)],
            'group_name' => 'required|string|max:255',
        ]);

        $permission->name = $validated['name'];
        $permission->group_name = $validated['group_name'];
        $permission->save();

        return back()->with('success', 'Permission updated successfully.');
    }

    public function destroy(string $id)
    {
        $permission = Permission::findOrFail($id);

        $permission->delete();

        return back()->with('success', 'Permission deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);

        $deleted = Permission::whereIn('id', $validated['ids'])->delete();

        return back()->with('success', "{$deleted} permission(s) deleted successfully.");
    }
}
