<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$urls = [
    '/',
    '/features',
    '/pricing',
    '/blog',
    '/blog/launch-your-first-store-in-a-weekend',
    '/blog/does-not-exist',
];

foreach ($urls as $url) {
    $request = Illuminate\Http\Request::create('http://127.0.0.1'.$url, 'GET');
    $response = $kernel->handle($request);
    $body = $response->getContent();

    echo str_pad($url, 50).$response->getStatusCode().' '.strlen($body)." bytes\n";

    if (preg_match('/data-page="([^"]+)"/', $body, $m)) {
        $page = json_decode(html_entity_decode($m[1], ENT_QUOTES), true);
        $component = $page['component'] ?? '?';
        $props = $page['props'] ?? [];
        echo '    component: '.$component."\n";

        foreach (['hero', 'stats', 'features', 'steps', 'testimonials', 'plans', 'useCases', 'featureGrid', 'comparison', 'faqs', 'featured', 'posts', 'categories', 'post', 'related', 'brand'] as $key) {
            if (array_key_exists($key, $props)) {
                $value = $props[$key];
                $summary = is_array($value) ? 'array('.count($value).')' : gettype($value);
                echo '    prop '.str_pad($key, 14).' '.$summary."\n";
            }
        }
    } else {
        echo "    (no data-page found)\n";
    }
}
