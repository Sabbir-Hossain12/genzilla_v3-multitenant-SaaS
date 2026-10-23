<?php

namespace App\Http\Controllers\Store\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StoreAdminAdminController extends Controller
{
    private function tenantId(): int
    {
        return (int) auth()->user()->tenant->id;
    }

    public function index(Request $request)
    {
        $tenantId = $this->tenantId();
        $roles = Role::query()->where('tenant_id', $tenantId)->orderBy('name')->get();
        $admins = User::query()
            ->whereHas('roles', fn ($query) => $query->where('roles.tenant_id', $tenantId))
            ->with('roles')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('store_admin.pages.admins.index', compact('admins', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')->where('tenant_id', $this->tenantId())->where('guard_name', 'web')],
        ]);

        $role = Role::where('tenant_id', $this->tenantId())->findOrFail($validated['role_id']);
        abort_if($role->name === 'Store Owner', 422, 'New admins cannot be assigned the protected Store Owner role.');

        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'status' => 1,
        ]);
        $admin->assignRole($role);

        return back()->with('success', 'Store admin created successfully.');
    }

    public function update(Request $request, int $admin)
    {
        $admin = $this->findAdmin($admin);
        abort_if((int) $admin->id === (int) auth()->user()->tenant->user_id, 403);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')->where('tenant_id', $this->tenantId())->where('guard_name', 'web')],
        ]);

        $role = Role::where('tenant_id', $this->tenantId())->findOrFail($validated['role_id']);
        abort_if($role->name === 'Store Owner', 422, 'Admins cannot be assigned the protected Store Owner role.');

        $admin->fill(['name' => $validated['name'], 'email' => $validated['email']]);
        if (! empty($validated['password'])) {
            $admin->password = $validated['password'];
        }
        $admin->save();
        $admin->syncRoles([$role]);

        return back()->with('success', 'Store admin updated successfully.');
    }

    public function destroy(int $admin)
    {
        $admin = $this->findAdmin($admin);
        if ((int) $admin->id === (int) Auth::id() || (int) $admin->id === (int) auth()->user()->tenant->user_id) {
            return back()->with('error', 'You cannot delete the store owner or your own account.');
        }
        $admin->delete();

        return back()->with('success', 'Store admin deleted successfully.');
    }

    public function changeStatus(int $admin)
    {
        $admin = $this->findAdmin($admin);
        if ((int) $admin->id === (int) Auth::id() || (int) $admin->id === (int) auth()->user()->tenant->user_id) {
            return back()->with('error', 'You cannot change the store owner or your own status.');
        }
        $admin->status = $admin->status ? 0 : 1;
        $admin->save();

        return back()->with('success', 'Store admin status updated successfully.');
    }

    private function findAdmin(int $id): User
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->where('roles.tenant_id', $this->tenantId()))
            ->findOrFail($id);
    }
}
