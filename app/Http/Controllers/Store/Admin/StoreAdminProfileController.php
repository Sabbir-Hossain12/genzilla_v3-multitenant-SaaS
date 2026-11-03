<?php

namespace App\Http\Controllers\Store\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class StoreAdminProfileController extends Controller
{
    /**
     * Display the authenticated store admin's profile page.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $tenant = Tenant::find(tenantId());
        $roles = $user->getRoleNames();
        $permissionsCount = $user->getAllPermissions()->count();

        return view('store_admin.pages.profile.index', [
            'user' => $user,
            'tenant' => $tenant,
            'roles' => $roles,
            'permissionsCount' => $permissionsCount,
        ]);
    }

    /**
     * Update the authenticated store admin's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'img' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if ($request->hasFile('img')) {
            if ($user->img && Storage::disk('public')->exists($user->img)) {
                Storage::disk('public')->delete($user->img);
            }

            $user->img = $request->file('img')->store('avatars', 'public');
        }

        $user->save();

        return redirect()->route('store.admin.profile.edit')->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the authenticated store admin's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();
        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('store.admin.profile.edit')->with('success', 'Password updated successfully.');
    }
}
