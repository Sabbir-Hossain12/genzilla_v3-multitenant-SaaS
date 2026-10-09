<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class PlatformAdminController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('name')->get();

        return view('platform_admin.pages.admins.index', compact('roles'));
    }

    public function getData()
    {
        $admins = User::query()->permission('Platform Admin Dashboard');

        return DataTables::eloquent($admins)
            ->addIndexColumn()
            ->addColumn('role', function (User $admin) {
                $roles = $admin->getRoleNames();

                if ($roles->isEmpty()) {
                    return '<span class="badge bg-secondary">No role</span>';
                }

                return $roles
                    ->map(fn ($role) => '<span class="badge bg-success me-1">'.e($role).'</span>')
                    ->implode('');
            })
            ->addColumn('status', function (User $admin) {
                if (! Auth::user()->can('Status Admin')) {
                    return $admin->status == 1
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                }

                $icon = $admin->status == 1
                    ? '<i class="fa-solid fa-toggle-on fa-2x"></i>'
                    : '<i class="fa-solid fa-toggle-off fa-2x" style="color: grey"></i>';

                return '<a class="status" id="adminStatus" href="javascript:void(0)" data-id="'.$admin->id.'" data-status="'.$admin->status.'">'.$icon.'</a>';
            })
            ->addColumn('action', function (User $admin) {
                $editAction = Auth::user()->can('Edit Admin')
                    ? '<a class="editButton btn btn-sm btn-primary" href="javascript:void(0)" data-id="'.$admin->id.'" data-bs-toggle="modal" data-bs-target="#editAdminModal"><i class="fas fa-edit"></i></a>'
                    : '';

                $deleteAction = Auth::user()->can('Delete Admin')
                    ? '<a class="btn btn-sm btn-danger" href="javascript:void(0)" data-id="'.$admin->id.'" id="deleteAdminBtn"><i class="fas fa-trash"></i></a>'
                    : '';

                return '<div class="d-flex gap-3">'.$editAction.$deleteAction.'</div>';
            })
            ->rawColumns(['action', 'status', 'role'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => 'required|string|min:6',
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
        ]);

        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'status' => 1,
        ]);

        $admin->assignRole($validated['role']);

        return response()->json(['message' => 'success'], 201);
    }

    public function edit(string $id)
    {
        $admin = User::findOrFail($id);
        $roles = Role::orderBy('name')->get();

        return response()->json([
            'message' => 'success',
            'data' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'status' => $admin->status,
                'role' => $admin->getRoleNames()->first(),
            ],
            'roles' => $roles,
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $admin = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin->id)],
            'password' => 'nullable|string|min:6',
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
        ]);

        $admin->name = $validated['name'];
        $admin->email = $validated['email'];

        if (! empty($validated['password'])) {
            $admin->password = $validated['password'];
        }

        $admin->save();
        $admin->syncRoles([$validated['role']]);

        return response()->json(['message' => 'success'], 200);
    }

    public function destroy(string $id)
    {
        $admin = User::findOrFail($id);

        if (Auth::id() === $admin->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $admin->delete();

        return response()->json(['message' => 'success'], 200);
    }

    public function changeAdminStatus(Request $request)
    {
        $admin = User::findOrFail($request->id);

        if (Auth::id() === $admin->id) {
            return response()->json(['message' => 'You cannot change your own status.'], 422);
        }

        $admin->status = $request->status == 1 ? 0 : 1;
        $admin->save();

        return response()->json(['message' => 'success', 'status' => $admin->status, 'id' => $admin->id]);
    }
}
