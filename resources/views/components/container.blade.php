@props(['full' => request()->routeIs('images', 'gallery', 'admin.images')])

<div {{ $attributes->merge(['class' => ($full ? 'h-full' : 'h-full') . ' site-container mx-auto sm:ml-[252px]']) }}>
    {{ $slot }}
</div>
