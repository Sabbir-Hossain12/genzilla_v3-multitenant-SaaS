@extends('store_admin.layout.master')

@section('title', 'Transaction #' . $transaction->transaction_id . ' - ' . (auth()->user()->tenant->store_name ?? 'Store Admin'))

@section('contents')
<div class="max-w-4xl mx-auto space-y-6 pb-12">

    {{-- Breadcrumbs & Back --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('store.admin.transactions.index') }}"
           class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-indigo-600 transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Transactions</span>
        </a>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()"
                    class="px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 transition">
                <i class="fa-solid fa-print mr-1"></i> Print Receipt
            </button>
        </div>
    </div>

    {{-- Transaction Card --}}
    <div class="card p-6 border border-slate-200 dark:border-slate-800 shadow-xs space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-5">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-extrabold uppercase tracking-wide border {{ $transaction->type_badge_class }}">
                        {{ $transaction->type }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize {{ $transaction->status_badge_class }}">
                        {{ $transaction->status }}
                    </span>
                </div>
                <h2 class="text-xl font-mono font-bold text-ink dark:text-white mt-1.5">
                    #{{ $transaction->transaction_id }}
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Recorded on {{ $transaction->created_at->format('M d, Y h:i A') }}</p>
            </div>

            <div class="text-left sm:text-right">
                <span class="text-xs text-slate-400 block uppercase font-bold tracking-wider">Transaction Amount</span>
                <span class="text-3xl font-black {{ $transaction->type === 'refund' ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                    {{ $transaction->type === 'refund' ? '-' : '+' }}৳{{ number_format($transaction->amount, 2) }}
                </span>
                @if($transaction->refunds->count() > 0)
                    <div class="text-xs text-purple-600 font-semibold mt-1">
                        ৳{{ number_format($transaction->refunded_amount, 2) }} Total Refunded
                    </div>
                @endif
            </div>
        </div>

        {{-- Meta Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-slate-50 dark:bg-slate-800/40 p-4 rounded-xl border border-slate-100 dark:border-slate-800">
            <div>
                <span class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider">Payment Method</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 mt-0.5 flex items-center gap-1.5 text-xs">
                    <i class="fa-solid {{ $transaction->method_icon }} text-slate-400"></i>
                    {{ $transaction->method_label }}
                </span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider">Currency</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 mt-0.5 text-xs">{{ $transaction->currency }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider">Paid Timestamp</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 mt-0.5 text-xs">
                    {{ $transaction->paid_at ? $transaction->paid_at->format('M d, Y h:i A') : 'Awaiting Payment' }}
                </span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider">Remaining Refundable</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 mt-0.5 text-xs">
                    ৳{{ number_format($transaction->remaining_refundable, 2) }}
                </span>
            </div>
        </div>

        {{-- Linked Order Info --}}
        @if($transaction->order)
            <div class="space-y-3">
                <h3 class="font-bold text-sm text-slate-900 dark:text-white uppercase tracking-wider text-xs">Linked Customer Order</h3>
                <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 bg-white dark:bg-slate-900 space-y-3 text-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="text-slate-500">Order Number:</span>
                            <a href="{{ route('store.admin.orders.show', $transaction->order->id) }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline ml-1">
                                #{{ $transaction->order->order_number }}
                            </a>
                        </div>
                        <div>
                            <span class="text-slate-500">Order Total:</span>
                            <span class="font-bold text-slate-900 dark:text-white ml-1">৳{{ number_format($transaction->order->total, 2) }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Customer:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $transaction->order->shipping_name }}</span>
                            <div class="text-slate-500">{{ $transaction->order->shipping_phone }}</div>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Shipping Destination:</span>
                            <div class="text-slate-700 dark:text-slate-300">{{ $transaction->order->shipping_address }}, {{ $transaction->order->shipping_city }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Gateway Metadata --}}
        @if($transaction->gateway_response)
            <div class="space-y-2">
                <h3 class="font-bold text-sm text-slate-900 dark:text-white uppercase tracking-wider text-xs">Gateway &amp; Audit Payload</h3>
                <pre class="bg-slate-900 text-emerald-400 p-4 rounded-xl font-mono text-xs overflow-x-auto leading-relaxed">{{ json_encode($transaction->gateway_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        @endif

        {{-- Associated Refunds --}}
        @if($transaction->refunds->count() > 0)
            <div class="space-y-3">
                <h3 class="font-bold text-sm text-rose-600 dark:text-rose-400 uppercase tracking-wider text-xs">Refund History</h3>
                <div class="divide-y divide-slate-100 dark:divide-slate-800 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden text-xs">
                    @foreach($transaction->refunds as $rf)
                        <div class="p-3.5 flex items-center justify-between bg-rose-50/30 dark:bg-rose-950/10">
                            <div>
                                <span class="font-mono font-bold text-rose-700 dark:text-rose-400">#{{ $rf->transaction_id }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ $rf->created_at->format('M d, Y h:i A') }}</span>
                                @if(!empty($rf->gateway_response['refund_reason']))
                                    <p class="text-slate-600 dark:text-slate-300 mt-1">Reason: {{ $rf->gateway_response['refund_reason'] }}</p>
                                @endif
                            </div>
                            <span class="font-bold text-rose-600 dark:text-rose-400 text-sm">-৳{{ number_format($rf->amount, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
