<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformPage extends Model
{
    use HasFactory;

    protected $table = 'platform_pages';

    protected $guarded = ['id'];

    protected $casts = [
        'status' => 'integer',
    ];
}
