@extends('store_admin.layout.master')
@section('contents')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Customers</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">View and manage registered store customer accounts, addresses, and order histories.</p>
        </div>
        @can('Store Customer Create')
            <a href="{{ route('store.admin.customers.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                <i class="fa-solid fa-plus"></i> Add Customer
            </a>
        @endcan
    </div>

    {{-- Feedback Alerts --}}
    @if(session('success'))
        <div class="rounded-2xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-300">&times;</button>
        </div>
    @endif

    {{-- Quick Stat Cards --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                <i class="fa-solid fa-users text-lg"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Total Customers</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $stats['total'] }}</span>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                <i class="fa-solid fa-user-check text-lg"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Active Accounts</span>
                <span class="text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400">{{ $stats['active'] }}</span>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400">
                <i class="fa-solid fa-user-slash text-lg"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Suspended</span>
                <span class="text-xl font-bold font-mono text-rose-600 dark:text-rose-400">{{ $stats['suspended'] }}</span>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                <i class="fa-solid fa-bag-shopping text-lg"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">With Orders</span>
                <span class="text-xl font-bold font-mono text-blue-600 dark:text-blue-400">{{ $stats['with_orders'] }}</span>
            </div>
        </div>
    </div>

    {{-- Customers Table Card --}}
    <section class="card overflow-hidden">
        {{-- Search & Filter Toolbar --}}
        <form method="GET" action="{{ route('store.admin.customers.index') }}" class="p-4 border-b border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 text-xs">
                @php $curStatus = request('status', 'all'); @endphp
                <a href="{{ route('store.admin.customers.index') }}" class="rounded-lg px-3 py-1.5 font-medium transition {{ $curStatus === 'all' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    All ({{ $stats['total'] }})
                </a>
                <a href="{{ route('store.admin.customers.index', ['status' => 'active']) }}" class="rounded-lg px-3 py-1.5 font-medium transition {{ $curStatus === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    Active ({{ $stats['active'] }})
                </a>
                <a href="{{ route('store.admin.customers.index', ['status' => 'suspended']) }}" class="rounded-lg px-3 py-1.5 font-medium transition {{ $curStatus === 'suspended' ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    Suspended ({{ $stats['suspended'] }})
                </a>
            </div>

            <div class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, phone..." class="w-64 rounded-xl border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                <button type="submit" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 transition">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
        </form>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500 dark:bg-slate-800/50 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="p-4 sm:px-6">Customer Profile</th>
                        <th class="p-4">Contact Info</th>
                        <th class="p-4">Primary Address</th>
                        <th class="p-4 text-center">Orders</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Joined</th>
                        <th class="p-4 text-right sm:px-6">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                            <td class="p-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 font-bold text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                                        {{ $customer->initials }}
                                    </div>
                                    <div>
                                        <a href="{{ route('store.admin.customers.show', $customer) }}" class="font-bold text-slate-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400 transition">
                                            {{ $customer->full_name }}
                                        </a>
                                        <div class="text-xs text-slate-400 font-mono">
                                            ID: #{{ $customer->id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-xs space-y-1">
                                <div>
                                    <a href="mailto:{{ $customer->email }}" class="text-slate-600 dark:text-slate-300 hover:text-indigo-600 flex items-center gap-1.5">
                                        <i class="fa-regular fa-envelope text-slate-400"></i> {{ $customer->email }}
                                    </a>
                                </div>
                                @if($customer->phone)
                                    <div>
                                        <a href="tel:{{ $customer->phone }}" class="text-slate-600 dark:text-slate-300 hover:text-indigo-600 flex items-center gap-1.5 font-mono">
                                            <i class="fa-solid fa-phone text-slate-400"></i> {{ $customer->phone }}
                                        </a>
                                    </div>
                                @endif
                            </td>
                            <td class="p-4 text-xs">
                                @if($customer->defaultAddress)
                                    <div class="font-medium text-slate-800 dark:text-slate-200 flex items-center gap-1">
                                        <i class="fa-solid fa-location-dot text-indigo-500 text-[11px]"></i>
                                        <span>{{ $customer->defaultAddress->title }}: {{ $customer->defaultAddress->address_1 }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $customer->defaultAddress->state_district }}, {{ $customer->defaultAddress->country }}
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">No address on file</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-slate-100 px-2 text-xs font-bold text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                                    {{ $customer->orders_count }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold border {{ $customer->status_badge_class }}">
                                    <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ ucfirst($customer->status) }}
                                </span>
                            </td>
                            <td class="p-4 text-xs text-slate-500 dark:text-slate-400 font-mono">
                                {{ $customer->created_at->format('M j, Y') }}
                            </td>
                            <td class="p-4 sm:px-6 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('store.admin.customers.show', $customer) }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-indigo-600 dark:text-slate-400 dark:hover:bg-slate-800 transition" title="View Profile">
                                        <i class="fa-regular fa-eye"></i>
                                    </a>
                                    @can('Store Customer Edit')
                                        <a href="{{ route('store.admin.customers.edit', $customer) }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-indigo-600 dark:text-slate-400 dark:hover:bg-slate-800 transition" title="Edit Customer">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </a>
                                        <form method="POST" action="{{ route('store.admin.customers.toggle', $customer) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-amber-600 dark:text-slate-400 dark:hover:bg-slate-800 transition" title="{{ $customer->status === 'active' ? 'Suspend Account' : 'Activate Account' }}">
                                                <i class="fa-solid {{ $customer->status === 'active' ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                            </button>
                                        </form>
                                    @endcan
                                    @can('Store Customer Delete')
                                        <form method="POST" action="{{ route('store.admin.customers.destroy', $customer) }}" onsubmit="return confirm('Are you sure you want to delete customer {{ $customer->full_name }}?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg p-1.5 text-slate-500 hover:bg-rose-50 hover:text-rose-600 dark:text-slate-400 dark:hover:bg-rose-950/40 transition" title="Delete Customer">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-xs text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                                        <i class="fa-solid fa-users text-xl"></i>
                                    </div>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">No customers found</span>
                                    <span>Try clearing search filters or add a new customer manually.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $customers->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
