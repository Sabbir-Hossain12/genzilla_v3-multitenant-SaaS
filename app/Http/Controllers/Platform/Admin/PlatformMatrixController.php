<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformMatrix;
use Illuminate\Http\Request;

class PlatformMatrixController extends Controller
{
    public function index()
    {
        $matrix = PlatformMatrix::firstOrNew(['id' => 1]);
        return view('platform_admin.pages.matrix.index', compact('matrix'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'active_merchant' => 'required|integer|min:0',
            'gmv_proccessed' => 'required|numeric|min:0',
            'countries_served' => 'required|integer|min:0',
            'platform_uptime' => 'required|string|max:50',
        ]);

        $matrix = PlatformMatrix::firstOrNew(['id' => 1]);
        $matrix->fill($validated);
        $matrix->save();

        return redirect()->back()->with('success', 'Platform matrix updated successfully.');
    }
}
