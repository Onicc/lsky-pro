<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/" class="flex items-center justify-center gap-3 text-xl font-bold text-slate-800">
                <span class="brand-mark"><i class="fas fa-images"></i></span>
                <span>{{ \App\Utils::config(\App\Enums\ConfigKey::AppName) }}</span>
            </a>
        </x-slot>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <div class="mb-7 mt-8"><h1 class="text-2xl font-bold text-slate-800">欢迎回来</h1><p class="mt-2 text-sm text-slate-500">登录后管理你的图片资产和上传策略。</p></div>
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <x-label for="email" :value="__('Email')" />

                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            </div>

            <!-- Password -->
            <div>
                <x-label for="password" :value="__('Password')" />

                <x-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />
            </div>

            <!-- Remember Me -->
            <div class="block">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" name="remember">
                    <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                <div class="flex items-center text-sm">
                    @if(\App\Utils::config(\App\Enums\ConfigKey::IsEnableRegistration))
                        没有账号？
                        <a class="underline text-gray-600 hover:text-gray-900" href="{{ route('register') }}">
                            注册
                        </a>
                    @endif
                </div>
                <div class="flex items-center">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif

                    <x-button class="ml-0">
                        {{ __('Log in') }}
                    </x-button>
                </div>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
