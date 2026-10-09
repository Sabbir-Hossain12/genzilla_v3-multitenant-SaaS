<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\TenantInvoice;
use Illuminate\Http\Request;

class TenantInvoiceController extends Controller
{
    public function index()
    {
        $invoices = TenantInvoice::with(['tenant', 'subscription.plan'])->latest()->get();
        return view('platform_admin.pages.invoices.index', compact('invoices'));
    }

    public function show(TenantInvoice $invoice)
    {
        $invoice->load(['tenant', 'subscription.plan']);
        return view('platform_admin.pages.invoices.show', compact('invoice'));
    }

    public function updateStatus(Request $request, TenantInvoice $invoice)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:draft,open,paid,uncollectible,void',
        ]);

        if ($validated['status'] === 'paid' && !$invoice->paid_at) {
            $invoice->paid_at = now();
        }

        $invoice->status = $validated['status'];
        $invoice->save();

        return redirect()->back()->with('success', 'Invoice status updated.');
    }
}
