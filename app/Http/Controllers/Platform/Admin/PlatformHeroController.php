<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformHero;
use Illuminate\Http\Request;

class PlatformHeroController extends Controller
{
    public function index()
    {
        $hero = PlatformHero::firstOrNew(['id' => 1]);
        return view('platform_admin.pages.hero.index', compact('hero'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'ai_product_desc' => 'nullable|string',
            'title' => 'required|string|max:255',
            'short_desc' => 'nullable|string',
            'btn_1_text' => 'nullable|string|max:100',
            'btn_1_link' => 'nullable|string|max:255',
            'btn_2_text' => 'nullable|string|max:100',
            'btn_2_link' => 'nullable|string|max:255',
        ]);

        $hero = PlatformHero::firstOrNew(['id' => 1]);
        $hero->fill($validated);
        $hero->save();

        return redirect()->back()->with('success', 'Hero section updated successfully.');
    }
}
