@php
    $lpSuccessMessage = session('success') ?? session('status');
    $lpErrorMessage = session('error');
@endphp

@if ($lpSuccessMessage || $lpErrorMessage)
    <div
        id="lp-toast-container"
        class="no-print fixed top-20 right-4 sm:right-6 z-50 flex flex-col gap-2.5 w-[calc(100vw-2rem)] max-w-sm pointer-events-none"
        aria-live="polite"
        aria-atomic="true"
    >
        @if ($lpSuccessMessage)
            <div
                data-toast-item
                data-duration="4200"
                role="status"
                class="lp-toast lp-toast-enter pointer-events-auto relative overflow-hidden rounded-xl bg-white border border-[#E5E7EB] shadow-md"
            >
                <div class="flex items-start gap-3 px-4 py-3.5 pr-10">
                    <div class="w-8 h-8 rounded-lg bg-[#ECFDF5] border border-[#BBF7D0] text-[#16A34A] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-[#1F2937] leading-5">Berhasil</p>
                        <p class="text-xs text-[#6B7280] leading-relaxed mt-0.5 break-words">{{ $lpSuccessMessage }}</p>
                    </div>
                    <button
                        type="button"
                        data-toast-close
                        aria-label="Tutup notifikasi"
                        class="absolute top-2.5 right-2.5 w-7 h-7 rounded-full inline-flex items-center justify-center text-[#9CA3AF] hover:text-[#1F2937] hover:bg-[#F3F4F6] transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="h-1 w-full bg-[#F3F4F6] overflow-hidden">
                    <div class="lp-toast-bar h-full w-full bg-[#16A34A]"></div>
                </div>
            </div>
        @endif

        @if ($lpErrorMessage)
            <div
                data-toast-item
                data-duration="5200"
                role="alert"
                class="lp-toast lp-toast-enter pointer-events-auto relative overflow-hidden rounded-xl bg-white border border-[#E5E7EB] shadow-md"
            >
                <div class="flex items-start gap-3 px-4 py-3.5 pr-10">
                    <div class="w-8 h-8 rounded-lg bg-[#FEF2F2] border border-[#FECACA] text-[#DC2626] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-[#1F2937] leading-5">Terjadi Kendala</p>
                        <p class="text-xs text-[#DC2626] leading-relaxed mt-0.5 break-words">{{ $lpErrorMessage }}</p>
                    </div>
                    <button
                        type="button"
                        data-toast-close
                        aria-label="Tutup notifikasi"
                        class="absolute top-2.5 right-2.5 w-7 h-7 rounded-full inline-flex items-center justify-center text-[#9CA3AF] hover:text-[#1F2937] hover:bg-[#F3F4F6] transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="h-1 w-full bg-[#F3F4F6] overflow-hidden">
                    <div class="lp-toast-bar h-full w-full bg-[#DC2626]"></div>
                </div>
            </div>
        @endif
    </div>
@endif
