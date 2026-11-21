@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'input mt-1 block w-full px-3.5 py-2.5 text-sm']) !!}>
