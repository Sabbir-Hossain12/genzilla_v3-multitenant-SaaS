<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformUseCase;
use Illuminate\Http\Request;

class PlatformUseCaseController extends Controller
{
    public function index()
    {
        $useCases = PlatformUseCase::orderBy('sort_order', 'asc')->get();
        return view('platform_admin.pages.use_cases.index', compact('useCases'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'badge_label' => 'nullable|string|max:255',
            'badge_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'icon_url' => 'nullable|string',
            'bg_color' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|integer',
        ]);

        $validated['status'] = $request->has('status') ? 1 : 0;
        PlatformUseCase::create($validated);

        return redirect()->back()->with('success', 'Use case created successfully.');
    }

    public function update(Request $request, PlatformUseCase $useCase)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'badge_label' => 'nullable|string|max:255',
            'badge_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'icon_url' => 'nullable|string',
            'bg_color' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|integer',
        ]);

        $validated['status'] = $request->has('status') ? 1 : 0;
        $useCase->update($validated);

        return redirect()->back()->with('success', 'Use case updated successfully.');
    }

    public function destroy(PlatformUseCase $useCase)
    {
        $useCase->delete();
        return redirect()->back()->with('success', 'Use case deleted successfully.');
    }
}
