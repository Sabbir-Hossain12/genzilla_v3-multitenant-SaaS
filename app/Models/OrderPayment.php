<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class OrderPayment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (OrderPayment $payment) {
            if (empty($payment->transaction_id)) {
                $prefix = $payment->type === 'refund' ? 'REF-' : 'TRX-';
                $payment->transaction_id = $prefix . strtoupper(Str::random(10));
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function getCustomerAttribute(): ?Customer
    {
        return $this->order?->customer;
    }

    public function getCustomerNameAttribute(): string
    {
        return $this->order?->customer?->name ?? $this->order?->shipping_name ?? 'Guest Customer';
    }

    public function getCustomerPhoneAttribute(): ?string
    {
        return $this->order?->customer?->phone ?? $this->order?->shipping_phone;
    }

    public function getCustomerEmailAttribute(): ?string
    {
        return $this->order?->customer?->email ?? $this->order?->shipping_email;
    }

    public function getRefundedAmountAttribute(): float
    {
        if ($this->relationLoaded('refunds')) {
            return (float) $this->refunds->where('status', 'successful')->sum('amount');
        }

        return (float) $this->refunds()->where('status', 'successful')->sum('amount');
    }

    public function getRemainingRefundableAttribute(): float
    {
        return max(0.00, (float) $this->amount - $this->refunded_amount);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'successful' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
            'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-200 dark:border-amber-800',
            'failed' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border-rose-200 dark:border-rose-800',
            'refunded' => 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border-purple-200 dark:border-purple-800',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700',
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'charge' => 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300 border-sky-200 dark:border-sky-800',
            'refund' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border-rose-200 dark:border-rose-800',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700',
        };
    }

    public function getMethodLabelAttribute(): string
    {
        return match (strtolower($this->payment_method)) {
            'cod' => 'Cash on Delivery (COD)',
            'card' => 'Credit / Debit Card',
            'bkash' => 'bKash Mobile Banking',
            'nagad' => 'Nagad Mobile Banking',
            'rocket' => 'Rocket Mobile Banking',
            'bank_transfer' => 'Bank Wire Transfer',
            'cash' => 'Cash in Hand',
            default => ucfirst(str_replace('_', ' ', $this->payment_method)),
        };
    }

    public function getMethodIconAttribute(): string
    {
        return match (strtolower($this->payment_method)) {
            'card' => 'fa-credit-card',
            'cod' => 'fa-hand-holding-dollar',
            'bkash', 'nagad', 'rocket' => 'fa-mobile-screen-button',
            'bank_transfer' => 'fa-building-columns',
            'cash' => 'fa-money-bill-wave',
            default => 'fa-wallet',
        };
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', 'successful');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('status', 'refunded');
    }

    public function scopeCharges(Builder $query): Builder
    {
        return $query->where('type', 'charge');
    }

    public function scopeRefunds(Builder $query): Builder
    {
        return $query->where('type', 'refund');
    }
}
