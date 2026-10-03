<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function loginPage()
    {
        return view('platform_admin.pages.auth.login');
    }

    public function login()
    {

    }

    public function logout()
    {

    }

    /**
     * Handle an incoming authentication request.
     */
//    public function store(AdminLoginRequest $request): RedirectResponse
//    {
//        $request->authenticate();
//
//        $request->session()->regenerate();
//
//        return redirect()->intended(route('admin.dashboard.index', absolute: false));
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
