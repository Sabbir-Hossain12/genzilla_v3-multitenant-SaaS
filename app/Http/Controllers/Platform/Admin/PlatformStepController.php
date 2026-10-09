<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformStep;
use Illuminate\Http\Request;

class PlatformStepController extends Controller
{
    public function index()
    {
        $steps = PlatformStep::orderBy('sort_order', 'asc')->get();
        return view('platform_admin.pages.steps.index', compact('steps'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_desc' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|integer',
        ]);

        $validated['status'] = $request->has('status') ? 1 : 0;
        PlatformStep::create($validated);

        return redirect()->back()->with('success', 'Step created successfully.');
    }

    public function update(Request $request, PlatformStep $step)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_desc' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|integer',
        ]);

        $validated['status'] = $request->has('status') ? 1 : 0;
        $step->update($validated);

        return redirect()->back()->with('success', 'Step updated successfully.');
    }

    public function destroy(PlatformStep $step)
    {
        $step->delete();
        return redirect()->back()->with('success', 'Step deleted successfully.');
    }
}
