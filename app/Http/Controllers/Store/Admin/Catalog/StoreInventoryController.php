<?php

namespace App\Http\Controllers\Store\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\InventoryBatch;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreInventoryController extends Controller
{
    public function inventory()
    {
        $variants = ProductVariant::where('tenant_id', tenantId())->whereHas('product')->with(['product', 'batches' => fn($q) => $q->orderBy('expiry_date')])->orderBy('sku')->paginate(25);
        $movements = StockMovement::where('tenant_id',tenantId())->with(['variant.product' => fn($q) => $q->withTrashed(), 'user'])->latest('created_at')->limit(50)->get();
        return view('store_admin.pages.catalog.inventory', compact('variants', 'movements'));
    }

    public function receiveStock(Request $request, int $variant)
    {
        $variant = ProductVariant::where('tenant_id', tenantId())->findOrFail($variant);
        $data = $request->validate([
            'batch_no' => ['required', 'string', 'max:100'], 'quantity' => ['required', 'integer', 'min:1'],
            'expiry_date' => ['nullable', 'date'], 'cost_price' => ['nullable', 'numeric', 'min:0'],
        ]);
        DB::transaction(fn() => $this->receiveIntoBatch($variant, $data['quantity'], $data['batch_no'], $data['expiry_date'] ?? null, $data['cost_price'] ?? null, 'Stock received'));
        return back()->with('success', 'Stock received into batch ' . $data['batch_no'] . '.');
    }

    private function receiveIntoBatch(ProductVariant $variant, int $quantity, string $batchNo, ?string $expiry, ?float $cost, string $note): void
    {
        $batch = InventoryBatch::firstOrCreate(
            ['tenant_id' => tenantId(), 'variant_id' => $variant->id, 'batch_no' => $batchNo],
            ['quantity' => 0, 'reserved' => 0, 'expiry_date' => $expiry, 'cost_price' => $cost, 'received_at' => now(), 'status' => 'active']
        );
        $batch->increment('quantity', $quantity);
        $batch->update(['received_at' => now(), 'expiry_date' => $expiry ?? $batch->expiry_date, 'cost_price' => $cost ?? $batch->cost_price]);
        StockMovement::create(['tenant_id' => tenantId(), 'variant_id' => $variant->id, 'batch_id' => $batch->id, 'type' => 'receive', 'qty_delta' => $quantity, 'reserved_delta' => 0, 'user_id' => auth()->id(), 'note' => $note]);
    }

    public function adjustStock(Request $request, int $batch)
    {
        $data = $request->validate(['quantity_delta' => ['nullable', 'integer', 'not_in:0'], 'note' => ['nullable', 'string', 'max:1000'], 'status' => ['nullable', Rule::in(['active', 'quarantined', 'recalled'])]]);
        if (!$request->filled('quantity_delta') && !$request->filled('status')) {
            throw \Illuminate\Validation\ValidationException::withMessages(['quantity_delta' => 'Enter a quantity adjustment or choose a batch status.']);
        }
        $adjusted = DB::transaction(function () use ($batch, $data) {
            $batch = InventoryBatch::where('tenant_id', tenantId())->lockForUpdate()->findOrFail($batch);
            $delta = (int)($data['quantity_delta'] ?? 0);
            $next = $batch->quantity + $delta;
            if ($next < $batch->reserved) return false;
            $batch->update(['quantity' => $next, 'status' => $data['status'] ?? $batch->status]);
            if ($delta !== 0) StockMovement::create(['tenant_id' => tenantId(), 'variant_id' => $batch->variant_id, 'batch_id' => $batch->id, 'type' => 'adjust', 'qty_delta' => $delta, 'reserved_delta' => 0, 'user_id' => auth()->id(), 'note' => $data['note'] ?? null]);
            return true;
        });
        if (!$adjusted) return back()->withErrors(['quantity_delta' => 'Adjustment cannot reduce stock below the reserved quantity.']);
        return back()->with('success', 'Inventory adjusted.');
    }

}
