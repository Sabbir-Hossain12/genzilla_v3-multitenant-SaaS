<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeValue extends Model
{
    protected $table = 'attributes_values';
    protected $guarded = ['id'];
    protected $casts = ['status' => 'boolean'];
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function attribute(): BelongsTo { return $this->belongsTo(ProductAttribute::class, 'attribute_id'); }
}
