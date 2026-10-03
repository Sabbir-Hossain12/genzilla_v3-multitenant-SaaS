<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StoreWebviewController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('store/home');
    }
}
