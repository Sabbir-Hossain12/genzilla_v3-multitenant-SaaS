<?php

namespace App\Http\Controllers\Store\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StoreAdminDashboardController extends Controller
{
    public function index()
    {
        return view('store_admin.pages.dashboard.dashboard');
    }
}
