<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="keywords" content="{{ \App\Utils::config(\App\Enums\ConfigKey::SiteKeywords) }}"/>
    <meta name="description" content="{{ \App\Utils::config(\App\Enums\ConfigKey::SiteDescription) }}"/>

    <title>@hasSection('title')@yield('title') · @endif{{ config('app.name') }}</title>

    <link rel="stylesheet" href="{{ Vite::asset('markdown.css') }}">
    @stack('styles')

    <!-- Styles -->
    <link rel="stylesheet" href="{{ Vite::asset('app.css') }}">
</head>
<body class="font-sans antialiased overflow-hidden">
<div class="app-shell" x-data x-cloak>
    @include('layouts.sidebar')
    @include('layouts.header')
    <div
        x-transition:enter="ease-in-out duration-500"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in-out duration-500"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="z-[9] bg-slate-900/50 backdrop-blur-sm transition-opacity h-full w-full fixed inset-0 sm:hidden"
        x-show="$store.sidebar.open"
        @click.outside="$store.sidebar.open = false"
        @close.stop="$store.sidebar.open = false"
        @click="$store.sidebar.toggle()"
        style="display: none"
    >

    </div>
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-50 focus:rounded focus:bg-white focus:px-4 focus:py-2">前往主要内容</a>
    <x-container id="main-content" role="main" tabindex="-1" class="site-content flex flex-col overflow-y-auto absolute left-0 right-0 bottom-0 transition-all duration-300 min-h-screen h-full">
        {{ $slot }}
    </x-container>
</div>
</body>
<!-- Scripts -->
<script src="{{ Vite::asset('app.js') }}"></script>
@include('common.notice')
<script>
    // 开关组件默认值
    let setSwitch = function (e) {
        if (e.checked) {
            $(e).closest('.switch').find('input[type=hidden]').remove();
        } else {
            $(e).before('<input type="hidden" name="'+e.name+'" value="0" />');
        }
    }
    $('.switch input[type=checkbox]').each(function () {
        setSwitch(this);
    }).click(function () {
        setSwitch(this);
    });
</script>
@if(file_exists(public_path('js/custom.js')))
<script src="{{ asset('js/custom.js') }}"></script>
@endif
@stack('scripts')
</html>
