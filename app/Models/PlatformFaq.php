<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformFaq extends Model
{
    use HasFactory;

    protected $table = 'platform_faq';

    protected $guarded = ['id'];

    protected $casts = [
        'sort_order' => 'integer',
        'status' => 'integer',
    ];
}
