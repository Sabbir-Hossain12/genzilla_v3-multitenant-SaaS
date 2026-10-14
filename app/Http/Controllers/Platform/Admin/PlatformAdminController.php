<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class PlatformAdminController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::orderBy('name')->get();

        $admins = User::query()
            ->permission('Platform Admin Dashboard')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('platform_admin.pages.admins.index', compact('roles', 'admins'));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->can('Create Admin')) {
            abort(403);
        }

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

        return redirect()->back()->with('success', 'Admin user created successfully.');
    }

    public function update(Request $request, string $id)
    {
        if (! Auth::user()->can('Edit Admin')) {
            abort(403);
        }

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

        return redirect()->back()->with('success', 'Admin user updated successfully.');
    }

    public function destroy(string $id)
    {
        if (! Auth::user()->can('Delete Admin')) {
            abort(403);
        }

        $admin = User::findOrFail($id);

        if (Auth::id() === $admin->id) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $admin->delete();

        return redirect()->back()->with('success', 'Admin user deleted successfully.');
    }

    public function changeAdminStatus(string $id)
    {
        if (! Auth::user()->can('Status Admin')) {
            abort(403);
        }

        $admin = User::findOrFail($id);

        if (Auth::id() === $admin->id) {
            return redirect()->back()->with('error', 'You cannot change your own status.');
        }

        $admin->status = $admin->status == 1 ? 0 : 1;
        $admin->save();

        $statusText = $admin->status == 1 ? 'activated' : 'deactivated';

        return redirect()->back()->with('success', "Admin user {$statusText} successfully.");
    }
}

