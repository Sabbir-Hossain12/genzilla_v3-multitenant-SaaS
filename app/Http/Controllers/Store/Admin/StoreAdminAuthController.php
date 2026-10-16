<?php

namespace App\Http\Controllers\Store\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
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
            // 2. Attempt authentication using Laravel's Auth guard
            $remember = $request->boolean('remember');

            if (! Auth::attempt($credentials, $remember)) {
                // Throw validation exception if authentication fails
                throw ValidationException::withMessages([
                    'email' => ['These credentials do not match our records.'],
                ]);
            }

            // 3. Regenerate session to protect against Session Fixation attacks
            $request->session()->regenerate();

            // 4. Redirect user to intended route
            return redirect()->intended(route('admin.dashboard.index', absolute: false))->with('success', 'You are now logged in.');

        } catch (ValidationException $e) {
            // Re-throw validation exception so Laravel automatically attaches $errors to the view
            throw $e;
        } catch (Throwable $e) {
            // Log unexpected database or server errors
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

    public function register()
    {

    }

    public function logout()
    {

    }
}
