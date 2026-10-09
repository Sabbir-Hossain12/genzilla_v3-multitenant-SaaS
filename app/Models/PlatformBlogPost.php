<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformBlogPost extends Model
{
    use HasFactory;

    protected $table = 'platform_blog_posts';

    protected $guarded = ['id'];

    protected $casts = [
        'is_featured' => 'boolean',
        'published_at' => 'date',
        'read_time' => 'integer',
        'status' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PlatformBlogCategory::class, 'blog_category_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 1);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getReadTimeLabelAttribute(): string
    {
        return $this->read_time.' min read';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
