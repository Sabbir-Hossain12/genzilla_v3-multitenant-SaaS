<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['status' => 'boolean'];
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function products(): HasMany { return $this->hasMany(Product::class); }
}
