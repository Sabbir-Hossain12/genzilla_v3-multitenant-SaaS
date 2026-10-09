<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformContact extends Model
{
    use HasFactory;

    protected $table = 'platform_contacts';

    protected $guarded = ['id'];
}
