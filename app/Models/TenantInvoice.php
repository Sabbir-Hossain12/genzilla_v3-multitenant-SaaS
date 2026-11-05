<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantInvoice extends Model
{
    use HasFactory;

    protected $table = 'tenant_invoices';

    protected $guarded = ['id'];

    protected $casts = [
        'billing_period_start' => 'datetime',
        'billing_period_end' => 'datetime',
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantSubscription::class, 'platform_subscription_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlatformPlan::class, 'plan_id');
    }

    public function getPlanTitleAttribute(): string
    {
        return $this->plan_name
            ?? $this->plan?->name
            ?? $this->subscription?->plan?->name
            ?? 'Pharmacy SaaS Plan';
    }

    public function getCycleAttribute(): string
    {
        return $this->billing_interval
            ?? $this->subscription?->billing_interval
            ?? 'monthly';
    }
}
