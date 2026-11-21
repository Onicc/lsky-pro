<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/" class="auth-brand">
                <span class="brand-mark"><i class="fas fa-images"></i></span>
                <span>{{ \App\Utils::config(\App\Enums\ConfigKey::AppName) }}</span>
            </a>
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            输入注册邮箱，我们会发送密码重置链接，帮助你设置新密码。
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <x-label for="email" value="邮箱" />

                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-button>
                    发送密码重置邮件
                </x-button>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
