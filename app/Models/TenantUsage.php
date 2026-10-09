<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantUsage extends Model
{
    use HasFactory;

    protected $table = 'tenant_usages';

    protected $guarded = ['id'];

    protected $casts = [
        'storage_used_bytes' => 'integer',
        'staff_accounts_count' => 'integer',
        'products_count' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
