<div
    id="lp-quick-search-modal"
    class="no-print hidden fixed inset-0 z-50 flex items-start justify-center pt-16 sm:pt-24 p-4 bg-[#1F2937]/40"
    role="dialog"
    aria-modal="true"
    aria-labelledby="lp-quick-search-title"
>
    <div
        id="lp-quick-search-card"
        class="lp-modal-pop w-full max-w-lg rounded-2xl bg-white border border-[#E5E7EB] shadow-xl overflow-hidden"
    >
        <h2 id="lp-quick-search-title" class="sr-only">Pencarian Cepat &amp; Navigasi</h2>

        <form id="lp-quick-search-form" action="{{ $searchAction }}" method="GET" class="flex items-center gap-3 px-4 py-3 border-b border-[#E5E7EB]">
            <svg class="w-4 h-4 text-[#E53935] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="11" cy="11" r="7"/>
                <line x1="20" y1="20" x2="16.2" y2="16.2"/>
            </svg>
            <input
                id="lp-quick-search-input"
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="{{ $searchPlaceholder }}"
                autocomplete="off"
                class="flex-1 bg-transparent text-sm text-[#1F2937] placeholder-[#6B7280] focus:outline-none"
            >
            <button
                type="button"
                id="lp-quick-search-close"
                class="px-2 py-1 rounded-md border border-[#E5E7EB] bg-[#F7F7F8] text-[11px] font-semibold text-[#6B7280] hover:text-[#1F2937] hover:border-[#CBD5E1] transition-colors"
            >
                ESC
            </button>
        </form>

        <div class="max-h-80 overflow-y-auto p-2 space-y-2">
            <button
                type="submit"
                form="lp-quick-search-form"
                id="lp-quick-search-submit-row"
                class="hidden w-full px-3 py-2.5 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-left flex items-center justify-between gap-3 hover:bg-[#FEE2E2] transition-colors"
            >
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-7 h-7 rounded-lg bg-[#E53935] text-white flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7"/>
                            <line x1="20" y1="20" x2="16.2" y2="16.2"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-[#1F2937] truncate">
                            Cari "<span id="lp-quick-search-query-preview"></span>"
                        </p>
                        <p class="text-[11px] text-[#6B7280] truncate">{{ $searchPlaceholder }}</p>
                    </div>
                </div>
                <span class="text-[11px] font-semibold text-[#E53935] shrink-0">Enter</span>
            </button>

            <div>
                <p class="px-3 pt-1 pb-1.5 text-[11px] font-semibold uppercase tracking-wider text-[#6B7280]">
                    Navigasi Cepat
                </p>
                <div id="lp-quick-search-nav-list" class="space-y-0.5">
                    @foreach ($navItems as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            data-quick-nav-item
                            data-quick-nav-label="{{ mb_strtolower($item['label']) }}"
                            class="px-3 py-2.5 rounded-xl flex items-center justify-between gap-3 text-sm transition-colors {{ $item['active'] ? 'bg-[#F7F7F8] text-[#1F2937] font-semibold' : 'text-[#374151] hover:bg-[#F7F7F8] font-medium' }}"
                        >
                            <span class="flex items-center gap-2.5 min-w-0">
                                <span class="w-1.5 h-1.5 rounded-full {{ $item['active'] ? 'bg-[#E53935]' : 'bg-[#D1D5DB]' }}"></span>
                                <span class="truncate">{{ $item['label'] }}</span>
                            </span>
                            @if ($item['active'])
                                <span class="px-2 py-0.5 rounded-full bg-[#FEF2F2] border border-[#FECACA] text-[10px] font-semibold text-[#E53935] shrink-0">
                                    Halaman Aktif
                                </span>
                            @else
                                <span class="text-[11px] text-[#9CA3AF] shrink-0">Buka</span>
                            @endif
                        </a>
                    @endforeach
                </div>
                <p id="lp-quick-search-nav-empty" class="hidden px-3 py-4 text-center text-xs text-[#6B7280]">
                    Tidak ada menu yang cocok. Tekan Enter untuk mencari data di halaman ini.
                </p>
            </div>
        </div>

        <div class="px-4 py-2.5 bg-[#F7F7F8] border-t border-[#E5E7EB] flex items-center justify-between text-[11px] text-[#6B7280]">
            <span>Tekan <kbd class="px-1.5 py-0.5 rounded bg-white border border-[#E5E7EB] font-semibold text-[#1F2937]">Enter</kbd> untuk mencari</span>
            <span><kbd class="px-1.5 py-0.5 rounded bg-white border border-[#E5E7EB] font-semibold text-[#1F2937]">Ctrl</kbd> + <kbd class="px-1.5 py-0.5 rounded bg-white border border-[#E5E7EB] font-semibold text-[#1F2937]">K</kbd></span>
        </div>
    </div>
</div>
