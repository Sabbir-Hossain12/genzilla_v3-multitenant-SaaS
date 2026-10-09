<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformUseCase extends Model
{
    use HasFactory;

    protected $table = 'platform_use_cases';

    protected $guarded = ['id'];

    protected $casts = [
        'sort_order' => 'integer',
        'status' => 'integer',
    ];
}
