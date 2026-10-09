<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\TenantUsage;
use Illuminate\Http\Request;

class TenantUsageController extends Controller
{
    public function index()
    {
        $usages = TenantUsage::with(['tenant.activeSubscription.plan'])->latest()->get();
        return view('platform_admin.pages.usages.index', compact('usages'));
    }
}
