@props([
    'alatId' => null,
    'kategoriId' => null,
    'kategori' => '',
    'namaAlat' => '',
    'class' => 'w-20 h-20',
])

@php
    $resolved = \App\Services\AlatIllustrationService::resolve(
        (string) $namaAlat,
        (string) $kategori,
        $alatId !== null ? (int) $alatId : null,
        $kategoriId !== null ? (int) $kategoriId : null
    );
@endphp

@if ($resolved['mode'] === 'upload' && !empty($resolved['url']))
    <img
        src="{{ $resolved['url'] }}"
        alt="{{ $namaAlat ?: ($kategori ?: 'Ilustrasi alat') }}"
        {{ $attributes->merge(['class' => $class . ' object-contain rounded-lg']) }}
    >
@else
    <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        {!! \App\Services\AlatIllustrationService::svgInner($resolved['type']) !!}
    </svg>
@endif
