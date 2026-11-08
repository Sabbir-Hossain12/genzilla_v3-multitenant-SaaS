<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StoreCustomerAuthController extends Controller
{
    /**
     * Find customer by flexible phone match within current tenant.
     */
    protected function findCustomerByPhone(int $tenantId, string $rawPhone): ?Customer
    {
        $digits = preg_replace('/[^0-9]/', '', $rawPhone);
        if (empty($digits)) {
            return null;
        }

        // Local 11-digit e.g. 01711223344
        if (str_starts_with($digits, '880')) {
            $local = '0' . substr($digits, 3);
        } elseif (str_starts_with($digits, '0')) {
            $local = $digits;
        } else {
            $local = '0' . $digits;
        }

        $candidates = array_unique([
            $rawPhone,
            trim($rawPhone),
            $local,
            '+88' . $local,
            '88' . $local,
            substr($local, 1),
            '+880' . substr($local, 1),
        ]);

        return Customer::where('tenant_id', $tenantId)
            ->where(function ($query) use ($candidates) {
                foreach ($candidates as $candidate) {
                    $query->orWhere('phone', $candidate);
                }
            })
            ->first();
    }

    /**
     * Step 1: Check whether mobile number exists for tenant.
     */
    public function checkPhone(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        $customer = $this->findCustomerByPhone($tenantId, $request->phone);

        if ($customer) {
            return response()->json([
                'exists' => true,
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'first_name' => $customer->first_name,
                    'phone' => $customer->phone,
                ],
                'message' => 'Welcome back! Please enter your password.',
            ]);
        }

        // For new customers, simulate sending OTP code (1234 for demo/dev)
        $otp = '1234';
        session([
            'store_customer_otp' => [
                'phone' => $request->phone,
                'code' => $otp,
                'expires_at' => now()->addMinutes(10),
            ],
        ]);

        return response()->json([
            'exists' => false,
            'otp_sent' => true,
            'demo_otp' => $otp,
            'message' => 'Verification code sent to your phone (Use 1234 for demo).',
        ]);
    }

    /**
     * Existing Customer: Log in with password.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        $customer = $this->findCustomerByPhone($tenantId, $request->phone);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this mobile number.',
            ], 422);
        }

        if ($customer->status === 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is suspended. Please contact store support.',
            ], 403);
        }

        // If customer has no password set yet (e.g. seeded or guest checkout)
        if (empty($customer->password)) {
            // Allow login and prompt to set password, or verify default password 'password'
            if ($request->password === 'password' || $request->password === '123456') {
                $customer->update(['password' => $request->password]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Incorrect password. Default initial password is "password".',
                ], 422);
            }
        } elseif (!Hash::check($request->password, $customer->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password. Please try again.',
            ], 422);
        }

        auth('customer')->login($customer, true);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'redirect' => route('store.dashboard'),
            'message' => 'Logged in successfully!',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'first_name' => $customer->first_name,
                'phone' => $customer->phone,
                'email' => $customer->email,
            ],
        ]);
    }

    /**
     * New Customer: Verify 4-digit OTP.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'otp' => ['required', 'string', 'size:4'],
        ]);

        $sessionOtp = session('store_customer_otp');

        // Accept '1234' or exact session code
        $isValid = ($request->otp === '1234')
            || ($sessionOtp && isset($sessionOtp['code']) && $sessionOtp['code'] === $request->otp);

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code. Please enter 1234.',
            ], 422);
        }

        session(['store_otp_verified_phone' => $request->phone]);

        return response()->json([
            'success' => true,
            'verified' => true,
            'message' => 'Phone verified successfully. Please set your password.',
        ]);
    }

    /**
     * New Customer: Set Name & Password and complete registration.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        // Check if customer already exists
        $existing = $this->findCustomerByPhone($tenantId, $request->phone);
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'An account already exists with this phone number. Please login with your password.',
            ], 422);
        }

        // Email resolution
        $email = $request->email;
        if (empty($email)) {
            $digits = preg_replace('/[^0-9]/', '', $request->phone);
            $subdomain = $tenant?->subdomain ?? 'medimart';
            $email = "user_{$digits}@{$subdomain}.local";
        }

        if (Customer::where('tenant_id', $tenantId)->where('email', $email)->exists()) {
            $email = time() . '_' . $email;
        }

        $customer = Customer::create([
            'tenant_id' => $tenantId,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'email' => $email,
            'password' => $request->password, // automatically hashed by casts
            'status' => 'active',
        ]);

        auth('customer')->login($customer, true);
        $request->session()->regenerate();
        $request->session()->forget(['store_customer_otp', 'store_otp_verified_phone']);

        return response()->json([
            'success' => true,
            'redirect' => route('store.dashboard'),
            'message' => 'Welcome to ' . ($tenant?->name ?? 'our store') . '! Your account has been created.',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'first_name' => $customer->first_name,
                'phone' => $customer->phone,
                'email' => $customer->email,
            ],
        ]);
    }

    /**
     * Log out customer.
     */
    public function logout(Request $request)
    {
        auth('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => '/',
                'message' => 'Logged out successfully.',
            ]);
        }

        return redirect('/');
    }

    /**
     * Update customer profile from dashboard settings.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
        ]);

        $customer->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone ?: $customer->phone,
            'email' => $request->email ?: $customer->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'phone' => $customer->phone,
                'email' => $customer->email,
            ],
        ]);
    }

    /**
     * Update customer password from dashboard settings.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if (!empty($customer->password) && !Hash::check($request->current_password, $customer->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password does not match.',
            ], 422);
        }

        $customer->update([
            'password' => $request->password,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully!',
        ]);
    }
}
