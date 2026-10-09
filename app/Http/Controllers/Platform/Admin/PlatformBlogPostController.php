<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformBlogCategory;
use App\Models\PlatformBlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlatformBlogPostController extends Controller
{
    public function index(Request $request)
    {
        $posts = PlatformBlogPost::with('category')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->integer('status')))
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderBy('sort_order')
            ->paginate(15)
            ->withQueryString();

        return view('platform_admin.pages.blog.index', compact('posts'));
    }

    public function create()
    {
        $categories = PlatformBlogCategory::orderBy('sort_order')->get();

        return view('platform_admin.pages.blog.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $post = PlatformBlogPost::create($this->validated($request));
        $this->syncFeatured($post);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post created successfully.');
    }

    public function edit(PlatformBlogPost $blogPost)
    {
        $categories = PlatformBlogCategory::orderBy('sort_order')->get();

        return view('platform_admin.pages.blog.edit', compact('blogPost', 'categories'));
    }

    public function update(Request $request, PlatformBlogPost $blogPost)
    {
        $blogPost->update($this->validated($request, $blogPost));
        $this->syncFeatured($blogPost);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post updated successfully.');
    }

    public function destroy(PlatformBlogPost $blogPost)
    {
        $blogPost->delete();

        return redirect()->back()->with('success', 'Blog post deleted successfully.');
    }

    protected function validated(Request $request, ?PlatformBlogPost $post = null): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('platform_blog_posts', 'slug')->ignore($post?->id)],
            'blog_category_id' => 'nullable|integer|exists:platform_blog_categories,id',
            'excerpt' => 'required|string',
            'long_desc' => 'nullable|string',
            'author_name' => 'required|string|max:255',
            'author_role' => 'nullable|string|max:255',
            'author_initials' => 'nullable|string|max:5',
            'read_time' => 'required|integer|min:1|max:120',
            'published_at' => 'nullable|date',
            'cover_class' => 'nullable|string|max:255',
            'emoji' => 'nullable|string|max:16',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|integer',
            'is_featured' => 'nullable|boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?: $this->uniqueSlug($validated['title'], $post?->id);
        $validated['status'] = $request->boolean('status') ? 1 : 0;
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['published_at'] = $validated['published_at'] ?? null;

        return $validated;
    }

    protected function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $original = Str::slug($base) ?: 'post';
        $slug = $original;
        $suffix = 1;

        while (PlatformBlogPost::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $original.'-'.$suffix++;
        }

        return $slug;
    }

    protected function syncFeatured(PlatformBlogPost $post): void
    {
        if ($post->is_featured) {
            PlatformBlogPost::where('id', '!=', $post->id)->update(['is_featured' => false]);
        }
    }
}
