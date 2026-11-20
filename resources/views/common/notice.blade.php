@if($_is_notice)

    <x-modal id="notice-modal">
        <div class="markdown-body">
            {!! \Illuminate\Support\Str::markdown((string) \App\Utils::config(\App\Enums\ConfigKey::SiteNotice), [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
                'max_nesting_level' => 20,
            ]) !!}
        </div>
        <div class="mt-4 w-full text-right">
            <x-button type="button" @click="$store.modal.close('notice-modal');">OK</x-button>
        </div>
    </x-modal>

    @push('scripts')
        <script>
            let noticeHash = "{{ md5(\App\Utils::config(\App\Enums\ConfigKey::SiteNotice)) }}";

            let openNotice = function () {
                Alpine.store('modal').open('notice-modal');
                localStorage.setItem('notice-hash', noticeHash);
            }

            if (localStorage.getItem('notice-hash') !== noticeHash) {
                setTimeout(function () {
                    openNotice();
                }, 1000)
            }
        </script>
    @endpush
@endif
