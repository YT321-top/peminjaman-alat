@if (session('added_item'))
    @php
        $added = session('added_item');
    @endphp
    <div
        id="added-cart-modal"
        class="no-print fixed inset-0 z-50 flex items-center justify-center bg-[#1F2937]/40 px-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="added-cart-modal-title"
    >
        <div class="w-full max-w-md bg-white border border-[#E5E7EB] border-t-4 border-t-[#E53935] rounded-xl p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#16A34A]">Berhasil Ditambahkan</p>
                    <h2 id="added-cart-modal-title" class="text-base font-bold text-[#1F2937] mt-0.5">
                        Alat Masuk ke Daftar Pinjam
                    </h2>
                </div>
                <button
                    type="button"
                    onclick="document.getElementById('added-cart-modal').remove()"
                    class="p-1 rounded-lg text-[#6B7280] hover:bg-[#F7F7F8] hover:text-[#1F2937] transition"
                    aria-label="Tutup"
                >
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <div class="mt-4 p-3.5 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] flex items-center gap-3.5">
                <div class="w-14 h-14 rounded-lg bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0">
                    <x-alat-illustration
                        :alat-id="$added['id_alat'] ?? null"
                        :kategori="$added['nama_kategori']"
                        :nama-alat="$added['nama_alat']"
                        class="w-11 h-11"
                    />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] uppercase tracking-wider text-[#6B7280]">{{ $added['nama_kategori'] }}</p>
                    <p class="text-sm font-semibold text-[#1F2937] truncate">{{ $added['nama_alat'] }}</p>
                    <p class="text-xs text-[#6B7280] mt-0.5">
                        Ditambahkan +{{ $added['jumlah_ditambah'] }} unit (total {{ $added['jumlah_di_keranjang'] }} unit di daftar)
                    </p>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between text-xs text-[#6B7280]">
                <span>Total di Daftar Pinjam</span>
                <span class="font-semibold text-[#1F2937]">
                    {{ $added['total_jenis_keranjang'] }} jenis alat ({{ $added['total_unit_keranjang'] }} unit)
                </span>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3">
                <button
                    type="button"
                    onclick="document.getElementById('added-cart-modal').remove()"
                    class="h-10 px-4 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition"
                >
                    Lanjut Pilih Alat
                </button>
                <a
                    href="{{ route('peminjam.daftar-pinjam.index') }}"
                    class="h-10 px-4 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold flex items-center justify-center transition"
                >
                    Lihat Daftar Pinjam
                </a>
            </div>
        </div>
    </div>
@endif
