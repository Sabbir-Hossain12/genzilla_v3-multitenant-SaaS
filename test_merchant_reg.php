<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Illuminate\Support\Facades\DB::transaction(function () {
        $controller = new App\Http\Controllers\Store\Admin\StoreAdminAuthController();

        $request = Illuminate\Http\Request::create('/store/register', 'POST', [
            'name' => 'Dr. Alexander Fleming',
            'email' => 'fleming@antibiotics.test',
            'password' => 'penicillin123',
            'store_name' => 'Fleming Pharmaceuticals',
            'subdomain' => 'fleming-pharma',
            'plan_slug' => 'starter',
            'billing_interval' => 'monthly',
        ]);

        $response = $controller->register($request);

        echo "Response status: " . $response->getStatusCode() . "\n";
        echo "Redirect target: " . $response->getTargetUrl() . "\n";

        $user = App\Models\User::where('email', 'fleming@antibiotics.test')->first();
        echo "1. User created: " . ($user ? $user->name . ' (ID: ' . $user->id . ')' : 'No') . "\n";

        $tenant = App\Models\Tenant::where('subdomain', 'fleming-pharma')->first();
        echo "2. Tenant created: " . ($tenant ? $tenant->store_name . ' (ID: ' . $tenant->id . ')' : 'No') . "\n";
        echo "   Tenant current_sub_id: " . $tenant->current_subscription_id . " | status: " . $tenant->subscription_status . "\n";

        $domain = $tenant->primaryDomain;
        echo "3. Tenant Domain: " . ($domain ? $domain->domain . ' (Verified: ' . $domain->dns_verified . ')' : 'No') . "\n";

        $sub = $tenant->currentSubscription;
        echo "4. Tenant Subscription: " . ($sub ? 'ID: ' . $sub->id . ' | Plan: ' . $sub->plan_id . ' | Interval: ' . $sub->billing_interval . ' | Status: ' . $sub->subscription_status . ' | Source: ' . $sub->source : 'No') . "\n";

        $invoice = $tenant->invoices()->first();
        echo "5. Tenant Invoice: " . ($invoice ? 'Number: ' . $invoice->invoice_number . ' | Total: ' . $invoice->total . ' | Status: ' . $invoice->status : 'No') . "\n";

        $usage = $tenant->usage;
        echo "6. Tenant Usage: " . ($usage ? 'Staff: ' . $usage->staff_accounts_count . ' | Storage: ' . $usage->storage_used_bytes : 'No') . "\n";

        echo "Auth logged in ID: " . Illuminate\Support\Facades\Auth::id() . "\n";

        throw new Exception("Store registration test succeeded and rolled back!");
    });
} catch (\Exception $e) {
    echo "Result: " . $e->getMessage() . "\n";
}
