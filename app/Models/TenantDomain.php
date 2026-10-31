<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantDomain extends Model
{
    use HasFactory;

    protected $table = 'tenant_domains';

    protected $guarded = ['id'];

    protected $casts = [
        'is_custom' => 'boolean',
        'is_primary' => 'boolean',
        'dns_verified' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function isPendingDns(): bool
    {
        return $this->status === 'pending_dns' || !$this->dns_verified;
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->dns_verified;
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_custom) {
            return 'System Default';
        }

        return match ($this->status) {
            'active' => 'Connected & Active',
            'ssl_pending' => 'DNS Verified (SSL Pending)',
            'pending_dns' => 'Pending DNS Setup',
            'failed' => 'Verification Failed',
            default => ucfirst(str_replace('_', ' ', $this->status ?? 'pending')),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if (!$this->is_custom) {
            return 'bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
        }

        return match ($this->status) {
            'active' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
            'ssl_pending' => 'bg-cyan-50 text-cyan-700 border border-cyan-200/60 dark:bg-cyan-950/40 dark:text-cyan-300 dark:border-cyan-800',
            'pending_dns' => 'bg-amber-50 text-amber-700 border border-amber-200/60 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
            default => 'bg-rose-50 text-rose-700 border border-rose-200/60 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
        };
    }
}
