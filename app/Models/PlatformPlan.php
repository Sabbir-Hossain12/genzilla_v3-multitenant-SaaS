<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformPlan extends Model
{
    use HasFactory;

    protected $table = 'platform_plans';

    protected $guarded = ['id'];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'max_products' => 'integer',
        'max_staff_accounts' => 'integer',
        'max_storage_mb' => 'integer',
        'max_digital_file_size_mb' => 'integer',
        'allow_custom_domain' => 'boolean',
        'has_multi_currency' => 'boolean',
        'max_currencies_supported' => 'integer',
        'has_advanced_reporting' => 'boolean',
        'has_custom_rbac' => 'boolean',
        'has_audit_logs' => 'boolean',
        'status' => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class, 'plan_id');
    }
}
