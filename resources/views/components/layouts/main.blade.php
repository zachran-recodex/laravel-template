<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @hasSection('meta_tag')
            @yield('meta_tag')
        @else
            <x-meta-tags />
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-white">

        <!-- Main Content -->
        {{ $slot }}

        @fluxScripts
    </body>
</html>
