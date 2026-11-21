<header class="site-header transition-all duration-300 w-full flex justify-center fixed top-0 z-[9]">
    <x-container class="w-full flex justify-between items-center">
        <div class="flex justify-start items-center max-w-[70%]">
            <a href="javascript:void(0)" @click="$store.sidebar.toggle()" class="w-9 h-9 rounded-xl sm:hidden mr-3 flex justify-center items-center text-slate-600 hover:bg-slate-100" aria-label="打开导航">
                <i class="fas fa-bars text-lg"></i>
            </a>
            <div class="min-w-0">
                <p class="text-[10px] uppercase tracking-[.18em] text-slate-400">Lsky Pro</p>
                <a href="" class="block truncate text-lg font-bold text-slate-800" id="header-title">@yield('title', \App\Utils::config(\App\Enums\ConfigKey::AppName))</a>
            </div>
        </div>
        <div class="flex justify-end items-center space-x-4">
            @includeWhen($_is_notice, 'layouts.notice')
            @includeWhen($_group->strategies->isNotEmpty(), 'layouts.strategies')
            @include('layouts.user-nav')
        </div>
    </x-container>
</header>
