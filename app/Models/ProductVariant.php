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
    public function movements(): HasMany { return $this->hasMany(StockMovement::class, 'variant_id'); }
    public function attributeValues() { return $this->belongsToMany(ProductAttributeValue::class, 'product_variant_attribute_values', 'variant_id', 'attribute_value_id')->withPivot('attribute_id', 'tenant_id'); }
}
