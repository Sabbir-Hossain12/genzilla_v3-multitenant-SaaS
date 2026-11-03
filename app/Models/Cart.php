<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'recovery_contacted_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeAbandoned(Builder $query, int $hours = 2): Builder
    {
        return $query->where('updated_at', '<=', now()->subHours($hours));
    }

    public function scopeRecoverable(Builder $query): Builder
    {
        return $query->whereNotNull('customer_id')->has('items');
    }

    public function scopeContacted(Builder $query): Builder
    {
        return $query->whereNotNull('recovery_contacted_at');
    }

    public function scopeUncontacted(Builder $query): Builder
    {
        return $query->whereNull('recovery_contacted_at');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getTotalItemsCountAttribute(): int
    {
        if ($this->relationLoaded('items')) {
            return (int) $this->items->sum('quantity');
        }
        return (int) $this->items()->sum('quantity');
    }

    public function getSubtotalAttribute(): float
    {
        if ($this->relationLoaded('items')) {
            return (float) $this->items->sum(function ($item) {
                $price = $item->productVariant?->price ?? $item->product?->defaultVariant?->price ?? 0;
                return (float) $price * (int) $item->quantity;
            });
        }

        $items = $this->items()->with(['productVariant', 'product.defaultVariant'])->get();
        return (float) $items->sum(function ($item) {
            $price = $item->productVariant?->price ?? $item->product?->defaultVariant?->price ?? 0;
            return (float) $price * (int) $item->quantity;
        });
    }

    public function getHasPrescriptionItemsAttribute(): bool
    {
        if ($this->relationLoaded('items')) {
            return $this->items->contains(function ($item) {
                return (bool) ($item->product?->requires_prescription ?? false);
            });
        }

        return $this->items()->whereHas('product', function ($q) {
            $q->where('requires_prescription', true);
        })->exists();
    }

    public function getIsContactedAttribute(): bool
    {
        return !is_null($this->recovery_contacted_at);
    }

    public function getIdleTimeAttribute(): string
    {
        return $this->updated_at ? $this->updated_at->diffForHumans() : 'N/A';
    }

    public function getContactedBadgeClassAttribute(): string
    {
        return $this->is_contacted
            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
            : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-200 dark:border-amber-800';
    }
}
