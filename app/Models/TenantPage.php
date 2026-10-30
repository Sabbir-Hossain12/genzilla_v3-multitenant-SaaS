<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TenantPage extends Model
{
    protected $table = 'tenant_pages';
    protected $guarded = ['id'];

    protected $casts = [
        'status' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getIsActiveAttribute(): bool
    {
        return (bool) $this->status;
    }

    public function setIsActiveAttribute($value): void
    {
        $this->attributes['status'] = (bool) $value;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->status
            ? 'bg-emerald-50 text-emerald-700 border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800'
            : 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status ? 'Published' : 'Draft';
    }

    public function getExcerptAttribute(): string
    {
        return Str::limit(strip_tags($this->content ?? ''), 120);
    }
}
