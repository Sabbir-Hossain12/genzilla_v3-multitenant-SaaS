<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformMatrix extends Model
{
    use HasFactory;

    protected $table = 'platform_matrix';

    protected $guarded = ['id'];

    protected $casts = [
        'active_merchant' => 'integer',
        'gmv_proccessed' => 'decimal:2',
        'countries_served' => 'integer',
    ];
}
