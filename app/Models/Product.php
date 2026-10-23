<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];
    protected $casts = ['requires_prescription' => 'boolean', 'specs' => 'array'];
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function brand(): BelongsTo { return $this->belongsTo(Brand::class); }
    public function variants(): HasMany { return $this->hasMany(ProductVariant::class)->orderBy('position'); }
    public function media(): HasMany { return $this->hasMany(Media::class, 'model_id')->where('model_type', self::class)->orderBy('position'); }
}
