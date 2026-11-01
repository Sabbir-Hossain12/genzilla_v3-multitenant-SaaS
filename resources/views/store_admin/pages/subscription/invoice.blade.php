@extends('store_admin.layout.master')

@section('title', 'Invoice ' . $invoice->invoice_number . ' - ' . ($invoice->tenant->store_name ?? 'Store Admin'))

@section('contents')
<div class="max-w-3xl mx-auto space-y-6 pb-12">
    <div class="flex items-center justify-between">
        <a href="{{ route('store.admin.subscription.index') }}"
           class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-indigo-600 transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Subscription & Billing</span>
        </a>
        <button type="button" onclick="window.print()"
                class="px-4 py-2 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold text-xs shadow-xs hover:opacity-90 flex items-center gap-1.5">
            <i class="fa-solid fa-print"></i>
            <span>Print Invoice</span>
        </button>
    </div>

    <div class="card p-8 md:p-10 rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm space-y-8">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6 pb-6 border-b border-slate-100 dark:border-slate-800">
            <div>
                <span class="text-xs font-extrabold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">GENZILLA MULTI-TENANT SAAS</span>
                <h1 class="text-2xl font-extrabold text-ink dark:text-white mt-1">Invoice Receipt</h1>
                <p class="text-xs text-slate-400 mt-1 font-mono">Invoice #: <strong>{{ $invoice->invoice_number }}</strong></p>
                <p class="text-xs text-slate-400">Date: {{ $invoice->created_at ? $invoice->created_at->format('F d, Y') : '-' }}</p>
            </div>
            <div class="sm:text-right">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $invoice->status === 'paid' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                    <i class="fa-solid fa-circle-check text-[10px]"></i>
                    <span class="uppercase">{{ $invoice->status }}</span>
                </span>
                <p class="text-xs text-slate-400 mt-2">Paid on: {{ $invoice->paid_at ? $invoice->paid_at->format('F d, Y') : '-' }}</p>
            </div>
        </div>

        {{-- Billed To & Subscription Details --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs pb-6 border-b border-slate-100 dark:border-slate-800">
            <div>
                <p class="font-bold text-slate-400 uppercase tracking-wider text-[11px]">Billed To Store:</p>
                <p class="text-base font-extrabold text-ink dark:text-white mt-1">{{ $invoice->tenant->store_name ?? 'Pharmacy Store' }}</p>
                <p class="text-slate-500 dark:text-slate-400 mt-0.5">Subdomain: {{ $invoice->tenant->subdomain }}.{{ config('app.base_domain') }}</p>
                <p class="text-slate-500 dark:text-slate-400">Email: {{ $invoice->tenant->support_email ?? 'N/A' }}</p>
            </div>
            <div class="sm:text-right">
                <p class="font-bold text-slate-400 uppercase tracking-wider text-[11px]">Billing Coverage:</p>
                <p class="text-sm font-bold text-ink dark:text-white mt-1">
                    @if($invoice->billing_period_start && $invoice->billing_period_end)
                        {{ $invoice->billing_period_start->format('M d, Y') }} &rarr; {{ $invoice->billing_period_end->format('M d, Y') }}
                    @else
                        Subscription Cycle
                    @endif
                </p>
                <p class="text-slate-500 dark:text-slate-400 mt-0.5 capitalize">Cycle: {{ $invoice->subscription?->billing_interval ?? 'Monthly' }}</p>
            </div>
        </div>

        {{-- Items Table --}}
        <div>
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 uppercase font-semibold text-[11px] border-b border-slate-100 dark:border-slate-800">
                        <th class="py-3">Subscription Item</th>
                        <th class="py-3 text-center">Interval</th>
                        <th class="py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr>
                        <td class="py-4">
                            <p class="font-bold text-ink dark:text-white text-sm">
                                {{ $invoice->subscription?->plan?->name ?? 'Pharmacy SaaS Plan' }}
                            </p>
                            <p class="text-slate-400 text-xs mt-0.5">
                                High-performance multi-tenant pharmacy platform with catalog, inventory, and domain tools.
                            </p>
                        </td>
                        <td class="py-4 text-center capitalize font-semibold text-slate-600 dark:text-slate-300">
                            {{ $invoice->subscription?->billing_interval ?? 'Monthly' }}
                        </td>
                        <td class="py-4 text-right font-extrabold text-ink dark:text-white text-sm">
                            ${{ number_format($invoice->subtotal, 2) }}
                        </td>
                    </tr>
                </tbody>
                <tfoot class="border-t border-slate-200 dark:border-slate-700">
                    <tr>
                        <td colspan="2" class="py-3 text-right text-slate-500">Subtotal:</td>
                        <td class="py-3 text-right font-bold text-slate-800 dark:text-slate-200">${{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="py-2 text-right text-slate-500">Taxes & Fees (0%):</td>
                        <td class="py-2 text-right font-bold text-slate-800 dark:text-slate-200">${{ number_format($invoice->tax, 2) }}</td>
                    </tr>
                    <tr class="text-base font-extrabold">
                        <td colspan="2" class="py-3 text-right text-ink dark:text-white">Total Settled:</td>
                        <td class="py-3 text-right text-emerald-600 dark:text-emerald-400">${{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Footer Note --}}
        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-400">
            <span>Thank you for choosing Genzilla SaaS platform.</span>
            <span>Support: support@genzilla.com</span>
        </div>
    </div>
</div>
@endsection
