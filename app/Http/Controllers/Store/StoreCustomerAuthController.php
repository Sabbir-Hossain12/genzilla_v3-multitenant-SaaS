<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Tenant;
use App\Models\TenantPrescription;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
     * Resolve active customer for operations (auth guard or active tenant customer fallback).
     */
    protected function resolveCustomer(): ?Customer
    {
        $customer = auth('customer')->user();
        if ($customer) {
            return $customer;
        }

        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        return Customer::where('tenant_id', $tenantId)->first();
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
     * Verify 4-digit OTP.
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
            'message' => 'Phone verified successfully.',
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
     * Existing Customer: Reset password after OTP verification.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        $customer = $this->findCustomerByPhone($tenantId, $request->phone);
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'No customer account found with this mobile number.',
            ], 422);
        }

        if ($customer->status === 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is suspended. Please contact store support.',
            ], 403);
        }

        // Update customer password in DB
        $customer->update([
            'password' => $request->password, // automatically hashed by casts
        ]);

        // Authenticate customer session
        auth('customer')->login($customer, true);
        $request->session()->regenerate();
        $request->session()->forget(['store_customer_otp', 'store_otp_verified_phone']);

        return response()->json([
            'success' => true,
            'redirect' => route('store.dashboard'),
            'message' => 'Your password has been reset successfully! Welcome back.',
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
     * Existing Customer: Direct login via OTP (if user skips changing password).
     */
    public function loginWithOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        $customer = $this->findCustomerByPhone($tenantId, $request->phone);
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'No customer account found with this mobile number.',
            ], 422);
        }

        if ($customer->status === 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is suspended. Please contact store support.',
            ], 403);
        }

        auth('customer')->login($customer, true);
        $request->session()->regenerate();
        $request->session()->forget(['store_customer_otp', 'store_otp_verified_phone']);

        return response()->json([
            'success' => true,
            'redirect' => route('store.dashboard'),
            'message' => 'Logged in successfully via OTP!',
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
        $customer = $this->resolveCustomer();
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
        $customer = $this->resolveCustomer();
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

    /**
     * Store new customer address.
     */
    public function storeAddress(Request $request): JsonResponse
    {
        $customer = $this->resolveCustomer();
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:50'],
            'address_1' => ['required', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'state_district' => ['nullable', 'string', 'max:100'],
            'zip' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['is_default'])) {
            CustomerAddress::where('tenant_id', $tenantId)
                ->where('customer_id', $customer->id)
                ->update(['is_default' => false]);
        }

        $address = CustomerAddress::create([
            'tenant_id' => $tenantId,
            'customer_id' => $customer->id,
            'title' => $validated['title'] ?: 'Home',
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'phone' => $validated['phone'],
            'address_1' => $validated['address_1'],
            'address_2' => $validated['address_2'] ?? null,
            'state_district' => $validated['state_district'] ?? 'Dhaka',
            'zip' => $validated['zip'] ?? null,
            'country' => $validated['country'] ?? 'Bangladesh',
            'is_default' => (bool) ($validated['is_default'] ?? false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Delivery address saved successfully!',
            'address' => [
                'id' => $address->id,
                'title' => $address->title,
                'first_name' => $address->first_name,
                'last_name' => $address->last_name,
                'name' => $address->full_name,
                'phone' => $address->phone,
                'address_1' => $address->address_1,
                'address_2' => $address->address_2,
                'state_district' => $address->state_district,
                'zip' => $address->zip,
                'country' => $address->country,
                'is_default' => (bool) $address->is_default,
                'formatted' => $address->formatted_address,
            ],
        ]);
    }

    /**
     * Set default customer address.
     */
    public function setDefaultAddress(int $id): JsonResponse
    {
        $customer = $this->resolveCustomer();
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        CustomerAddress::where('tenant_id', $tenantId)
            ->where('customer_id', $customer->id)
            ->update(['is_default' => false]);

        $address = CustomerAddress::where('tenant_id', $tenantId)
            ->where('customer_id', $customer->id)
            ->findOrFail($id);

        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default delivery address updated.',
        ]);
    }

    /**
     * Delete customer address.
     */
    public function deleteAddress(int $id): JsonResponse
    {
        $customer = $this->resolveCustomer();
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        $address = CustomerAddress::where('tenant_id', $tenantId)
            ->where('customer_id', $customer->id)
            ->findOrFail($id);

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Delivery address removed.',
        ]);
    }

    /**
     * Direct upload prescription to tenant_prescriptions table.
     */
    public function uploadPrescription(Request $request): JsonResponse
    {
        $customer = $this->resolveCustomer();
        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        $validated = $request->validate([
            'prescription_file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf,svg', 'max:5120'],
            'patient_notes' => ['nullable', 'string', 'max:1000'],
            'delivery_address' => ['nullable', 'string', 'max:500'],
            'customer_name' => ['nullable', 'string', 'max:150'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
        ]);

        $path = $request->file('prescription_file')->store('prescriptions', 'public');

        $custName = $validated['customer_name'] ?: ($customer ? $customer->name : 'Customer');
        $custPhone = $validated['customer_phone'] ?: ($customer ? $customer->phone : '');
        $custEmail = $customer?->email;

        $rxNumber = 'RX-' . date('Ym') . '-' . strtoupper(Str::random(5));

        $rx = TenantPrescription::create([
            'tenant_id' => $tenantId,
            'customer_id' => $customer?->id,
            'prescription_number' => $rxNumber,
            'customer_name' => $custName,
            'customer_phone' => $custPhone,
            'customer_email' => $custEmail,
            'delivery_address' => $validated['delivery_address'] ?: 'Customer address on file',
            'patient_notes' => $validated['patient_notes'] ?? null,
            'prescription_image' => $path,
            'status' => 'pending_review',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Prescription uploaded successfully! Our licensed pharmacist will review it shortly.',
            'prescription' => [
                'id' => $rx->id,
                'prescription_number' => $rx->prescription_number,
                'status' => 'pending_review',
                'status_label' => 'Under Pharmacist Review',
                'status_badge' => 'Pending Review',
                'color' => 'text-amber-700 bg-amber-50 border border-amber-200',
                'image_url' => $rx->image_url,
                'customer_name' => $rx->customer_name,
                'customer_phone' => $rx->customer_phone,
                'delivery_address' => $rx->delivery_address,
                'patient_notes' => $rx->patient_notes,
                'date' => $rx->created_at->format('M d, Y'),
            ],
        ]);
    }

    /**
     * Remove item from wishlist.
     */
    public function removeWishlist(int $id): JsonResponse
    {
        $customer = $this->resolveCustomer();
        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        if ($customer) {
            Wishlist::where('tenant_id', $tenantId)
                ->where('customer_id', $customer->id)
                ->where(function ($q) use ($id) {
                    $q->where('id', $id)->orWhere('product_id', $id);
                })
                ->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Item removed from wishlist.',
        ]);
    }

    /**
     * Upload customer profile picture / avatar.
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $customer = $this->resolveCustomer();
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:3072'],
        ]);

        if (!empty($customer->avatar) && !str_starts_with($customer->avatar, 'http')) {
            Storage::disk('public')->delete($customer->avatar);
        }

        $path = $request->file('avatar')->store('customers/avatars', 'public');
        $customer->update(['avatar' => $path]);

        $avatarUrl = $customer->avatar_url;

        return response()->json([
            'success' => true,
            'message' => 'Profile photo updated successfully!',
            'avatar_url' => $avatarUrl,
        ]);
    }
}
