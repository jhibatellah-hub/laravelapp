@props([
    'variant' => 'avatar',
])

@php
    $logoSrc = file_exists(public_path('images/clinic-profile-logo.png'))
        ? asset('images/clinic-profile-logo.png')
        : asset('images/clinic-profile-logo.svg');
@endphp

<img
    src="{{ $logoSrc }}"
    alt=""
    decoding="async"
    {{ $attributes->class([
        'clinic-logo',
        $variant === 'brand' ? 'clinic-logo--brand' : 'clinic-logo--avatar',
    ]) }}
>
