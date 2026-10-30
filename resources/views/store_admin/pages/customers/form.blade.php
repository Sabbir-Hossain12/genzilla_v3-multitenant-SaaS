@extends('store_admin.layout.master')
@section('contents')
@php
    $field = 'w-full rounded-xl border-slate-200 bg-white text-xs text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500 p-2.5 transition';
@endphp

<div class="space-y-6 max-w-4xl">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('store.admin.customers.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 font-semibold flex items-center gap-1">
                    <i class="fa-solid fa-arrow-left"></i> Back to Customers
                </a>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                {{ $isEdit ? 'Edit Customer: ' . $customer->full_name : 'Add New Customer' }}
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ $isEdit ? 'Update customer profile information and contact details.' : 'Register a new store customer profile and initial shipping address.' }}
            </p>
        </div>
    </div>

    {{-- Error Banner --}}
    @if($errors->any())
        <div class="rounded-2xl bg-rose-50 p-4 text-sm text-rose-800 dark:bg-rose-950/40 dark:text-rose-200 border border-rose-200 dark:border-rose-800 shadow-sm">
            <div class="font-semibold flex items-center gap-1.5 mb-1">
                <i class="fa-solid fa-circle-exclamation text-rose-600 dark:text-rose-400"></i> Please resolve the following errors:
            </div>
            <ul class="list-inside list-disc text-xs space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('store.admin.customers.update', $customer) : route('store.admin.customers.store') }}" class="space-y-6">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        {{-- Personal Details Card --}}
        <section class="card p-6 space-y-4">
            <h2 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                <i class="fa-regular fa-user text-indigo-600 dark:text-indigo-400"></i>
                Profile Information
            </h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">First Name *</label>
                    <input type="text" name="first_name" required value="{{ old('first_name', $customer->first_name) }}" placeholder="e.g. Sarah" class="{{ $field }}">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $customer->last_name) }}" placeholder="e.g. Jenkins" class="{{ $field }}">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Address *</label>
                    <input type="email" name="email" required value="{{ old('email', $customer->email) }}" placeholder="e.g. sarah@example.com" class="{{ $field }}">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" placeholder="e.g. +8801711223344" class="{{ $field }}">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Account Status *</label>
                    <select name="status" required class="{{ $field }}">
                        <option value="active" @selected(old('status', $customer->status ?? 'active') === 'active')>Active</option>
                        <option value="suspended" @selected(old('status', $customer->status) === 'suspended')>Suspended</option>
                        <option value="inactive" @selected(old('status', $customer->status) === 'inactive')>Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Password {{ $isEdit ? '(Leave empty to keep current)' : '*' }}
                    </label>
                    <input type="password" name="password" {{ $isEdit ? '' : 'required' }} minlength="6" placeholder="{{ $isEdit ? '••••••••' : 'Min. 6 characters' }}" class="{{ $field }}">
                </div>
            </div>
        </section>

        {{-- Initial Address Card (Only on Create) --}}
        @if(!$isEdit)
            <section class="card p-6 space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-indigo-600 dark:text-indigo-400"></i>
                            Initial Default Address (Optional)
                        </h2>
                        <p class="text-xs text-slate-400">Add an initial shipping address for this customer now, or add it later.</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Address Label</label>
                        <input type="text" name="address_title" value="{{ old('address_title', 'Home') }}" placeholder="e.g. Home, Office" class="{{ $field }}">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Country</label>
                        <input type="text" name="country" value="{{ old('country', 'Bangladesh') }}" class="{{ $field }}">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Street Address line 1</label>
                        <input type="text" name="address_1" value="{{ old('address_1') }}" placeholder="House, Building, Road name" class="{{ $field }}">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Street Address line 2 (Optional)</label>
                        <input type="text" name="address_2" value="{{ old('address_2') }}" placeholder="Apartment, suite, unit, floor" class="{{ $field }}">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">City / District</label>
                        <input type="text" name="state_district" value="{{ old('state_district') }}" placeholder="e.g. Dhaka, Chittagong" class="{{ $field }}">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Postal / Zip Code</label>
                        <input type="text" name="zip" value="{{ old('zip') }}" placeholder="e.g. 1212" class="{{ $field }}">
                    </div>
                </div>
            </section>
        @endif

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('store.admin.customers.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 transition">
                Cancel
            </a>
            <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-floppy-disk"></i> {{ $isEdit ? 'Update Customer' : 'Create Customer' }}
            </button>
        </div>
    </form>
</div>
@endsection
