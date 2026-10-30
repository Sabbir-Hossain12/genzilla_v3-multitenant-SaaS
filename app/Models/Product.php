<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];
    protected $casts = ['requires_prescription' => 'boolean', 'specs' => 'array'];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function brand(): BelongsTo { return $this->belongsTo(Brand::class); }
    public function variants(): HasMany { return $this->hasMany(ProductVariant::class)->orderBy('position'); }
    public function activeVariants(): HasMany { return $this->hasMany(ProductVariant::class)->where('status', 'active')->orderBy('position'); }
    public function defaultVariant(): HasOne { return $this->hasOne(ProductVariant::class)->where('status', 'active')->where('is_default', true); }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model')->orderBy('position');
    }

    public function thumbnailMedia(): MorphOne
    {
        return $this->morphOne(Media::class, 'model')->whereIn('collection', ['thumbnail', 'cover']);
    }

    public function coverMedia(): MorphOne
    {
        return $this->morphOne(Media::class, 'model')->whereIn('collection', ['thumbnail', 'cover']);
    }

    public function sliderMedia(): MorphMany
    {
        return $this->morphMany(Media::class, 'model')->where('collection', 'slider')->orderBy('position');
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        $thumb = $this->relationLoaded('media')
            ? ($this->media->firstWhere('collection', 'thumbnail') ?? $this->media->firstWhere('collection', 'cover') ?? $this->media->first())
            : $this->media()->whereIn('collection', ['thumbnail', 'cover'])->first();

        if (!$thumb) {
            return null;
        }
        return Str::startsWith($thumb->path, ['http://', 'https://'])
            ? $thumb->path
            : asset('storage/' . $thumb->path);
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->thumbnail_url;
    }

    public function getTotalStockAttribute(): int
    {
        $vars = $this->relationLoaded('activeVariants')
            ? $this->activeVariants
            : ($this->relationLoaded('variants')
                ? $this->variants->where('status', 'active')
                : $this->activeVariants()->with('batches')->get());

        return (int) $vars->sum(function ($variant) {
            return $variant->relationLoaded('batches')
                ? $variant->batches->sum('quantity')
                : $variant->batches()->sum('quantity');
        });
    }

    public function getAvailableStockAttribute(): int
    {
        $vars = $this->relationLoaded('activeVariants')
            ? $this->activeVariants
            : ($this->relationLoaded('variants')
                ? $this->variants->where('status', 'active')
                : $this->activeVariants()->with('batches')->get());

        return (int) $vars->sum(function ($variant) {
            $batches = $variant->relationLoaded('batches')
                ? $variant->batches->where('status', 'active')
                : $variant->batches()->where('status', 'active')->get();

            return $batches->sum(fn($b) => max(0, (int)$b->quantity - (int)$b->reserved));
        });
    }

    public function getTracksInventoryAttribute(): bool
    {
        $vars = $this->relationLoaded('activeVariants')
            ? $this->activeVariants
            : ($this->relationLoaded('variants')
                ? $this->variants->where('status', 'active')
                : $this->activeVariants()->get());

        return $vars->contains('track_inventory', true);
    }

    public function getMinPriceAttribute(): ?float
    {
        $vars = $this->relationLoaded('activeVariants')
            ? $this->activeVariants
            : ($this->relationLoaded('variants')
                ? $this->variants->where('status', 'active')
                : $this->activeVariants()->get());

        $min = $vars->pluck('price')->filter(fn($p) => $p !== null)->min();
        return $min !== null ? (float)$min : null;
    }

    public function getMaxPriceAttribute(): ?float
    {
        $vars = $this->relationLoaded('activeVariants')
            ? $this->activeVariants
            : ($this->relationLoaded('variants')
                ? $this->variants->where('status', 'active')
                : $this->activeVariants()->get());

        $max = $vars->pluck('price')->filter(fn($p) => $p !== null)->max();
        return $max !== null ? (float)$max : null;
    }
}
