<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
use Database\Seeders\PermissionSeeder;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class AuthTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. If the user is not logged in, redirect to your custom store login route
        if (! $user) {
            return redirect()->route('store.admin.login')
                ->with('error', 'Please log in to access the admin panel.');
        }

        // 2. If the user is logged in, but does not have a tenant row
        if (! $user->tenant) {
            // Optional: Log them out so their invalid session doesn't linger
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('store.admin.login')
                ->with('error', 'This account does not have an associated store.');
        }

        $permissions = app(PermissionRegistrar::class);
        $permissions->setPermissionsTeamId($user->tenant->id);

        // The tenant owner is the initial Store Owner. Repair older tenants that
        // have the role row but were created before its permissions were seeded.
        if ((int) $user->tenant->user_id === (int) $user->id) {
            $ownerRole = Role::firstOrCreate([
                'tenant_id' => $user->tenant->id,
                'name' => 'Store Owner',
                'guard_name' => 'web',
            ]);

            if (! $ownerRole->permissions()->exists()) {
                $storePermissions = [];
                foreach (PermissionSeeder::STOREFRONT_GROUPS as $groupName => $permissionNames) {
                    foreach ($permissionNames as $permissionName) {
                        $storePermissions[] = Permission::firstOrCreate(
                            ['name' => $permissionName, 'guard_name' => 'web'],
                            ['group_name' => $groupName]
                        );
                    }
                }
                $ownerRole->syncPermissions($storePermissions);
            }

            if (! $user->hasRole($ownerRole)) {
                $user->assignRole($ownerRole);
            }
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
    }
}
