<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformTestimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlatformTestimonialController extends Controller
{
    public function index()
    {
        $testimonials = PlatformTestimonial::orderBy('sort_order', 'asc')->get();
        return view('platform_admin.pages.testimonials.index', compact('testimonials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'merchant_name' => 'required|string|max:255',
            'merchant_title' => 'nullable|string|max:255',
            'text' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'merchant_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|integer',
        ]);

        if ($request->hasFile('merchant_image')) {
            $validated['merchant_image'] = $request->file('merchant_image')->store('testimonials', 'public');
        }

        $validated['status'] = $request->has('status') ? 1 : 0;
        PlatformTestimonial::create($validated);

        return redirect()->back()->with('success', 'Testimonial created successfully.');
    }

    public function update(Request $request, PlatformTestimonial $testimonial)
    {
        $validated = $request->validate([
            'merchant_name' => 'required|string|max:255',
            'merchant_title' => 'nullable|string|max:255',
            'text' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'merchant_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|integer',
        ]);

        if ($request->hasFile('merchant_image')) {
            if ($testimonial->merchant_image && Storage::disk('public')->exists($testimonial->merchant_image)) {
                Storage::disk('public')->delete($testimonial->merchant_image);
            }
            $validated['merchant_image'] = $request->file('merchant_image')->store('testimonials', 'public');
        }

        $validated['status'] = $request->has('status') ? 1 : 0;
        $testimonial->update($validated);

        return redirect()->back()->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(PlatformTestimonial $testimonial)
    {
        if ($testimonial->merchant_image && Storage::disk('public')->exists($testimonial->merchant_image)) {
            Storage::disk('public')->delete($testimonial->merchant_image);
        }
        $testimonial->delete();

        return redirect()->back()->with('success', 'Testimonial deleted successfully.');
    }
}
