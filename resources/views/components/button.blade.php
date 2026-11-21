<button {{ $attributes->merge(['type' => 'submit', 'class' => 'modern-btn modern-btn-primary inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm']) }}>
    {{ $slot }}
</button>
