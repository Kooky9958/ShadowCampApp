<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles

        <!-- Facebook Pixel Code -->
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '735364924958170');
            fbq('track', 'PageView');
        </script>
        <noscript>
            <img height="1" width="1" style="display:none" 
                src="https://www.facebook.com/tr?id=735364924958170&ev=PageView&noscript=1"/>
        </noscript>
        <!-- End Facebook Pixel Code -->
    </head>
    <body class="font-sans antialiased">
        @if (!env('APP_DEBUG', false))
            <noscript>
                <img src="https://analytics.gigaquad.eu/ingress/1cefe1a2-ced0-4274-b4cd-978b581ec62a/pixel.gif">
            </noscript>
            <script defer src="https://analytics.gigaquad.eu/ingress/1cefe1a2-ced0-4274-b4cd-978b581ec62a/script.js"></script>
        @endif

        <div class="min-h-screen bg-sc-bg-1">
            @livewire('navigation-menu')

            <!-- Document offset to avoid navigation obstructing content -->
            <div class="pb-16"></div>

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white border-b-2 border-sc-grey-1">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            <!-- Document offset to avoid tray obstructing content -->
            <div class="pb-16"></div>

            @livewire('foot-tray')
        </div>

        @stack('modals')

        @livewireScripts
    </body>
</html>
