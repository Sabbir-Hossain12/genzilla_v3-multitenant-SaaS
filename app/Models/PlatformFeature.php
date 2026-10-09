<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformFeature extends Model
{
    use HasFactory;

    protected $table = 'platform_features';

    protected $guarded = ['id'];

    protected $casts = [
        'status' => 'integer',
        'sort_order' => 'integer',
    ];
}
