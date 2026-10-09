<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformNewsletterSubscriber;
use Illuminate\Http\Request;

class PlatformNewsletterSubscriberController extends Controller
{
    public function index()
    {
        $subscribers = PlatformNewsletterSubscriber::latest()->get();
        return view('platform_admin.pages.newsletter.index', compact('subscribers'));
    }

    public function toggleStatus(PlatformNewsletterSubscriber $subscriber)
    {
        $subscriber->is_subscribed = !$subscriber->is_subscribed;
        $subscriber->save();

        return redirect()->back()->with('success', 'Subscriber status toggled.');
    }

    public function destroy(PlatformNewsletterSubscriber $subscriber)
    {
        $subscriber->delete();
        return redirect()->back()->with('success', 'Subscriber removed successfully.');
    }
}
