<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BasicInfo extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'basic_infos';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'black_logo',
        'light_logo',
        'email',
        'phone_1',
        'fb_link',
        'x_link',
        'p_link',
        'youtube_link',
        'insta_link',
        'inside_dhaka_charge',
        'outside_dhaka_charge',
        'store_location',
        'short_desc',
        'currency_symbol',
        'fb_pixel',
        'google_analytics',
        'chatbox_script',
        'marquee_text',
        'tagline',
        'working_hours',
        'copyright_text',
        'app_download_link',
        'app_download_img',
        'payment_methods_img',
    ];
}