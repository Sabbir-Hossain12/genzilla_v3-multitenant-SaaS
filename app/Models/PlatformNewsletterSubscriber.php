<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformNewsletterSubscriber extends Model
{
    use HasFactory;

    protected $table = 'platform_newsletter_subscribers';

    protected $guarded = ['id'];

    protected $casts = [
        'is_subscribed' => 'boolean',
        'subscribed_at' => 'datetime',
    ];
}
