<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformNewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        PlatformNewsletterSubscriber::updateOrCreate(
            ['email' => $validated['email']],
            ['is_subscribed' => true, 'subscribed_at' => now()],
        );

        return back()->with('success', "Thanks — you're on the list.");
    }
}
