<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformBlogCategory extends Model
{
    use HasFactory;

    protected $table = 'platform_blog_categories';

    protected $guarded = ['id'];

    protected $casts = [
        'sort_order' => 'integer',
        'status' => 'integer',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(PlatformBlogPost::class, 'blog_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
