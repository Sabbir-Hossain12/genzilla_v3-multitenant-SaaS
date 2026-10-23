<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
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

        if (! $user->status) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('store.admin.login')
                ->with('error', 'This store admin account is inactive.');
        }

        // Owners resolve through tenants.user_id. Store staff resolve through
        // their tenant-scoped role pivot.
        $tenant = $user->tenant;
        if (! $tenant) {
            $pivot = config('permission.table_names.model_has_roles');
            $teamKey = config('permission.column_names.team_foreign_key');
            $roleKey = config('permission.column_names.role_pivot_key') ?: 'role_id';
            $tenantIds = DB::table($pivot.' as model_roles')
                ->join(config('permission.table_names.roles').' as tenant_roles', function ($join) use ($pivot, $teamKey, $roleKey) {
                    $join->on('tenant_roles.id', '=', 'model_roles.'.$roleKey)
                        ->on('tenant_roles.'.$teamKey, '=', 'model_roles.'.$teamKey);
                })
                ->where('model_roles.model_type', $user->getMorphClass())
                ->where('model_roles.model_id', $user->id)
                ->where('tenant_roles.guard_name', 'web')
                ->distinct()
                ->pluck('model_roles.'.$teamKey);

            if ($tenantIds->count() === 1) {
                $tenant = Tenant::find($tenantIds->first());
            }
        }

        if (! $tenant) {
            // Optional: Log them out so their invalid session doesn't linger
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('store.admin.login')
                ->with('error', 'This account does not have an associated store.');
        }

        $user->setRelation('tenant', $tenant);

        $permissions = app(PermissionRegistrar::class);
        $permissions->setPermissionsTeamId($tenant->id);

        // The tenant owner is the initial Store Owner. Repair older tenants that
        // have the role row but were created before its permissions were seeded.
        if ((int) $tenant->user_id === (int) $user->id) {
            $ownerRole = Role::firstOrCreate([
                'tenant_id' => $tenant->id,
                'name' => 'Store Owner',
                'guard_name' => 'web',
            ]);

            $storePermissions = [];
            foreach (PermissionSeeder::STOREFRONT_GROUPS as $groupName => $permissionNames) {
                foreach ($permissionNames as $permissionName) {
                    $storePermissions[] = Permission::updateOrCreate(
                        ['name' => $permissionName, 'guard_name' => 'web'],
                        ['group_name' => $groupName, 'panel' => 'store']
                    );
                }
            }
            $ownerRole->syncPermissions($storePermissions);

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
