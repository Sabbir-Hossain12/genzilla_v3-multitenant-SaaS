<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Platform\Concerns\SharesPlatformBrand;
use App\Models\PlatformBlogCategory;
use App\Models\PlatformBlogPost;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    use SharesPlatformBrand;

    public function index(): Response
    {
        $posts = PlatformBlogPost::query()
            ->published()
            ->with('category')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->get()
            ->map(fn (PlatformBlogPost $post) => $this->transform($post))
            ->values();

        return Inertia::render('platform/blog', [
            'featured' => $posts->first(),
            'posts' => $posts->slice(1)->values()->all(),
            'categories' => $this->categories(),
            'brand' => $this->brand(),
        ]);
    }

    public function show(string $slug): Response
    {
        $post = PlatformBlogPost::query()
            ->published()
            ->with('category')
            ->where('slug', $slug)
            ->first();

        $related = $post
            ? PlatformBlogPost::query()
                ->published()
                ->with('category')
                ->where('id', '!=', $post->id)
                ->when($post->blog_category_id, fn ($query) => $query->where('blog_category_id', $post->blog_category_id))
                ->orderByDesc('published_at')
                ->limit(3)
                ->get()
                ->map(fn (PlatformBlogPost $item) => $this->transform($item))
                ->all()
            : [];

        return Inertia::render('platform/blog/show', [
            'post' => $post ? $this->transform($post) : null,
            'related' => $related,
            'brand' => $this->brand(),
        ]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function categories(): array
    {
        $categories = PlatformBlogCategory::query()
            ->active()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PlatformBlogCategory $category) => [
                'slug' => $category->slug,
                'label' => $category->name,
            ])
            ->all();

        return array_merge([['slug' => 'all', 'label' => 'All posts']], $categories);
    }

    /**
     * @return array<string, mixed>
     */
    private function transform(PlatformBlogPost $post): array
    {
        return [
            'slug' => $post->slug,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'category' => $post->category?->name ?? 'General',
            'categorySlug' => $post->category?->slug,
            'coverClass' => $post->cover_class ?: 'bg-gradient-to-br from-indigo-50 to-indigo-100',
            'emoji' => $post->emoji ?: '',
            'initials' => $post->author_initials ?: $this->initials($post->author_name),
            'author' => $post->author_name,
            'role' => $post->author_role,
            'date' => $post->published_at?->format('F j, Y') ?? '',
            'readTime' => $post->read_time_label,
            'html' => (string) $post->long_desc,
        ];
    }
}
