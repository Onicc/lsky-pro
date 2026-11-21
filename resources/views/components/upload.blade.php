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

<section class="upload-page" aria-labelledby="upload-heading" data-uploader
    data-endpoint="{{ url('/upload') }}" data-extensions="{{ implode(',', $extensions) }}" data-max-bytes="{{ $maxBytes }}"
    data-concurrency="{{ $group->configs->get(\App\Enums\GroupConfigKey::ConcurrentUploadNum, 3) }}"
    data-paste-immediate="{{ $userConfig?->get(\App\Enums\UserConfigKey::PastedAction) === \App\Enums\PastedAction::Upload ? '1' : '0' }}"
    data-hide-finished-previews="{{ $userConfig?->get(\App\Enums\UserConfigKey::IsAutoClearPreview) ? '1' : '0' }}">
    <div class="upload-hero">
        <div>
            <div class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-[.16em] text-blue-600">
                <span class="h-2 w-2 rounded-full bg-blue-500"></span> Media workspace
            </div>
            <h1 id="upload-heading">把图片放进你的图床</h1>
            <p>拖拽、粘贴或选择图片。上传完成后，链接会按文件独立生成，方便复制和管理。</p>
        </div>
        <div class="upload-hero-badge"><i class="fas fa-shield-alt" aria-hidden="true"></i> 安全处理 · 自动生成缩略图</div>
    </div>

    @if($strategies->isEmpty())
        <div class="panel p-6 text-sm text-amber-900" role="alert">
            <div class="flex items-start gap-3"><span class="text-lg">⚠</span><div><strong>暂时没有可用的储存策略</strong><p class="mt-1 text-amber-800">请联系管理员配置策略后再上传。</p></div></div>
        </div>
    @else
        <div class="upload-workspace">
            <div class="panel p-3 sm:p-4">
                <div data-upload-drop class="upload-dropzone" tabindex="0" role="button" aria-label="选择或拖拽图片上传">
                    <input id="picker" type="file" name="file" class="sr-only" data-upload-picker multiple accept="{{ implode(',', array_map(fn ($ext) => '.'.$ext, $extensions)) }}">
                    <div class="upload-dropzone-icon"><i class="fas fa-cloud-upload-alt" aria-hidden="true"></i></div>
                    <h2 class="upload-dropzone-title">拖拽图片到这里</h2>
                    <p class="upload-dropzone-hint">或者点击下方按钮选择文件，也支持直接粘贴截图</p>
                    <label for="picker" class="modern-btn modern-btn-primary inline-flex min-h-[44px] cursor-pointer items-center gap-2 px-5 py-2.5 text-sm">
                        <i class="fas fa-folder-open" aria-hidden="true"></i> 选择图片
                    </label>
                    <p data-upload-alert class="mt-4 min-h-[20px] text-xs text-red-600" role="status" aria-live="polite"></p>
                    <div class="upload-dropzone-meta">
                        <span>{{ implode(' · ', array_map('strtoupper', $extensions)) }}</span>
                        <span>单张最大 {{ \App\Utils::formatSize($maxBytes) }}</span>
                        <span>最多 {{ $group->configs->get(\App\Enums\GroupConfigKey::ConcurrentUploadNum, 3) }} 个并发</span>
                    </div>
                </div>
            </div>

            <aside class="panel upload-options" aria-label="上传选项">
                <div class="upload-options-title"><span>上传选项</span><i class="fas fa-sliders-h text-blue-500" aria-hidden="true"></i></div>
                <label>储存策略
                    <select data-upload-strategy>
                        @foreach($strategies as $strategy)
                            <option value="{{ $strategy->id }}" @selected($defaultStrategy === $strategy->id)>{{ $strategy->name }}</option>
                        @endforeach
                    </select>
                </label>
                @auth
                    <label>相册
                        <select data-upload-album>
                            <option value="0">不归入相册</option>
                            @foreach($albums as $album)
                                <option value="{{ $album->id }}" @selected($defaultAlbum === $album->id)>{{ $album->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>可见性
                        <select data-upload-permission aria-describedby="upload-visibility-help">
                            <option value="{{ \App\Enums\ImagePermission::LinkOnly }}" @selected($defaultPermission === \App\Enums\ImagePermission::LinkOnly)>持链接可访问</option>
                            <option value="{{ \App\Enums\ImagePermission::Private }}" @selected($defaultPermission === \App\Enums\ImagePermission::Private)>仅自己可查看</option>
                            <option value="{{ \App\Enums\ImagePermission::Public }}" @selected($defaultPermission === \App\Enums\ImagePermission::Public)>公开到画廊</option>
                        </select>
                    </label>
                    <p id="upload-visibility-help" class="mt-3 text-xs leading-5 text-slate-500">私有图片只对你和管理员可见。公开图片可能出现在画廊。</p>
                @endauth
            </aside>
        </div>

        <div data-upload-batch hidden class="upload-queue panel">
            <div class="upload-queue-header">
                <div><h2 class="text-base font-bold text-slate-800">上传队列</h2><p data-upload-summary class="mt-1 text-xs text-slate-500" role="status"></p></div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" data-upload-clear class="modern-btn modern-btn-quiet min-h-[40px] px-3 text-xs">移除已完成</button>
                    <button type="button" data-upload-start class="modern-btn modern-btn-primary min-h-[40px] px-3 text-xs"><i class="fas fa-play" aria-hidden="true"></i> 开始上传</button>
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 sm:px-5">
                <label class="flex items-center gap-2 text-xs font-semibold text-slate-600">链接格式
                    <select data-upload-format class="!mt-0 !w-auto !py-2 !text-xs">
                        <option value="url">原图链接</option><option value="markdown">Markdown</option><option value="markdown_with_link">Markdown 带链接</option><option value="html">HTML</option><option value="bbcode">BBCode</option><option value="thumbnail_url">缩略图链接</option>
                    </select>
                </label>
                <button type="button" data-upload-copy class="modern-btn modern-btn-quiet min-h-[38px] px-3 text-xs"><i class="fas fa-copy" aria-hidden="true"></i> 复制已完成链接</button>
                <p data-upload-copy-status class="w-full text-xs text-slate-500" role="status" aria-live="polite"></p>
            </div>
            <ol data-upload-list class="upload-queue-list divide-y divide-slate-100" aria-label="上传列表"></ol>
        </div>
    @endif

    <template data-upload-row>
        <li class="flex flex-wrap items-start gap-3 py-3 sm:flex-nowrap">
            <img data-preview alt="" hidden width="52" height="52" class="h-12 w-12 shrink-0 rounded-xl border border-slate-200 bg-slate-50 object-cover">
            <div class="min-w-[10rem] flex-1">
                <p data-name class="break-all text-sm font-bold text-slate-800"></p>
                <p data-state class="mt-1 text-xs text-slate-500"></p>
                <progress data-progress hidden class="mt-2 h-2 w-full" max="100"></progress>
                <input data-link hidden readonly class="mt-2 w-full rounded-lg border-slate-200 bg-slate-50 text-xs text-slate-700" aria-label="图片链接">
            </div>
            <div class="flex shrink-0 gap-2 text-xs">
                <button type="button" data-copy hidden class="modern-btn modern-btn-quiet min-h-[40px] px-3 text-xs">复制</button>
                <button type="button" data-action class="modern-btn modern-btn-quiet min-h-[40px] px-3 text-xs">移除</button>
            </div>
        </li>
    </template>
</section>
