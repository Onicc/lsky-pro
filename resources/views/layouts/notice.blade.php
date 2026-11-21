@if($_is_notice)
    <button type="button" class="site-toolbar-trigger flex items-center gap-2" id="open-notice" aria-expanded="false" aria-haspopup="dialog">
        <div class="toolbar-icon">
            <i class="fas fa-envelope"></i>
        </div>
        <span class="sm:block hidden">公告</span>
    </button>
    @push('scripts')
        <script>
            $('#open-notice').click(function () {
                openNotice();
            });
        </script>
    @endpush
@endif
