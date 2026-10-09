<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformStoreDemo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlatformStoreDemoController extends Controller
{
    public function index()
    {
        $demos = PlatformStoreDemo::orderBy('sort_order', 'asc')->get();
        return view('platform_admin.pages.store_demos.index', compact('demos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:platform_store_demo,slug',
            'link' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|integer',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('store_demos', 'public');
        }

        $validated['status'] = $request->has('status') ? 1 : 0;
        PlatformStoreDemo::create($validated);

        return redirect()->back()->with('success', 'Store demo created successfully.');
    }

    public function update(Request $request, PlatformStoreDemo $storeDemo)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:platform_store_demo,slug,' . $storeDemo->id,
            'link' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|integer',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);

        if ($request->hasFile('image')) {
            if ($storeDemo->image && Storage::disk('public')->exists($storeDemo->image)) {
                Storage::disk('public')->delete($storeDemo->image);
            }
            $validated['image'] = $request->file('image')->store('store_demos', 'public');
        }

        $validated['status'] = $request->has('status') ? 1 : 0;
        $storeDemo->update($validated);

        return redirect()->back()->with('success', 'Store demo updated successfully.');
    }

    public function destroy(PlatformStoreDemo $storeDemo)
    {
        if ($storeDemo->image && Storage::disk('public')->exists($storeDemo->image)) {
            Storage::disk('public')->delete($storeDemo->image);
        }
        $storeDemo->delete();

        return redirect()->back()->with('success', 'Store demo deleted successfully.');
    }
}
