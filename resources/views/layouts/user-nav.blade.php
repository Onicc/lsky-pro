<!-- Profile dropdown -->
<x-dropdown trigger-id="account-menu-button">
    <x-slot name="trigger">
        <button type="button" class="site-account-trigger flex items-center gap-2" id="account-menu-button" aria-expanded="false" aria-haspopup="true">
            <span class="sr-only">打开账户菜单</span>
            <img class="h-8 w-8 rounded-full" src="{{ Auth::user()->avatar }}" alt="">
            <span class="sm:block hidden font-semibold">{{ Auth::user()->name }}</span>
        </button>
    </x-slot>

    <x-slot name="content">
        <!-- Authentication -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-dropdown-link href="{{ route('images') }}">我的图片</x-dropdown-link>
            <x-dropdown-link href="{{ route('dashboard') }}">仪表盘</x-dropdown-link>
            <x-dropdown-link href="{{ route('settings') }}">设置</x-dropdown-link>
            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                {{ __('Log Out') }}
            </x-dropdown-link>
        </form>
    </x-slot>
</x-dropdown>
