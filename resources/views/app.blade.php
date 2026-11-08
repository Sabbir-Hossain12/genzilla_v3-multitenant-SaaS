<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'MediMart — Medicine, Beauty & Healthcare') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', 'resources/css/store.css', 'resources/css/platform.css', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead

        @if (!empty($page['props']['seo']['custom_head_script']))
            {!! $page['props']['seo']['custom_head_script'] !!}
        @endif
        @if (!empty($page['props']['seo']['gtm_id']))
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','{{ $page['props']['seo']['gtm_id'] }}');</script>
        @endif
        @if (!empty($page['props']['seo']['google_analytics_id']))
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $page['props']['seo']['google_analytics_id'] }}"></script>
            <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $page['props']['seo']['google_analytics_id'] }}');
            </script>
        @endif
        @if (!empty($page['props']['seo']['fb_pixel_id']))
            <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ $page['props']['seo']['fb_pixel_id'] }}');
            fbq('track', 'PageView');
            </script>
        @endif
    </head>
    <body class="bg-gray-50 text-gray-800 font-sans">
        @inertia
    </body>
</html>
