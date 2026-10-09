<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformBlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlatformBlogCategoryController extends Controller
{
    public function index()
    {
        $categories = PlatformBlogCategory::withCount('posts')->orderBy('sort_order')->get();

        return view('platform_admin.pages.blog.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        PlatformBlogCategory::create($this->validated($request));

        return redirect()->back()->with('success', 'Blog category created successfully.');
    }

    public function update(Request $request, PlatformBlogCategory $blogCategory)
    {
        $blogCategory->update($this->validated($request, $blogCategory));

        return redirect()->back()->with('success', 'Blog category updated successfully.');
    }

    public function destroy(PlatformBlogCategory $blogCategory)
    {
        $blogCategory->delete();

        return redirect()->back()->with('success', 'Blog category deleted successfully.');
    }

    protected function validated(Request $request, ?PlatformBlogCategory $category = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('platform_blog_categories', 'slug')->ignore($category?->id)],
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|integer',
        ]);

        $validated['slug'] = $validated['slug'] ?: $this->uniqueSlug($validated['name'], $category?->id);
        $validated['status'] = $request->boolean('status') ? 1 : 0;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        return $validated;
    }

    protected function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $original = Str::slug($base) ?: 'category';
        $slug = $original;
        $suffix = 1;

        while (PlatformBlogCategory::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $original.'-'.$suffix++;
        }

        return $slug;
    }
}
