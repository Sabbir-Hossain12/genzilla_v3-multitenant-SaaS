<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformFaq;
use Illuminate\Http\Request;

class PlatformFaqController extends Controller
{
    public function index()
    {
        $faqs = PlatformFaq::orderBy('sort_order', 'asc')->get();
        return view('platform_admin.pages.faqs.index', compact('faqs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|integer',
        ]);

        $validated['status'] = $request->has('status') ? 1 : 0;
        PlatformFaq::create($validated);

        return redirect()->back()->with('success', 'FAQ entry created successfully.');
    }

    public function update(Request $request, PlatformFaq $faq)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|integer',
        ]);

        $validated['status'] = $request->has('status') ? 1 : 0;
        $faq->update($validated);

        return redirect()->back()->with('success', 'FAQ entry updated successfully.');
    }

    public function destroy(PlatformFaq $faq)
    {
        $faq->delete();
        return redirect()->back()->with('success', 'FAQ entry deleted successfully.');
    }
}
