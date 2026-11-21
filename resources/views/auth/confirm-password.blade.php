<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/" class="auth-brand">
                <span class="brand-mark"><i class="fas fa-images"></i></span>
                <span>{{ \App\Utils::config(\App\Enums\ConfigKey::AppName) }}</span>
            </a>
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            这是受保护的操作，请先确认当前账户密码。
        </div>

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <!-- Password -->
            <div>
                <x-label for="password" value="密码" />

                <x-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />
            </div>

            <div class="flex justify-end mt-4">
                <x-button>
                    确认
                </x-button>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
