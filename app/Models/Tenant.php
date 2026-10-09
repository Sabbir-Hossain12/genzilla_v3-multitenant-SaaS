<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class, 'tenant_id');
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(TenantSubscription::class, 'tenant_id')->ofMany([
            'id' => 'max',
        ], function ($query) {
            $query->whereIn('subscription_status', ['active', 'trialing']);
        });
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(TenantInvoice::class, 'tenant_id');
    }

    public function usage(): HasOne
    {
        return $this->hasOne(TenantUsage::class, 'tenant_id');
    }

    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class, 'tenant_id');
    }

    public function primaryDomain(): HasOne
    {
        return $this->hasOne(TenantDomain::class, 'tenant_id')->where('is_primary', true);
    }
}
