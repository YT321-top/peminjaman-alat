@if ($paginator->hasPages())
    <nav
        role="navigation"
        aria-label="Navigasi Halaman"
        class="bg-white border border-[#E5E7EB] rounded-xl px-4 py-3 flex flex-col sm:flex-row items-center justify-between gap-3"
    >
        {{-- Info Jumlah Data --}}
        <p class="text-xs text-[#6B7280] tabular-nums">
            @if ($paginator->firstItem())
                Menampilkan
                <span class="font-semibold text-[#1F2937]">{{ $paginator->firstItem() }}&ndash;{{ $paginator->lastItem() }}</span>
                dari
                <span class="font-semibold text-[#1F2937]">{{ $paginator->total() }}</span>
                data
            @else
                Menampilkan <span class="font-semibold text-[#1F2937]">{{ $paginator->count() }}</span> data
            @endif
        </p>

        {{-- Kontrol Halaman --}}
        <div class="flex flex-wrap items-center justify-center gap-1.5">
            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span
                    aria-disabled="true"
                    aria-label="Halaman sebelumnya"
                    class="inline-flex items-center gap-1 h-8 px-2.5 rounded-lg border border-[#E5E7EB] bg-[#F7F7F8] text-xs font-medium text-[#9CA3AF] cursor-not-allowed select-none"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </span>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    aria-label="Halaman sebelumnya"
                    class="group inline-flex items-center gap-1 h-8 px-2.5 rounded-lg border border-[#E5E7EB] bg-white text-xs font-medium text-[#1F2937] hover:border-[#E53935] hover:text-[#E53935] active:scale-95 transition-all duration-200"
                >
                    <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:-translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </a>
            @endif

            {{-- Nomor Halaman --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span
                        aria-disabled="true"
                        class="w-7 h-8 inline-flex items-center justify-center text-xs text-[#6B7280] select-none"
                    >
                        {{ $element }}
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span
                                aria-current="page"
                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-[#E53935] text-white text-xs font-semibold shadow-[0_2px_8px_rgba(229,57,53,0.28)] select-none tabular-nums"
                            >
                                {{ $page }}
                            </span>
                        @else
                            <a
                                href="{{ $url }}"
                                aria-label="Halaman {{ $page }}"
                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-transparent text-xs font-medium text-[#1F2937] hover:bg-[#FEF2F2] hover:text-[#E53935] hover:border-[#FECACA] active:scale-95 transition-all duration-200 tabular-nums"
                            >
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Tombol Berikutnya --}}
            @if ($paginator->hasMorePages())
                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    aria-label="Halaman berikutnya"
                    class="group inline-flex items-center gap-1 h-8 px-2.5 rounded-lg border border-[#E5E7EB] bg-white text-xs font-medium text-[#1F2937] hover:border-[#E53935] hover:text-[#E53935] active:scale-95 transition-all duration-200"
                >
                    <span class="hidden sm:inline">Berikutnya</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </a>
            @else
                <span
                    aria-disabled="true"
                    aria-label="Halaman berikutnya"
                    class="inline-flex items-center gap-1 h-8 px-2.5 rounded-lg border border-[#E5E7EB] bg-[#F7F7F8] text-xs font-medium text-[#9CA3AF] cursor-not-allowed select-none"
                >
                    <span class="hidden sm:inline">Berikutnya</span>
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
