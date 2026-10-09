<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformTestimonial extends Model
{
    use HasFactory;

    protected $table = 'platform_testimonials';

    protected $guarded = ['id'];

    protected $casts = [
        'rating' => 'integer',
        'status' => 'integer',
        'sort_order' => 'integer',
    ];
}
