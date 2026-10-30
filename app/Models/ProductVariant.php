<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['track_inventory' => 'boolean', 'is_default' => 'boolean'];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function batches(): HasMany { return $this->hasMany(InventoryBatch::class, 'variant_id'); }
    public function activeBatches(): HasMany { return $this->hasMany(InventoryBatch::class, 'variant_id')->where('status', 'active'); }
    public function movements(): HasMany { return $this->hasMany(StockMovement::class, 'variant_id'); }
    public function attributeValues() { return $this->belongsToMany(ProductAttributeValue::class, 'product_variant_attribute_values', 'variant_id', 'attribute_value_id')->withPivot('attribute_id', 'tenant_id'); }

    public function getStockOnHandAttribute(): int
    {
        return (int) ($this->relationLoaded('batches')
            ? $this->batches->sum('quantity')
            : $this->batches()->sum('quantity'));
    }

    public function getReservedStockAttribute(): int
    {
        return (int) ($this->relationLoaded('batches')
            ? $this->batches->sum('reserved')
            : $this->batches()->sum('reserved'));
    }

    public function getAvailableStockAttribute(): int
    {
        $batches = $this->relationLoaded('batches')
            ? $this->batches->where('status', 'active')
            : $this->batches()->where('status', 'active')->get();

        return (int) $batches->sum(fn($b) => max(0, (int)$b->quantity - (int)$b->reserved));
    }

    public function getIsInStockAttribute(): bool
    {
        if (!$this->track_inventory) {
            return true;
        }

        return $this->available_stock > 0;
    }
}
