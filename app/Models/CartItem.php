<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function variant(): BelongsTo
    {
        return $this->productVariant();
    }

    public function getUnitPriceAttribute(): float
    {
        return (float) ($this->productVariant?->price ?? $this->product?->defaultVariant?->price ?? 0);
    }

    public function getLineTotalAttribute(): float
    {
        return (float) ($this->unit_price * $this->quantity);
    }
}
