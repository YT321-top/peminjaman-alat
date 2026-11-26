{{-- Global Custom Confirm Dialog LabPinjam (Pengganti confirm() bawaan browser) --}}
<div
    id="lp-confirm-modal"
    class="no-print fixed inset-0 z-[100] hidden items-center justify-center bg-[#1F2937]/45 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="lp-confirm-title"
>
    <div
        id="lp-confirm-card"
        class="lp-modal-pop relative bg-white rounded-3xl border border-[#E5E7EB] shadow-2xl max-w-[340px] w-full px-6 pt-6 pb-5 text-center"
    >
        <button
            type="button"
            id="lp-confirm-close"
            aria-label="Tutup dialog"
            class="absolute top-3.5 right-3.5 w-8 h-8 rounded-full text-[#6B7280] hover:text-[#1F2937] hover:bg-[#F7F7F8] flex items-center justify-center transition cursor-pointer"
        >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>

        <div id="lp-confirm-illu" class="mx-auto w-28 h-24 flex items-center justify-center" aria-hidden="true"></div>

        <h3 id="lp-confirm-title" class="mt-2 text-base font-bold text-[#1F2937] leading-snug">
            Konfirmasi Tindakan
        </h3>

        <div
            id="lp-confirm-target-wrap"
            class="mt-1.5 hidden inline-flex items-center px-2.5 py-0.5 rounded-full bg-[#F7F7F8] border border-[#E5E7EB] text-[11px] font-semibold text-[#1F2937] max-w-full truncate"
        >
            <span id="lp-confirm-target" class="truncate"></span>
        </div>

        <p id="lp-confirm-msg" class="mt-2 text-xs text-[#6B7280] leading-relaxed"></p>

        <div class="mt-5 flex flex-col items-center gap-2">
            <button
                type="button"
                id="lp-confirm-ok"
                class="min-w-[168px] h-10 px-6 rounded-full border-[1.5px] border-[#E53935] bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-bold shadow-sm transition cursor-pointer"
            >
                Ya, Lanjutkan
            </button>
            <button
                type="button"
                id="lp-confirm-cancel"
                class="px-4 py-1 text-xs font-semibold text-[#6B7280] hover:text-[#1F2937] transition cursor-pointer"
            >
                Batal
            </button>
        </div>
    </div>
</div>
