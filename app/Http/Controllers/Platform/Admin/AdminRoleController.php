<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class AdminRoleController extends Controller
{
    /**
     * Roles that must never be renamed or deleted.
     */
    protected const PROTECTED = ['Platform SuperAdmin'];

    public function index()
    {
        return view('platform_admin.pages.admin_role.index');
    }

    public function getData()
    {
        $roles = Role::query();

        return DataTables::eloquent($roles)
            ->addIndexColumn()
            ->addColumn('permissions', function (Role $role) {
                if ($role->permissions->isEmpty()) {
                    return '<span class="text-muted">No permissions</span>';
                }

                $badges = $role->permissions
                    ->sortBy('name')
                    ->map(fn ($permission) => '<span class="badge bg-success me-1 mb-1" style="white-space: nowrap;">'.e($permission->name).'</span>')
                    ->implode('');

                return '<div class="d-flex flex-wrap" style="max-width: 430px;">'.$badges.'</div>';
            })
            ->addColumn('action', function (Role $role) {
                $assignPermission = auth()->user()->can('Assign Permission')
                    ? '<a class="btn btn-sm btn-info" href="'.route('admin.role.permission.edit', $role->id).'" title="Assign permissions"><i class="fa-solid fa-user-shield"></i></a>'
                    : '';

                $edit = auth()->user()->can('Edit Role')
                    ? '<a class="editButton btn btn-sm btn-primary" href="javascript:void(0)" data-id="'.$role->id.'" data-bs-toggle="modal" data-bs-target="#editRoleModal"><i class="fas fa-edit"></i></a>'
                    : '';

                $delete = '';
                if (auth()->user()->can('Delete Role') && ! in_array($role->name, self::PROTECTED, true)) {
                    $delete = '<a class="btn btn-sm btn-danger" href="javascript:void(0)" data-id="'.$role->id.'" id="deleteRoleBtn"><i class="fas fa-trash"></i></a>';
                }

                return '<div class="d-flex gap-2">'.$assignPermission.$edit.$delete.'</div>';
            })
            ->rawColumns(['action', 'permissions'])
            ->make(true);
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

        return response()->json(['message' => 'success'], 201);
    }

    public function edit(string $id)
    {
        $role = Role::findOrFail($id);

        return response()->json([
            'message' => 'success',
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'protected' => in_array($role->name, self::PROTECTED, true),
            ],
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, self::PROTECTED, true)) {
            return response()->json(['message' => 'This role is protected and cannot be renamed.'], 422);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
        ]);

        $role->name = $validated['name'];
        $role->save();

        return response()->json(['message' => 'success'], 200);
    }

    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, self::PROTECTED, true)) {
            return response()->json(['message' => 'This role is protected and cannot be deleted.'], 422);
        }

        $role->delete();

        return response()->json(['message' => 'success'], 200);
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
