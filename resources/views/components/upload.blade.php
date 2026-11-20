@php
    $group = $_group;
    $user = auth()->user();
    $strategies = $group->strategies;
    $extensions = array_diff($group->configs->get(\App\Enums\GroupConfigKey::AcceptedFileSuffixes, []), ['svg']);
    $maxBytes = $group->configs->get(\App\Enums\GroupConfigKey::MaximumFileSize, 5120) * 1024;
    $userConfig = $user?->configs;
    $albums = $user?->albums()->orderBy('name')->get(['id', 'name']) ?? collect();
    $defaultAlbum = (int) $userConfig?->get(\App\Enums\UserConfigKey::DefaultAlbum);
    $defaultStrategy = (int) $userConfig?->get(\App\Enums\UserConfigKey::DefaultStrategy);
    $defaultPermission = $userConfig?->get(\App\Enums\UserConfigKey::DefaultPermission, \App\Enums\ImagePermission::LinkOnly);
@endphp

<section class="mx-auto max-w-5xl space-y-5" aria-labelledby="upload-heading" data-uploader
    data-endpoint="{{ url('/upload') }}" data-extensions="{{ implode(',', $extensions) }}" data-max-bytes="{{ $maxBytes }}"
    data-concurrency="{{ $group->configs->get(\App\Enums\GroupConfigKey::ConcurrentUploadNum, 3) }}"
    data-paste-immediate="{{ $userConfig?->get(\App\Enums\UserConfigKey::PastedAction) === \App\Enums\PastedAction::Upload ? '1' : '0' }}"
    data-hide-finished-previews="{{ $userConfig?->get(\App\Enums\UserConfigKey::IsAutoClearPreview) ? '1' : '0' }}">
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7">
        <h1 id="upload-heading" class="text-2xl font-semibold tracking-tight text-gray-900">上传图片</h1>
        <p class="mt-1 text-sm text-gray-700">单张最大 {{ \App\Utils::formatSize($maxBytes) }}。支持多选、拖放和粘贴。</p>
        @if($strategies->isEmpty())
            <p class="mt-5 rounded-lg bg-amber-50 p-4 text-sm text-amber-900" role="alert">没有可用的存储策略，请联系管理员。</p>
        @else
            <div class="mt-5 grid gap-4 sm:grid-cols-3">
                <label class="block text-sm font-medium text-gray-800">存储策略
                    <select data-upload-strategy class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm">
                        @foreach($strategies as $strategy)
                            <option value="{{ $strategy->id }}" @selected($defaultStrategy === $strategy->id)>{{ $strategy->name }}</option>
                        @endforeach
                    </select>
                </label>
                @auth
                    <label class="block text-sm font-medium text-gray-800">相册
                        <select data-upload-album class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm">
                            <option value="0">不归入相册</option>
                            @foreach($albums as $album)
                                <option value="{{ $album->id }}" @selected($defaultAlbum === $album->id)>{{ $album->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm font-medium text-gray-800">可见性
                        <select data-upload-permission class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm" aria-describedby="upload-visibility-help">
                            <option value="{{ \App\Enums\ImagePermission::LinkOnly }}" @selected($defaultPermission === \App\Enums\ImagePermission::LinkOnly)>持链接可访问</option>
                            <option value="{{ \App\Enums\ImagePermission::Private }}" @selected($defaultPermission === \App\Enums\ImagePermission::Private)>仅自己可查看</option>
                            <option value="{{ \App\Enums\ImagePermission::Public }}" @selected($defaultPermission === \App\Enums\ImagePermission::Public)>公开到画廊</option>
                        </select>
                    </label>
                @endauth
            </div>
            @auth
                <p id="upload-visibility-help" class="mt-3 text-sm text-gray-700">私有图片可在图库生成临时分享链接。公开图片可能出现在画廊。</p>
            @endauth
            <div data-upload-drop class="mt-5 flex min-h-[112px] flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 px-4 py-4 text-center">
                <input id="picker" type="file" name="file" class="sr-only peer" data-upload-picker multiple accept="{{ implode(',', array_map(fn ($ext) => '.'.$ext, $extensions)) }}">
                <label for="picker" class="inline-flex min-h-[44px] cursor-pointer items-center rounded-md bg-indigo-700 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-800 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-600 peer-focus-visible:ring-offset-2">选择图片</label>
                <p class="text-sm text-gray-700">或将图片拖到此处</p>
            </div>
            <p data-upload-alert role="status" aria-live="polite" class="mt-3 min-h-[1.25rem] text-sm text-gray-700"></p>
        @endif
    </div>

    @if($strategies->isNotEmpty())
        <div data-upload-batch hidden class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">本次上传</h2>
                    <p data-upload-summary role="status" class="mt-1 text-sm text-gray-700"></p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" data-upload-clear class="min-h-[44px] rounded-md border border-gray-300 px-3 text-sm text-gray-800 hover:bg-gray-50">移除已完成</button>
                    <button type="button" data-upload-start class="min-h-[44px] rounded-md bg-indigo-700 px-4 text-sm font-medium text-white hover:bg-indigo-800 disabled:cursor-not-allowed disabled:opacity-60">开始上传</button>
                </div>
            </div>
            <div class="my-4 flex flex-wrap items-end gap-2 border-y border-gray-200 py-3">
                <label class="text-sm font-medium text-gray-800">链接格式
                    <select data-upload-format class="ml-1 rounded-md border-gray-300 bg-white text-sm">
                        <option value="url">原图链接</option>
                        <option value="markdown">Markdown</option>
                        <option value="markdown_with_link">Markdown 带链接</option>
                        <option value="html">HTML</option>
                        <option value="bbcode">BBCode</option>
                        <option value="thumbnail_url">缩略图链接</option>
                    </select>
                </label>
                <button type="button" data-upload-copy class="min-h-[44px] rounded-md border border-indigo-500 px-3 text-sm font-medium text-indigo-800 disabled:opacity-60">复制已完成链接</button>
                <p data-upload-copy-status role="status" aria-live="polite" class="w-full text-sm text-gray-700"></p>
            </div>
            <ol data-upload-list class="divide-y divide-gray-100" aria-label="上传列表"></ol>
        </div>
        <template data-upload-row>
            <li class="flex flex-wrap items-start gap-3 py-3 sm:flex-nowrap">
                <img data-preview alt="" hidden width="44" height="44" class="h-11 w-11 shrink-0 rounded-md object-cover">
                <div class="min-w-[10rem] flex-1">
                    <p data-name class="break-all text-sm font-medium text-gray-900"></p>
                    <p data-state class="mt-1 text-sm text-gray-700"></p>
                    <progress data-progress hidden class="mt-2 h-2 w-full" max="100"></progress>
                    <input data-link hidden readonly class="mt-2 w-full rounded-md border-gray-300 bg-gray-50 text-xs text-gray-800" aria-label="图片链接">
                </div>
                <div class="flex shrink-0 gap-2 text-sm">
                    <button type="button" data-copy hidden class="min-h-[44px] rounded-md border border-gray-300 px-3 text-indigo-800">复制</button>
                    <button type="button" data-action class="min-h-[44px] rounded-md border border-gray-300 px-3 text-gray-800">移除</button>
                </div>
            </li>
        </template>
    @endif
</section>
