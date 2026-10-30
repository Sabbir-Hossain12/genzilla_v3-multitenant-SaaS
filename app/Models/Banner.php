<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Banner extends Model
{
    protected $table = 'banners';
    protected $guarded = ['id'];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model')->orderBy('position');
    }

    public function getIsActiveAttribute(): bool
    {
        return (bool) $this->status;
    }

    public function setIsActiveAttribute($value): void
    {
        $this->attributes['status'] = (bool) $value;
    }

    public function getImageUrlAttribute(): ?string
    {
        $path = $this->image;
        if (!$path) {
            $cover = $this->relationLoaded('media')
                ? ($this->media->firstWhere('collection', 'banner') ?? $this->media->first())
                : $this->media()->where('collection', 'banner')->first();
            $path = $cover?->path;
        }

        if (!$path) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://'])
            ? $path
            : asset('storage/' . $path);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->status
            ? 'bg-emerald-50 text-emerald-700 border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800'
            : 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status ? 'Active' : 'Inactive';
    }
}
