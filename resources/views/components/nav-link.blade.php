@props(['active'])

@php
$classes = 'site-nav-link w-full flex items-center' . (($active ?? false) ? ' is-active' : '');
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $icon }}
    <span class="tracking-widest">{{ $name }}</span>
</a>
