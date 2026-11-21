@section('title', '仪表盘')

<x-app-layout>
    <div class="py-2">
        <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
            <div><p class="mb-2 text-xs font-bold uppercase tracking-[.16em] text-blue-600">Workspace overview</p><h1 class="page-title">欢迎回来，{{ $user->name }}</h1><p class="page-lead mt-2">查看你的图片资产、储存空间和可用的上传策略。</p></div>
            <a href="{{ route('upload') }}" class="modern-btn modern-btn-primary inline-flex items-center gap-2 px-4 py-2.5 text-sm"><i class="fas fa-plus"></i> 上传图片</a>
        </div>
        <div class="stat-grid mb-7">
            <div class="stat-card"><div><p class="stat-label">图片数量</p><p class="stat-value">{{ $user->image_num }}</p></div><span class="stat-icon"><i class="fas fa-images"></i></span></div>
            <div class="stat-card"><div><p class="stat-label">可用储存</p><p class="stat-value text-[20px]">{{ \App\Utils::formatSize(($user->capacity - $user->use_capacity) * 1024) }}</p></div><span class="stat-icon"><i class="fas fa-database"></i></span></div>
            <div class="stat-card"><div><p class="stat-label">使用储存</p><p class="stat-value text-[20px]">{{ \App\Utils::formatSize($user->use_capacity * 1024) }}</p></div><span class="stat-icon"><i class="fas fa-chart-pie"></i></span></div>
            <div class="stat-card"><div><p class="stat-label">总储存</p><p class="stat-value text-[20px]">{{ \App\Utils::formatSize($user->capacity * 1024) }}</p></div><span class="stat-icon"><i class="fas fa-server"></i></span></div>
        </div>

        <div class="grid gap-5 xl:grid-cols-[1.35fr_.65fr]">
            <div class="w-full">
                <x-box>
                    <x-slot name="title">可使用的策略</x-slot>
                    <x-slot name="content">
                        @if($strategies->isEmpty())
                            <x-no-data message="您所在的组还没有可用的储存策略，请联系管理员。" />
                        @else
                            <div class="divide-y divide-solid">
                                @foreach ($strategies as $strategy)
                                    <div class="w-full px-4 py-3">
                                        <p>{{ $strategy->name }}</p>
                                        <span class="text-gray-700 text-sm">{{ $strategy->intro }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </x-slot>
                </x-box>
            </div>
            <div class="flex flex-col gap-5">
                <x-box>
                    <x-slot name="title">我的信息</x-slot>
                    <x-slot name="content">
                        <div class="px-4 py-3 space-y-3">
                            <div class="flex">
                                <p class="basis-1/3">姓名</p>
                                <p class="basis-2/3 truncate text-gray-800">{{ $user->name }}</p>
                            </div>
                            <div class="flex">
                                <p class="basis-1/3">邮箱</p>
                                <p class="basis-2/3 truncate text-gray-800">{{ $user->email }}</p>
                            </div>
                            <div class="flex">
                                <p class="basis-1/3">注册时间</p>
                                <p class="basis-2/3 truncate text-gray-800">{{ $user->created_at }}</p>
                            </div>
                            <div class="flex">
                                <p class="basis-1/3">注册 IP</p>
                                <p class="basis-2/3 truncate text-gray-800">{{ $user->registered_ip }}</p>
                            </div>
                            @if(\App\Utils::config(\App\Enums\ConfigKey::IsUserNeedVerify) && !$user->email_verified_at)
                                <p class="p-2 text-sm rounded bg-red-400 text-white">
                                    你的账号尚未激活，功能受限，请根据激活邮件指引激活账号，如果你没有收到邮件，请点击
                                    <a id="send-verify-email" href="javascript:void(0)" class="text-green-400">这里</a>
                                    重新发送。
                                </p>
                            @endif
                        </div>
                    </x-slot>
                </x-box>
                <x-box>
                    <x-slot name="title">角色组信息</x-slot>
                    <x-slot name="content">
                        <div class="px-4 py-3 space-y-3">
                            <div class="flex">
                                <p class="basis-1/2">组名</p>
                                <p class="basis-1/2 truncate text-gray-800">{{ $user->group ? $user->group->name : '系统默认组' }}</p>
                            </div>
                            <div class="flex">
                                <p class="basis-1/2">最大文件大小</p>
                                <p class="basis-1/2 truncate text-gray-800">{{ \App\Utils::formatSize($configs->get(\App\Enums\GroupConfigKey::MaximumFileSize) * 1024) }}</p>
                            </div>
                            <div class="flex">
                                <p class="basis-1/2">并发上传数量</p>
                                <p class="basis-1/2 truncate text-gray-800">{{ $configs->get(\App\Enums\GroupConfigKey::ConcurrentUploadNum) }} 张</p>
                            </div>
                            <div class="flex">
                                <p class="basis-1/2">每分钟上传限制</p>
                                <p class="basis-1/2 truncate text-gray-800">{{ $configs->get(\App\Enums\GroupConfigKey::LimitPerMinute) }} 张</p>
                            </div>
                            <div class="flex">
                                <p class="basis-1/2">每小时上传限制</p>
                                <p class="basis-1/2 truncate text-gray-800">{{ $configs->get(\App\Enums\GroupConfigKey::LimitPerHour) }} 张</p>
                            </div>
                            <div class="flex">
                                <p class="basis-1/2">每天上传限制</p>
                                <p class="basis-1/2 truncate text-gray-800">{{ $configs->get(\App\Enums\GroupConfigKey::LimitPerDay) }} 张</p>
                            </div>
                            <div class="flex">
                                <p class="basis-1/2">每周上传限制</p>
                                <p class="basis-1/2 truncate text-gray-800">{{ $configs->get(\App\Enums\GroupConfigKey::LimitPerWeek) }} 张</p>
                            </div>
                            <div class="flex">
                                <p class="basis-1/2">每月上传限制</p>
                                <p class="basis-1/2 truncate text-gray-800">{{ $configs->get(\App\Enums\GroupConfigKey::LimitPerMonth) }} 张</p>
                            </div>
                        </div>
                    </x-slot>
                </x-box>
            </div>
        </div>
    </div>

    @if(\App\Utils::config(\App\Enums\ConfigKey::IsUserNeedVerify) && !$user->email_verified_at)
        @push('scripts')
            <script>
                $('#send-verify-email').click(function () {
                    if (! $(this).attr('disabled')) {
                        $(this).text('发送中...').attr('disabled');
                        axios.post('{{ route('verification.send') }}').then(response => {
                            toastr.success('发送成功，请注意查收。');
                        }).catch(error => {
                            if (error.response.status === 429) {
                                toastr.error('操作频繁，请稍后再试');
                            }
                        }).finally(_ => {
                            $(this).text('这里').attr('disabled');
                        });
                    }
                });
            </script>
        @endpush
    @endif
</x-app-layout>
