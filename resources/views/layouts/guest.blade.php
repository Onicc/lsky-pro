<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="relative min-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="keywords" content="{{ \App\Support\Installation::exists() ? \App\Utils::config(\App\Enums\ConfigKey::SiteKeywords) : '' }}"/>
        <meta name="description" content="{{ \App\Support\Installation::exists() ? \App\Utils::config(\App\Enums\ConfigKey::SiteDescription) : '' }}"/>

        <title>{{ config('app.name') }}</title>

        <link rel="stylesheet" href="{{ Vite::asset('markdown.css') }}">
    @stack('styles')

        <!-- Styles -->
        <link rel="stylesheet" href="{{ Vite::asset('app.css') }}">
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen text-gray-900 bg-gray-100">
            {{ $slot }}
        </div>
    </body>
    <!-- Scripts -->
    <script src="{{ Vite::asset('app.js') }}"></script>
    @if(file_exists(public_path('js/custom.js')))
        <script src="{{ asset('js/custom.js') }}"></script>
    @endif
    @stack('scripts')
</html>
