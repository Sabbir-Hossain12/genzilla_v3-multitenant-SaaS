<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlatformFeatureController extends Controller
{
    public function index()
    {
        $features = PlatformFeature::orderBy('sort_order', 'asc')->get();
        return view('platform_admin.pages.features.index', compact('features'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_desc' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('features', 'public');
        }

        $validated['status'] = $request->has('status') ? 1 : 0;
        PlatformFeature::create($validated);

        return redirect()->back()->with('success', 'Feature created successfully.');
    }

    public function update(Request $request, PlatformFeature $feature)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_desc' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            if ($feature->image && Storage::disk('public')->exists($feature->image)) {
                Storage::disk('public')->delete($feature->image);
            }
            $validated['image'] = $request->file('image')->store('features', 'public');
        }

        $validated['status'] = $request->has('status') ? 1 : 0;
        $feature->update($validated);

        return redirect()->back()->with('success', 'Feature updated successfully.');
    }

    public function destroy(PlatformFeature $feature)
    {
        if ($feature->image && Storage::disk('public')->exists($feature->image)) {
            Storage::disk('public')->delete($feature->image);
        }
        $feature->delete();

        return redirect()->back()->with('success', 'Feature deleted successfully.');
    }
}
