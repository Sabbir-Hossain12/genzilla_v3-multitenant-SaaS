<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRoleController extends Controller
{
    /**
     * Roles that must never be renamed or deleted.
     */
    protected const PROTECTED = ['Platform SuperAdmin'];

    public function index()
    {
        $roles = Role::withCount('permissions')
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get();

        $protected = self::PROTECTED;

        return view('platform_admin.pages.admin_role.index', compact('roles', 'protected'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')],
        ]);

        Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        return back()->with('success', 'Role created successfully.');
    }

    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, self::PROTECTED, true)) {
            return back()->with('error', 'This role is protected and cannot be renamed.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
        ]);

        $role->name = $validated['name'];
        $role->save();

        return back()->with('success', 'Role updated successfully.');
    }

    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, self::PROTECTED, true)) {
            return back()->with('error', 'This role is protected and cannot be deleted.');
        }

        $role->delete();

        return back()->with('success', 'Role deleted successfully.');
    }

    public function assignPermissionsToRolePage(string $id)
    {
        $role = Role::findOrFail($id);
        $permissionGroups = Permission::query()
            ->orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->groupBy('group_name');

        return view('platform_admin.pages.admin_role.permissions_to_role', compact('role', 'permissionGroups'));
    }

    public function assignPermissionsToRole(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->back()->with('success', 'Permissions updated for '.$role->name.'.');
    }
}
