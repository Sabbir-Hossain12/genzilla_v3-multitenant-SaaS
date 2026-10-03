<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlatformWebviewController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('platform/index');
    }

    public function features(): Response
    {
        return Inertia::render('platform/features');
    }

    public function pricing(): Response
    {
        return Inertia::render('platform/pricing');
    }

}
