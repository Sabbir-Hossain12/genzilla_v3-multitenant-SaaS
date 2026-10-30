<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Brand extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['status' => 'boolean'];
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function products(): HasMany { return $this->hasMany(Product::class); }
    public function media(): MorphMany { return $this->morphMany(Media::class, 'model')->orderBy('position'); }

    public function getImageUrlAttribute(): ?string
    {
        $path = $this->image;
        if (!$path) {
            $cover = $this->relationLoaded('media')
                ? ($this->media->firstWhere('collection', 'cover') ?? $this->media->first())
                : $this->media()->where('collection', 'cover')->first();
            $path = $cover?->path;
        }

        if (!$path) {
            return null;
        }
        return Str::startsWith($path, ['http://', 'https://'])
            ? $path
            : asset('storage/' . $path);
    }
}
