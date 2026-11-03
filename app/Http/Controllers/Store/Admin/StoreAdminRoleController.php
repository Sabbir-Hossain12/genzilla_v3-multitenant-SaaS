<?php

namespace App\Http\Controllers\Store\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StoreAdminRoleController extends Controller
{
    private function tenantId(): int
    {
        return (int) (session('tenant_id') ?? auth()->user()->tenant?->id ?? 0);
    }

    private function permissions()
    {
        return Permission::query()
            ->where('panel','store')
            ->orderBy('group_name')
            ->orderBy('name')
            ->get();
    }

    public function index()
    {
        $roles = Role::query()->where('tenant_id', $this->tenantId())
            ->withCount('permissions')->with('permissions:id,name')->orderBy('name')->get();
        $protected = ['Store Owner'];

        return view('store_admin.pages.admin_role.index', compact('roles', 'protected'));
    }

    public function roles()
    {
        return $this->index();
    }

    public function createRole()
    {
        return $this->index();
    }

    public function storeRole(Request $request)
    {
        return $this->store($request);
    }

    public function editRole(int $role)
    {
        return $this->editPermissions($role);
    }

    public function updateRole(Request $request, int $role)
    {
        return $this->update($request, $role);
    }

    public function deleteRole(int $role)
    {
        return $this->destroy($role);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->where('tenant_id', $this->tenantId())->where('guard_name', 'web')],
        ]);

        Role::create(['name' => $validated['name'], 'guard_name' => 'web', 'tenant_id' => $this->tenantId()]);

        return back()->with('success', 'Role created successfully.');
    }

    public function update(Request $request, int $role)
    {
        $role = $this->findRole($role);
        abort_if(in_array($role->name, ['Store Owner'], true), 403);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->where('tenant_id', $this->tenantId())->where('guard_name', 'web')->ignore($role->id)],
        ]);
        $role->update(['name' => $validated['name']]);

        return back()->with('success', 'Role updated successfully.');
    }

    public function destroy(int $role)
    {
        $role = $this->findRole($role);
        abort_if(in_array($role->name, ['Store Owner'], true), 403);
        $role->delete();

        return back()->with('success', 'Role deleted successfully.');
    }

    public function editPermissions(int $role)
    {
        $role = $this->findRole($role);
        abort_if($role->name === 'Store Owner', 403);
        $permissionGroups = $this->permissions()->groupBy('group_name');

        return view('store_admin.pages.admin_role.permissions_to_role', compact('role', 'permissionGroups'));
    }

    public function updatePermissions(Request $request, int $role)
    {
        $role = $this->findRole($role);
        abort_if($role->name === 'Store Owner', 403);
        $allowed = $this->permissions()->pluck('name')->all();
        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in($allowed)],
        ]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return back()->with('success', 'Permissions updated for '.$role->name.'.');
    }

    private function findRole(int $id): Role
    {
        return Role::query()->where('tenant_id', $this->tenantId())->findOrFail($id);
    }
}
