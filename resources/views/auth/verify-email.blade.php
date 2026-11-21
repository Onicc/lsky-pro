<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/" class="auth-brand">
                <span class="brand-mark"><i class="fas fa-images"></i></span>
                <span>{{ \App\Utils::config(\App\Enums\ConfigKey::AppName) }}</span>
            </a>
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            感谢注册。请查收邮箱中的验证链接，完成账户激活。如果没有收到邮件，可以重新发送。
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif

        <div class="mt-4 flex items-center justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf

                <div>
                    <x-button>
                重新发送验证邮件
                    </x-button>
                </div>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900">
                    退出登录
                </button>
            </form>
        </div>
    </x-auth-card>
</x-guest-layout>
