<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformHero extends Model
{
    use HasFactory;

    protected $table = 'platform_hero';

    protected $guarded = ['id'];
}
