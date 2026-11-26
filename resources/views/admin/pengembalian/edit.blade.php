@extends('layouts.app')

@section('title', 'Edit Pengembalian')

@section('content')
    @php
        $pinjam = $pengembalian->peminjaman;
    @endphp

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <nav class="text-xs text-[#6B7280] mb-1">
                <a href="{{ route('admin.pengembalian.index') }}" class="hover:text-[#E53935] transition">Data Pengembalian</a>
                <span class="mx-1.5">/</span>
                <span class="text-[#1F2937] font-medium">Edit #PMJ-{{ $pengembalian->id_peminjaman }}</span>
            </nav>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-bold text-[#1F2937] tracking-tight">Edit Pengembalian #PMJ-{{ $pengembalian->id_peminjaman }}</h1>
                <span class="px-2.5 py-0.5 rounded-full bg-[#1F2937] text-white text-[11px] font-semibold">Mode Edit</span>
            </div>
        </div>
        <a
            href="{{ route('admin.pengembalian.index') }}"
            class="inline-flex items-center gap-1.5 h-9 px-4 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition self-start sm:self-auto"
        >
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <form method="POST" action="{{ route('admin.pengembalian.update', $pengembalian) }}" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        @csrf
        @method('PUT')

        {{-- Card Form Utama --}}
        <div class="lg:col-span-8 lp-card-3d bg-white border border-[#E5E7EB] rounded-2xl overflow-hidden">
            <div class="h-1.5 w-full bg-[#E53935]"></div>

            <div class="p-6 space-y-5">
                <div class="flex items-center gap-3 pb-4 border-b border-[#F3F4F6]">
                    <span class="w-10 h-10 rounded-xl bg-[#FEF2F2] text-[#E53935] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 20h9"/>
                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-[#1F2937]">Perbarui Data Pengembalian</h2>
                        <p class="text-xs text-[#6B7280]">Sesuaikan petugas penerima, tanggal pengembalian, dan nominal denda.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="id_petugas" class="block text-xs font-semibold text-[#1F2937] mb-1.5">Petugas Penerima</label>
                        <select
                            id="id_petugas"
                            name="id_petugas"
                            required
                            class="w-full h-10 px-3.5 rounded-xl bg-white border border-[#E5E7EB] text-sm text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
                        >
                            @foreach ($petugass as $ptg)
                                <option value="{{ $ptg->id_user }}" {{ (string) old('id_petugas', $pengembalian->id_petugas ?? auth()->id()) === (string) $ptg->id_user ? 'selected' : '' }}>
                                    {{ $ptg->username }} ({{ $ptg->role }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_petugas')
                            <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_kembali" class="block text-xs font-semibold text-[#1F2937] mb-1.5">Tanggal Kembali</label>
                        <input
                            id="tanggal_kembali"
                            type="date"
                            name="tanggal_kembali"
                            min="{{ $pinjam?->tanggal_pinjam?->toDateString() }}"
                            value="{{ old('tanggal_kembali', $pengembalian->tanggal_kembali?->toDateString()) }}"
                            required
                            class="w-full h-10 px-3.5 rounded-xl bg-[#F7F7F8] focus:bg-white border border-[#E5E7EB] text-sm text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
                        >
                        @error('tanggal_kembali')
                            <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="denda" class="block text-xs font-semibold text-[#1F2937]">Nominal Denda (Rp)</label>
                        <span id="denda-preview" class="text-xs font-bold text-[#1F2937]">
                            Rp {{ number_format((int) old('denda', $pengembalian->denda), 0, ',', '.') }}
                        </span>
                    </div>
                    <input
                        id="denda"
                        type="number"
                        name="denda"
                        value="{{ old('denda', (int) $pengembalian->denda) }}"
                        min="0"
                        step="1"
                        required
                        class="w-full h-10 px-3.5 rounded-xl bg-[#F7F7F8] focus:bg-white border border-[#E5E7EB] text-sm font-semibold text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
                    >
                    @error('denda')
                        <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="px-6 py-4 bg-[#F7F7F8] border-t border-[#E5E7EB] flex items-center justify-end gap-2.5">
                <a
                    href="{{ route('admin.pengembalian.index') }}"
                    class="inline-flex items-center h-9 px-5 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="lp-btn-red h-9 px-6 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold transition"
                >
                    Simpan Perubahan
                </button>
            </div>
        </div>

        {{-- Sidebar Ringkasan Peminjaman & Kalkulator Denda --}}
        <aside class="lg:col-span-4 lp-card-3d bg-white border border-[#E5E7EB] rounded-2xl p-5 space-y-4">
            <span class="block text-[11px] font-bold uppercase tracking-wider text-[#6B7280]">Ringkasan Peminjaman</span>

            <div class="p-3.5 rounded-xl bg-[#F7F7F8] border border-[#E5E7EB] space-y-2.5 text-xs">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-[#6B7280]">Kode Transaksi</p>
                        <p class="font-bold text-[#1F2937] mt-0.5">#PMJ-{{ $pengembalian->id_peminjaman }}</p>
                    </div>
                    <div>
                        <p class="text-[#6B7280]">Peminjam</p>
                        <p class="font-semibold text-[#1F2937] mt-0.5">{{ $pinjam?->peminjam?->username ?? '-' }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-[#6B7280]">Tanggal Pinjam</p>
                        <p class="font-semibold text-[#1F2937] mt-0.5">{{ $pinjam?->tanggal_pinjam?->format('d/m/Y') ?? '-' }}</p>
                    </div>
                </div>

                @if ($pinjam && $pinjam->detailPeminjaman->isNotEmpty())
                    <div class="pt-2.5 border-t border-[#E5E7EB]">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#6B7280] mb-1.5">Daftar Alat</p>
                        <ul class="space-y-1 text-[#1F2937]">
                            @foreach ($pinjam->detailPeminjaman as $detail)
                                <li class="flex items-center justify-between gap-2">
                                    <span class="font-medium">{{ $detail->alat?->nama_alat ?? 'Alat tidak ditemukan' }}</span>
                                    <span class="text-[#6B7280] font-semibold">{{ $detail->jumlah_pinjam }} unit</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="rounded-xl border border-[#E5E7EB] bg-[#F7F7F8] p-3.5 space-y-2">
                <p id="saran-denda-text" class="text-xs text-[#6B7280] leading-relaxed"></p>
                <button
                    type="button"
                    id="btn-gunakan-saran"
                    class="hidden inline-flex items-center h-7 px-3 rounded-full bg-white border border-[#E53935] text-[11px] font-semibold text-[#E53935] hover:bg-[#FEF2F2] transition"
                >
                    Gunakan Saran
                </button>
            </div>
        </aside>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            const BATAS_HARI = {{ (int) $batasHari }};
            const TARIF_PER_HARI = {{ (int) $tarifPerHari }};
            const tanggalPinjam = @json($pinjam?->tanggal_pinjam?->toDateString());

            const inputTanggal = document.getElementById('tanggal_kembali');
            const inputDenda = document.getElementById('denda');
            const elSaranText = document.getElementById('saran-denda-text');
            const elPreview = document.getElementById('denda-preview');
            const btnSaran = document.getElementById('btn-gunakan-saran');

            let currentSaran = 0;

            function formatRupiah(amount) {
                const num = Math.max(0, parseInt(amount, 10) || 0);
                return 'Rp ' + num.toLocaleString('id-ID');
            }

            function parseUtc(ymd) {
                if (!ymd) return null;
                const p = ymd.split('-').map(Number);
                if (p.length !== 3 || p.some(Number.isNaN)) return null;
                return Date.UTC(p[0], p[1] - 1, p[2]);
            }

            function updatePreview() {
                const val = parseInt(inputDenda.value, 10);
                elPreview.textContent = formatRupiah(Number.isNaN(val) ? 0 : val);
                if (!Number.isNaN(val) && val !== currentSaran) {
                    btnSaran.classList.remove('hidden');
                } else {
                    btnSaran.classList.add('hidden');
                }
            }

            function recalculate(applyToInput) {
                const pinjamMs = parseUtc(tanggalPinjam);
                const kembaliMs = parseUtc(inputTanggal.value);
                let hariPinjam = 0;
                if (pinjamMs !== null && kembaliMs !== null && kembaliMs > pinjamMs) {
                    hariPinjam = Math.round((kembaliMs - pinjamMs) / 86400000);
                }

                const hariTerlambat = Math.max(0, hariPinjam - BATAS_HARI);
                currentSaran = hariTerlambat * TARIF_PER_HARI;

                if (hariTerlambat > 0) {
                    elSaranText.className = 'text-xs text-[#DC2626] font-medium leading-relaxed';
                    elSaranText.textContent =
                        'Terlambat ' + hariTerlambat + ' hari (durasi ' + hariPinjam + ' hari, batas ' + BATAS_HARI + ' hari) — saran ' +
                        formatRupiah(currentSaran) + '.';
                } else {
                    elSaranText.className = 'text-xs text-[#16A34A] font-medium leading-relaxed';
                    elSaranText.textContent =
                        'Tepat waktu (durasi ' + hariPinjam + ' hari dari batas ' + BATAS_HARI + ' hari) — saran Rp 0.';
                }

                if (applyToInput) {
                    inputDenda.value = String(currentSaran);
                }
                updatePreview();
            }

            inputTanggal.addEventListener('change', function () {
                recalculate(true);
            });
            inputDenda.addEventListener('input', updatePreview);
            btnSaran.addEventListener('click', function () {
                inputDenda.value = String(currentSaran);
                updatePreview();
            });

            recalculate(false);
        })();
    </script>
@endpush
