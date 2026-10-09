<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class AdminPermissionController extends Controller
{
    public function index()
    {
        $groups = Permission::query()
            ->select('group_name')
            ->distinct()
            ->orderBy('group_name')
            ->pluck('group_name');

        return view('platform_admin.pages.admin_permissions.index', compact('groups'));
    }

    public function getData()
    {
        $permissions = Permission::query();

        return DataTables::eloquent($permissions)
            ->addIndexColumn()
            ->addColumn('group', function (Permission $permission) {
                return '<span class="badge bg-primary me-1">'.e($permission->group_name ?: 'General').'</span>';
            })
            ->addColumn('action', function (Permission $permission) {
                $edit = auth()->user()->can('Edit Permission')
                    ? '<a class="editButton btn btn-sm btn-primary" href="javascript:void(0)" data-id="'.$permission->id.'" data-bs-toggle="modal" data-bs-target="#editPermissionModal"><i class="fas fa-edit"></i></a>'
                    : '';

                $delete = auth()->user()->can('Delete Permission')
                    ? '<a class="btn btn-sm btn-danger" href="javascript:void(0)" data-id="'.$permission->id.'" id="deletePermissionBtn"><i class="fas fa-trash"></i></a>'
                    : '';

                return '<div class="d-flex gap-2">'.$edit.$delete.'</div>';
            })
            ->rawColumns(['action', 'group'])
            ->make(true);
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

        return response()->json(['message' => 'success'], 201);
    }

    public function edit(string $id)
    {
        $permission = Permission::findOrFail($id);

        return response()->json([
            'message' => 'success',
            'data' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'group_name' => $permission->group_name,
            ],
        ], 200);
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

        return response()->json(['message' => 'success'], 200);
    }

    public function destroy(string $id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return response()->json(['message' => 'success'], 200);
    }
}
