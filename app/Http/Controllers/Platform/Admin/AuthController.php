<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    public function loginPage()
    {
        return view('platform_admin.pages.auth.login');
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
            return redirect()->intended(route('admin.dashboard.index', absolute: false));

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

    public function logout(Request $request)
    {
        try {
            // 1. Log the user out of the specific guard ('web' for admins/merchants)
            Auth::guard('web')->logout();

            // 2. Clear all session data for the current user
            $request->session()->invalidate();

            // 3. Regenerate the CSRF token to prevent CSRF attacks
            $request->session()->regenerateToken();

            // 4. Redirect to the appropriate login or marketing page
            return redirect()->route('admin.login');

        } catch (Throwable $e) {
            // Log the exception for debugging
            report($e);

            return back()->withErrors([
                'error' => 'An unexpected error occurred during logout. Please try again.',
            ]);
        }
    }

    /**
     * Handle an incoming authentication request.
     */
    //    public function store(AdminLoginRequest $request): RedirectResponse
    //    {

    //    }
    //
    //    /**
    //     * Destroy an authenticated session.
    //     */
    //    public function destroy(Request $request): RedirectResponse
    //    {
    //        Auth::guard('admin')->logout();
    //
    //        $request->session()->invalidate();
    //
    //        $request->session()->regenerateToken();
    //
    //        return redirect('/');
    //    }
    //
    //    public function store(Request $request): RedirectResponse
    //    {
    //        $request->validate([
    //            'name' => ['required', 'string', 'max:255'],
    //            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.Admin::class],
    //            'password' => ['required', 'confirmed', Rules\Password::defaults()],
    //        ]);
    //
    //        $user = Admin::create([
    //            'name' => $request->name,
    //            'email' => $request->email,
    //            'password' => $request->password,
    //        ]);
    //
    //        event(new Registered($user));
    //
    //        Auth::guard('admin')->login($user);
    //
    //        return redirect(route('admin.dashboard.index', absolute: false));
    //    }
    //
    //    public function create2(): View
    //    {
    //        return view('backend.auth.register');
    //    }
}
