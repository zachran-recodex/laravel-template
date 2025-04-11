<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('components.layouts.partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">

        <!-- Sidebar -->
        @include('components.layouts.partials.sidebar')

        <!-- Header -->
        @include('components.layouts.partials.header')

        <flux:main>
            {{ $slot }}
        </flux:main>

        @fluxScripts
    </body>
</html>
