<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'rating' => 'integer',
        'is_verified_purchase' => 'boolean',
        'images' => 'array',
        'replied_at' => 'datetime',
        'helpful_count' => 'integer',
        'unhelpful_count' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    /* ── Scopes ── */

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /* ── Accessors & Helpers ── */

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'approved' => [
                'label' => 'Approved',
                'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                'icon' => 'fa-solid fa-circle-check',
            ],
            'rejected' => [
                'label' => 'Rejected',
                'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                'icon' => 'fa-solid fa-circle-xmark',
            ],
            default => [
                'label' => 'Pending Review',
                'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                'icon' => 'fa-solid fa-clock',
            ],
        };
    }

    public function getReviewerDisplayNameAttribute(): string
    {
        if ($this->customer && !empty($this->customer->first_name)) {
            return trim($this->customer->first_name . ' ' . ($this->customer->last_name ?? ''));
        }

        return $this->reviewer_name ?: 'Anonymous Customer';
    }

    public function getReviewerAvatarAttribute(): string
    {
        $hash = md5(strtolower(trim($this->reviewer_email ?? 'user@example.com')));
        return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=64";
    }
}
