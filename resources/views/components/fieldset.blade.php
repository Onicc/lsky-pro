<fieldset class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
    <legend class="px-1 text-sm font-bold text-slate-800">{{ $title }}</legend>
    @isset($faq)
        <p class="mt-1 text-xs leading-5 text-slate-500">{!! $faq !!}</p>
    @endisset
    <div class="mt-3 flex flex-wrap gap-3">
        {{ $slot }}
    </div>
</fieldset>
