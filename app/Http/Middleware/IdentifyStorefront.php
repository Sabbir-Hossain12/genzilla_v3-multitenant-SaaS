<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyStorefront
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost(); // e.g., 'mystore.127.0.0.1.nip.io', 'mystore.test', or 'customdomain.com'

        $baseDomain = config('app.base_domain', '127.0.0.1'); // e.g. 'yourplatform.com' or '127.0.0.1'

        $store = null;

        // 1. Check if the request is on the Base / Platform Domain with a Subdomain
        if (str_ends_with($host, $baseDomain) && $host !== $baseDomain) {
            // Extract subdomain prefix (e.g., "mystore" from "mystore.yourplatform.com" or "mystore.127.0.0.1.nip.io")
            $subdomain = explode('.', $host)[0];

            $store = Store::where('subdomain', $subdomain)->first();
        }
        // 2. Otherwise, treat it as a Custom Domain
        else {
            $store = Store::where('custom_domain', $host)->first();
        }

        // 3. Handle Store Not Found
        if (! $store) {
            abort(404, 'Storefront not found.');
        }

        // 4. Bind the resolved store instance into the container or request context
        app()->instance(Store::class, $store); //app(Store::class)
        $request->attributes->set('store', $store); //$request->attributes->get('store')

        return $next($request);
    }
}
