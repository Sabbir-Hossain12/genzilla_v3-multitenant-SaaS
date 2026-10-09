<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformContact;
use Illuminate\Http\Request;

class PlatformContactController extends Controller
{
    public function index()
    {
        $contacts = PlatformContact::latest()->get();
        return view('platform_admin.pages.contacts.index', compact('contacts'));
    }

    public function updateStatus(Request $request, PlatformContact $contact)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,contacted,resolved',
        ]);

        $contact->update($validated);

        return redirect()->back()->with('success', 'Contact status updated successfully.');
    }

    public function destroy(PlatformContact $contact)
    {
        $contact->delete();
        return redirect()->back()->with('success', 'Contact message deleted successfully.');
    }
}
