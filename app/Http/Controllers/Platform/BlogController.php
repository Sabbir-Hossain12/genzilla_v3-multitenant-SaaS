<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('platform/blog');
    }

    public function show(string $slug): Response
    {
        // Later, you can fetch the actual post here:
        // $post = Post::where('slug', $slug)->firstOrFail();

        return Inertia::render('platform/blog/show', [
            'slug' => $slug,
        ]);
    }
}
