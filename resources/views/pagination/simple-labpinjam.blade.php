@if ($paginator->hasPages())
    <nav
        role="navigation"
        aria-label="Navigasi Halaman"
        class="bg-white border border-[#E5E7EB] rounded-xl px-4 py-3 flex items-center justify-between gap-3"
    >
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-1 h-8 px-3 rounded-lg border border-[#E5E7EB] bg-[#F7F7F8] text-xs font-medium text-[#9CA3AF] cursor-not-allowed select-none">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                <span>Sebelumnya</span>
            </span>
        @else
            <a
                href="{{ $paginator->previousPageUrl() }}"
                rel="prev"
                class="group inline-flex items-center gap-1 h-8 px-3 rounded-lg border border-[#E5E7EB] bg-white text-xs font-medium text-[#1F2937] hover:border-[#E53935] hover:text-[#E53935] active:scale-95 transition-all duration-200"
            >
                <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:-translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                <span>Sebelumnya</span>
            </a>
        @endif

        <span class="text-xs text-[#6B7280] tabular-nums">
            Halaman <span class="font-semibold text-[#1F2937]">{{ $paginator->currentPage() }}</span>
        </span>

        @if ($paginator->hasMorePages())
            <a
                href="{{ $paginator->nextPageUrl() }}"
                rel="next"
                class="group inline-flex items-center gap-1 h-8 px-3 rounded-lg border border-[#E5E7EB] bg-white text-xs font-medium text-[#1F2937] hover:border-[#E53935] hover:text-[#E53935] active:scale-95 transition-all duration-200"
            >
                <span>Berikutnya</span>
                <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </a>
        @else
            <span class="inline-flex items-center gap-1 h-8 px-3 rounded-lg border border-[#E5E7EB] bg-[#F7F7F8] text-xs font-medium text-[#9CA3AF] cursor-not-allowed select-none">
                <span>Berikutnya</span>
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </span>
        @endif
    </nav>
@endif
