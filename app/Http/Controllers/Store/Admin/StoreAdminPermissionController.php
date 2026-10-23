<?php

namespace App\Http\Controllers\Store\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class StoreAdminPermissionController extends Controller
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

        $storePermissions = Permission::query()->where('panel', 'store');
        $permissions = (clone $storePermissions)
            ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('group_name', 'like', "%{$search}%")))
            ->when($group !== '', fn ($query) => $query->where('group_name', $group))
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();
        $groups = (clone $storePermissions)->select('group_name')->distinct()->orderBy('group_name')->pluck('group_name');
        $filters = ['search' => $search, 'group' => $group, 'sort' => $sort, 'direction' => $direction, 'per_page' => $perPage];

        return view('store_admin.pages.admin_permissions.index', compact('permissions', 'groups', 'filters'));
    }
}
