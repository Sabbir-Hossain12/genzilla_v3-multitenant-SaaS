<?php

namespace App\Http\Controllers\Store\Admin\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StoreCustomerController extends Controller
{
    public function customers(Request $request)
    {
        $tenantId = tenantId();

        $query = Customer::where('tenant_id', $tenantId)
            ->with('defaultAddress')
            ->withCount('orders');

        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                  ->orWhere('last_name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $customers = $query->latest('id')->paginate(15)->withQueryString();

        $stats = [
            'total' => Customer::where('tenant_id', $tenantId)->count(),
            'active' => Customer::where('tenant_id', $tenantId)->where('status', 'active')->count(),
            'suspended' => Customer::where('tenant_id', $tenantId)->where('status', 'suspended')->count(),
            'inactive' => Customer::where('tenant_id', $tenantId)->where('status', 'inactive')->count(),
            'with_orders' => Customer::where('tenant_id', $tenantId)->has('orders')->count(),
        ];

        return view('store_admin.pages.customers.index', compact('customers', 'stats'));
    }

    public function createCustomer()
    {
        return view('store_admin.pages.customers.form', [
            'customer' => new Customer(),
            'isEdit' => false,
        ]);
    }

    public function storeCustomer(Request $request)
    {
        $tenantId = tenantId();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('customers', 'email')->where('tenant_id', $tenantId),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:6'],
            'status' => ['required', Rule::in(['active', 'suspended', 'inactive'])],

            // Optional Address fields on creation
            'address_title' => ['nullable', 'string', 'max:50'],
            'address_1' => ['nullable', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'state_district' => ['nullable', 'string', 'max:100'],
            'zip' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:100'],
        ]);

        $customer = DB::transaction(function () use ($tenantId, $validated) {
            $customer = Customer::create([
                'tenant_id' => $tenantId,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'] ?? null,
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => !empty($validated['password']) ? Hash::make($validated['password']) : Hash::make('secret1234'),
                'status' => $validated['status'] ?? 'active',
            ]);

            if (!empty($validated['address_1'])) {
                CustomerAddress::create([
                    'tenant_id' => $tenantId,
                    'customer_id' => $customer->id,
                    'title' => $validated['address_title'] ?: 'Default Address',
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'address_1' => $validated['address_1'],
                    'address_2' => $validated['address_2'] ?? null,
                    'state_district' => $validated['state_district'] ?? null,
                    'zip' => $validated['zip'] ?? null,
                    'country' => $validated['country'] ?: 'United States',
                    'is_default' => true,
                ]);
            }

            return $customer;
        });

        return redirect()->route('store.admin.customers.show', $customer)->with('success', "Customer {$customer->full_name} created successfully.");
    }

    public function showCustomer(int $id)
    {
        $tenantId = tenantId();

        $customer = Customer::where('tenant_id', $tenantId)
            ->with(['addresses', 'orders' => function ($q) {
                $q->latest('placed_at')->take(10);
            }])
            ->withCount('orders')
            ->findOrFail($id);

        $totalSpent = Order::where('tenant_id', $tenantId)
            ->where('customer_id', $customer->id)
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');

        return view('store_admin.pages.customers.show', compact('customer', 'totalSpent'));
    }

    public function editCustomer(int $id)
    {
        $customer = Customer::where('tenant_id', tenantId())->findOrFail($id);

        return view('store_admin.pages.customers.form', [
            'customer' => $customer,
            'isEdit' => true,
        ]);
    }

    public function updateCustomer(Request $request, int $id)
    {
        $tenantId = tenantId();
        $customer = Customer::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('customers', 'email')->where('tenant_id', $tenantId)->ignore($customer->id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:6'],
            'status' => ['required', Rule::in(['active', 'suspended', 'inactive'])],
        ]);

        $updates = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $updates['password'] = Hash::make($validated['password']);
        }

        $customer->update($updates);

        return redirect()->route('store.admin.customers.show', $customer)->with('success', "Customer {$customer->full_name} updated successfully.");
    }

    public function deleteCustomer(int $id)
    {
        $customer = Customer::where('tenant_id', tenantId())->findOrFail($id);
        $name = $customer->full_name;
        $customer->delete();

        return redirect()->route('store.admin.customers.index')->with('success', "Customer {$name} deleted successfully.");
    }

    public function toggleStatus(Request $request, int $id)
    {
        $customer = Customer::where('tenant_id', tenantId())->findOrFail($id);
        $customer->status = ($customer->status === 'active') ? 'suspended' : 'active';
        $customer->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'status' => $customer->status]);
        }

        return back()->with('success', "Customer status updated to {$customer->status}.");
    }

    public function storeAddress(Request $request, int $customerId)
    {
        $tenantId = tenantId();
        $customer = Customer::where('tenant_id', $tenantId)->findOrFail($customerId);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address_1' => ['required', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'state_district' => ['nullable', 'string', 'max:100'],
            'zip' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $isDefault = $request->boolean('is_default');

        DB::transaction(function () use ($tenantId, $customer, $validated, $isDefault) {
            if ($isDefault || $customer->addresses()->count() === 0) {
                $customer->addresses()->update(['is_default' => false]);
                $isDefault = true;
            }

            CustomerAddress::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customer->id,
                'title' => $validated['title'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address_1' => $validated['address_1'],
                'address_2' => $validated['address_2'] ?? null,
                'state_district' => $validated['state_district'] ?? null,
                'zip' => $validated['zip'] ?? null,
                'country' => $validated['country'] ?: 'United States',
                'is_default' => $isDefault,
            ]);
        });

        return back()->with('success', 'Address added successfully.');
    }

    public function deleteAddress(int $customerId, int $addressId)
    {
        $tenantId = tenantId();
        $customer = Customer::where('tenant_id', $tenantId)->findOrFail($customerId);

        $address = CustomerAddress::where('tenant_id', $tenantId)
            ->where('customer_id', $customer->id)
            ->findOrFail($addressId);

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $customer->addresses()->first()?->update(['is_default' => true]);
        }

        return back()->with('success', 'Address removed successfully.');
    }
}
