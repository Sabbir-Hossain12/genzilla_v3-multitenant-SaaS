<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TenantPrescription extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (TenantPrescription $prescription) {
            if (empty($prescription->prescription_number)) {
                $prescription->prescription_number = 'RX-' . strtoupper(Str::random(8));
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->prescription_image)) {
            return null;
        }

        if (str_starts_with($this->prescription_image, 'http://') || str_starts_with($this->prescription_image, 'https://')) {
            return $this->prescription_image;
        }

        $clean = ltrim($this->prescription_image, '/');
        if (str_starts_with($clean, 'storage/')) {
            return '/' . $clean;
        }

        return '/storage/' . $clean;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending_review' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-200 dark:border-amber-800',
            'converted_to_order' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
            'rejected' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border-rose-200 dark:border-rose-800',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700',
        };
    }
}
