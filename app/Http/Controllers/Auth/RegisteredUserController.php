<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PlatformPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): RedirectResponse|Response
    {
        return redirect()->route('store.admin.register', $request->query());
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'store_name' => 'nullable|string|max:255',
        ]);

        $storeName = $request->input('store_name') ?: ($request->name . "'s Pharmacy");
        $subdomain = Str::slug($storeName);
        if (empty($subdomain) || Tenant::where('subdomain', $subdomain)->exists()) {
            $subdomain = ($subdomain ?: 'store') . '-' . Str::lower(Str::random(4));
        }

        $plan = PlatformPlan::where('slug', 'starter')->where('status', 1)->first()
            ?? PlatformPlan::where('status', 1)->first();

        $result = Tenant::provisionWithManualSubscription(
            userData: [
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'status' => 1,
            ],
            tenantData: [
                'store_name' => $storeName,
                'subdomain' => $subdomain,
                'support_email' => $request->email,
                'status' => 'active',
            ],
            plan: $plan,
            billingInterval: 'monthly',
            subscriptionStatus: 'trialing',
            createInvoice: true
        );

        $user = $result['user'];
        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
