@props([
    'icon' => 'box',
    'title' => 'Belum ada data',
    'description' => null,
    'primaryLabel' => null,
    'primaryHref' => null,
    'secondaryLabel' => null,
    'secondaryHref' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center text-center py-8 px-4']) }}>
    <div class="w-16 h-16 rounded-2xl bg-[#F7F7F8] border border-[#E5E7EB] flex items-center justify-center mb-4">
        @if ($icon === 'search')
            <svg class="w-8 h-8" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                <circle cx="21" cy="21" r="11" fill="#FFFFFF" stroke="#1F2937" stroke-width="2" />
                <circle cx="21" cy="21" r="6" fill="#FEF2F2" stroke="#E53935" stroke-width="1.6" stroke-dasharray="2.5 2.5" />
                <path d="M29.2 29.2L37.5 37.5" stroke="#1F2937" stroke-width="2.6" stroke-linecap="round" />
                <circle cx="37.5" cy="37.5" r="2" fill="#E53935" />
            </svg>
        @elseif ($icon === 'clipboard')
            <svg class="w-8 h-8" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                <rect x="11" y="10" width="26" height="30" rx="4" fill="#FFFFFF" stroke="#1F2937" stroke-width="2" />
                <rect x="18" y="6" width="12" height="7" rx="2.5" fill="#FEF2F2" stroke="#E53935" stroke-width="1.8" />
                <line x1="17" y1="21" x2="31" y2="21" stroke="#CBD5E1" stroke-width="2" stroke-linecap="round" />
                <line x1="17" y1="27" x2="28" y2="27" stroke="#CBD5E1" stroke-width="2" stroke-linecap="round" />
                <line x1="17" y1="33" x2="24" y2="33" stroke="#E53935" stroke-width="2" stroke-linecap="round" />
            </svg>
        @else
            <svg class="w-8 h-8" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                <path d="M9 17L24 9L39 17V33L24 41L9 33V17Z" fill="#FFFFFF" stroke="#1F2937" stroke-width="2" stroke-linejoin="round" />
                <path d="M9 17L24 25L39 17" stroke="#1F2937" stroke-width="1.8" stroke-linejoin="round" />
                <line x1="24" y1="25" x2="24" y2="41" stroke="#1F2937" stroke-width="1.8" />
                <path d="M16.5 13L31.5 21" stroke="#E53935" stroke-width="2" stroke-linecap="round" />
            </svg>
        @endif
    </div>

    <p class="text-sm font-semibold text-[#1F2937]">{{ $title }}</p>

    @if ($description)
        <p class="text-xs text-[#6B7280] mt-1 max-w-md leading-relaxed">{{ $description }}</p>
    @endif

    @if (($primaryLabel && $primaryHref) || ($secondaryLabel && $secondaryHref))
        <div class="mt-4 flex flex-wrap items-center justify-center gap-2.5">
            @if ($primaryLabel && $primaryHref)
                <a
                    href="{{ $primaryHref }}"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-[#E53935] hover:bg-[#C62828] text-xs font-semibold text-white transition-colors"
                >
                    {{ $primaryLabel }}
                </a>
            @endif
            @if ($secondaryLabel && $secondaryHref)
                <a
                    href="{{ $secondaryHref }}"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition-colors"
                >
                    {{ $secondaryLabel }}
                </a>
            @endif
        </div>
    @endif
</div>
