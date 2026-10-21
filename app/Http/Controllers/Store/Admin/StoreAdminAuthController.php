<?php

namespace App\Http\Controllers\Store\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class StoreAdminAuthController extends Controller
{
    public function loginPage()
    {
        return view('store_admin.pages.auth.login');
    }

    public function login(Request $request)
    {
        // 1. Validate incoming request
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $remember = $request->boolean('remember');

            // 2. Find the user by email
            $user = User::where('email', $credentials['email'])->first();

            // 3. Verify user exists and password matches
            if (! $user || ! Hash::check($credentials['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['These credentials do not match our records.'],
                ]);
            }

            // 4. Check if the user has an associated tenant row
            $hasTenant = Tenant::where('user_id', $user->id)->exists();

            if (! $hasTenant) {
                throw ValidationException::withMessages([
                    'email' => ['This account does not have an associated store account.'],
                ]);
            }

            // 5. Manually log the user in
            Auth::login($user, $remember);

            // 6. Regenerate session to protect against Session Fixation attacks
            $request->session()->regenerate();

            // 7. Redirect user to dashboard
            return redirect()->intended(route('store.admin.dashboard', absolute: false))->with('success', 'You are now logged in.');

        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'email' => ['An unexpected error occurred. Please try again later.'],
            ])->onlyInput('email');
        }
    }
    public function registerPage(Request $request)
    {
        // 1. Read query parameters with fallbacks
        $selectedPlanSlug = $request->query('plan', 'starter');
        $selectedInterval = $request->query('interval', 'monthly');

        // 2. Fetch available active plans
        $plans = PlatformPlan::where('status', 1)->get();

        // 3. Find the currently selected plan (fallback to first plan if slug is invalid)
        $selectedPlan = $plans->firstWhere('slug', $selectedPlanSlug) ?? $plans->first();

        // 4. Return standard Blade view
        return view('store_admin.pages.auth.register', compact('plans', 'selectedPlan', 'selectedInterval'));
    }

    public function register(Request $request)
    {
        // 1. Validate incoming registration inputs
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'store_name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required',
                'string',
                'alpha_dash',
                'max:100',
                'unique:tenants,subdomain',
                'not_in:admin,store,platform,api,mail,root,system,app,www,billing,help,support',
            ],
            'plan_slug' => ['nullable', 'string', 'exists:platform_plans,slug'],
            'plan_id' => ['nullable', 'exists:platform_plans,id'],
            'billing_interval' => ['nullable', 'string', 'in:monthly,yearly'],
        ]);

        $subdomain = strtolower(trim($validated['subdomain'], '-'));

        // 2. Resolve selected subscription plan
        $plan = null;
        if (! empty($validated['plan_id'])) {
            $plan = PlatformPlan::where('id', $validated['plan_id'])->where('status', 1)->first();
        } elseif (! empty($validated['plan_slug'])) {
            $plan = PlatformPlan::where('slug', $validated['plan_slug'])->where('status', 1)->first();
        }

        if (! $plan) {
            $plan = PlatformPlan::where('slug', 'starter')->where('status', 1)->first()
                ?? PlatformPlan::where('status', 1)->first();
        }

        if (! $plan) {
            throw ValidationException::withMessages([
                'plan_slug' => ['No active subscription plan is currently available. Please contact platform support.'],
            ]);
        }

        $billingInterval = $validated['billing_interval'] ?? 'monthly';

        try {
            // 3. Provision Merchant User, Tenant, Domain, 14-day Free Trial Subscription, Invoice, and Usages
            // Following the TenantSubscriptionSeeder sequence (User -> Tenant -> Domain -> Subscription -> Invoice -> Usage)
            $result = Tenant::provisionWithManualSubscription(
                userData: [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'status' => 1,
                ],
                tenantData: [
                    'store_name' => $validated['store_name'],
                    'subdomain' => $subdomain,
                    'support_email' => $validated['email'],
                    'support_phone' => null,
                    'currency' => 'USD',
                    'timezone' => 'UTC',
                    'status' => 'active',
                ],
                plan: $plan,
                billingInterval: $billingInterval,
                subscriptionStatus: 'trialing',
                createInvoice: true
            );

            $user = $result['user'];
            $tenant = $result['tenant'];

            // 4. Fire standard Registered event
            event(new Registered($user));

            // 6. Resolve destination URL (merchant storefront subdomain or dashboard)


            return redirect($targetUrl)->with('success', 'Your store ' . $tenant->store_name . ' and 14-day free trial have been activated.');

        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->withErrors([
                'email' => ['Registration failed: ' . $e->getMessage()],
            ]);
        }
    }

    public function logout(Request $request)
    {

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
