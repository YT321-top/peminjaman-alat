@extends('layouts.app')

@section('title', 'Pantau & Konfirmasi Pengembalian')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-[#1F2937]">Pantau &amp; Konfirmasi Pengembalian</h1>
            <p class="text-sm text-[#6B7280] mt-0.5">Verifikasi penerimaan fisik alat, kembalikan stok otomatis, dan kelola denda keterlambatan.</p>
        </div>
        <a
            href="{{ route('petugas.laporan.index') }}"
            class="inline-flex items-center justify-center h-9 px-4 rounded-full border border-[#E5E7EB] bg-white hover:border-[#E53935] hover:text-[#E53935] text-xs font-semibold text-[#1F2937] transition self-start sm:self-auto"
        >
            Lihat Laporan
        </a>
    </div>

    {{-- Pesan Error Validasi Form Konfirmasi --}}
    @if ($errors->any())
        <div class="mb-5 rounded-lg bg-white border border-[#E5E7EB] border-l-4 border-l-[#DC2626] px-4 py-3">
            <p class="text-xs font-semibold text-[#DC2626] mb-1">Konfirmasi pengembalian gagal:</p>
            <ul class="list-disc list-inside text-xs text-[#DC2626] space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Info Aturan Denda Standar --}}
    <div class="lp-panel-lg mb-5 rounded-xl bg-white border border-[#E5E7EB] px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-[#6B7280]">
        <span>
            Ketentuan standar lab: batas pinjam <strong class="font-semibold text-[#1F2937]">{{ $batasHari }} hari</strong> sejak tanggal pinjam, saran denda keterlambatan <strong class="font-semibold text-[#1F2937]">Rp {{ number_format($tarifPerHari, 0, ',', '.') }}/hari</strong>.
        </span>
        <span class="text-[#1F2937] font-medium shrink-0">Nominal denda bebas disesuaikan manual saat konfirmasi.</span>
    </div>

    @php
        $tabs = [
            'verifikasi' => 'Menunggu Verifikasi',
            'dipinjam' => 'Sedang Dipinjam',
            'selesai' => 'Selesai',
        ];
    @endphp

    {{-- Kontainer Tab, Pencarian & Tabel --}}
    <div style="--d: 60ms;" class="lp-panel-lg bg-white border border-[#E5E7EB] rounded-xl overflow-hidden">
        <div class="px-4 border-b border-[#E5E7EB] flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <div class="flex items-center gap-6 overflow-x-auto">
                @foreach ($tabs as $key => $label)
                    @php
                        $isActive = $tab === $key;
                        $tabParams = array_filter([
                            'tab' => $key,
                            'q' => $q !== '' ? $q : null,
                        ], fn ($v) => $v !== null);
                    @endphp
                    <a
                        href="{{ route('petugas.pengembalian.index', $tabParams) }}"
                        class="py-3.5 text-sm whitespace-nowrap border-b-2 transition flex items-center gap-2 {{ $isActive ? 'border-[#E53935] text-[#1F2937] font-semibold' : 'border-transparent text-[#6B7280] hover:text-[#1F2937] font-medium' }}"
                    >
                        <span>{{ $label }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $isActive ? 'bg-[#FEF2F2] text-[#E53935]' : 'bg-[#F7F7F8] text-[#6B7280]' }}">
                            {{ $counts[$key] ?? 0 }}
                        </span>
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('petugas.pengembalian.index') }}" class="flex items-center gap-2 py-3 lg:py-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="relative flex-1 sm:flex-initial">
                    <svg class="w-4 h-4 text-[#6B7280] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"/>
                        <line x1="20" y1="20" x2="16.2" y2="16.2"/>
                    </svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ $q }}"
                        placeholder="Username atau #PMJ-1..."
                        class="w-full sm:w-56 h-9 pl-9 pr-3 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] text-xs text-[#1F2937] placeholder-[#6B7280] focus:bg-white focus:border-[#E53935] focus:outline-none transition"
                    >
                </div>
                <button
                    type="submit"
                    class="h-9 px-4 rounded-lg bg-[#1F2937] hover:bg-[#111827] text-white text-xs font-semibold transition shrink-0"
                >
                    Cari
                </button>
                @if ($q !== '')
                    <a
                        href="{{ route('petugas.pengembalian.index', ['tab' => $tab]) }}"
                        class="h-9 px-3 rounded-lg border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-medium text-[#6B7280] hover:text-[#1F2937] inline-flex items-center transition shrink-0"
                    >
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7F7F8] border-b border-[#E5E7EB] text-[11px] font-semibold uppercase tracking-wider text-[#6B7280]">
                        <th class="px-5 py-3">Kode</th>
                        <th class="px-5 py-3">Peminjam</th>
                        <th class="px-5 py-3">Tanggal Pinjam</th>
                        <th class="px-5 py-3">Rincian Alat</th>
                        @if ($tab === 'selesai')
                            <th class="px-5 py-3">Tanggal Kembali</th>
                            <th class="px-5 py-3">Denda</th>
                            <th class="px-5 py-3">Petugas Penerima</th>
                        @else
                            <th class="px-5 py-3">Status &amp; Estimasi Denda</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB] text-sm">
                    @forelse ($peminjamans as $pinjam)
                        @php
                            $pengembalian = $pinjam->pengembalian;
                            $estimasi = $estimasiMap[$pinjam->id_peminjaman] ?? [
                                'default_tanggal_kembali' => today()->toDateString(),
                                'hari_pinjam' => 0,
                                'hari_terlambat' => 0,
                                'estimasi_denda' => 0,
                            ];

                            $modalPayload = [
                                'id' => $pinjam->id_peminjaman,
                                'kode' => '#PMJ-' . $pinjam->id_peminjaman,
                                'peminjam' => $pinjam->peminjam?->username ?? '-',
                                'tanggal_pinjam' => $pinjam->tanggal_pinjam?->toDateString(),
                                'tanggal_pinjam_label' => $pinjam->tanggal_pinjam?->format('d/m/Y') ?? '-',
                                'default_tanggal_kembali' => $estimasi['default_tanggal_kembali'],
                                'sudah_diajukan' => $pengembalian !== null && is_null($pengembalian->id_petugas),
                                'action_url' => route('petugas.pengembalian.konfirmasi', $pinjam),
                                'items' => $pinjam->detailPeminjaman->map(fn ($d) => [
                                    'nama' => $d->alat?->nama_alat ?? 'Alat tidak ditemukan',
                                    'jumlah' => (int) $d->jumlah_pinjam,
                                ])->values()->all(),
                            ];
                        @endphp
                        <tr class="lp-tr-row align-top">
                            <td class="px-5 py-4 font-bold text-[#1F2937] whitespace-nowrap">
                                #PMJ-{{ $pinjam->id_peminjaman }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="text-sm font-semibold text-[#1F2937]">{{ $pinjam->peminjam?->username ?? '-' }}</p>
                                @if ($pinjam->petugas)
                                    <p class="text-[11px] text-[#6B7280] mt-0.5">Disetujui: {{ $pinjam->petugas->username }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs text-[#6B7280] whitespace-nowrap">
                                {{ $pinjam->tanggal_pinjam?->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-4 min-w-[230px]">
                                <ul class="space-y-1">
                                    @foreach ($pinjam->detailPeminjaman as $detail)
                                        <li class="text-xs text-[#1F2937] flex items-center justify-between gap-3">
                                            <span class="font-medium">{{ $detail->alat?->nama_alat ?? 'Alat tidak ditemukan' }}</span>
                                            <span class="text-[#6B7280] whitespace-nowrap">{{ $detail->jumlah_pinjam }} unit</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>

                            @if ($tab === 'selesai')
                                <td class="px-5 py-4 text-xs text-[#1F2937] font-medium whitespace-nowrap">
                                    {{ $pengembalian?->tanggal_kembali?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="px-5 py-4 text-xs font-semibold whitespace-nowrap {{ ((int) ($pengembalian?->denda ?? 0)) > 0 ? 'text-[#DC2626]' : 'text-[#16A34A]' }}">
                                    Rp {{ number_format((int) ($pengembalian?->denda ?? 0), 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <p class="text-xs font-medium text-[#1F2937]">{{ $pengembalian?->petugas?->username ?? '-' }}</p>
                                    <span class="mt-1 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#F0FDF4] text-[#16A34A]">
                                        Selesai
                                    </span>
                                </td>
                            @else
                                <td class="px-5 py-4 min-w-[230px]">
                                    @if ($tab === 'verifikasi')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#FFFBEB] text-[#D97706]">
                                            Menunggu Verifikasi
                                        </span>
                                        <p class="text-[11px] text-[#6B7280] mt-1">
                                            Diajukan kembali: <strong class="text-[#1F2937]">{{ $pengembalian?->tanggal_kembali?->format('d/m/Y') }}</strong>
                                        </p>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#EFF6FF] text-[#2563EB]">
                                            Sedang Dipinjam
                                        </span>
                                        <p class="text-[11px] text-[#6B7280] mt-1">
                                            Belum diajukan kembali oleh peminjam
                                        </p>
                                    @endif

                                    @if ($estimasi['hari_terlambat'] > 0)
                                        <p class="text-xs font-semibold text-[#DC2626] mt-1">
                                            Terlambat {{ $estimasi['hari_terlambat'] }} hari — saran denda Rp {{ number_format($estimasi['estimasi_denda'], 0, ',', '.') }}
                                        </p>
                                    @else
                                        <p class="text-xs text-[#16A34A] font-medium mt-1">
                                            Tepat waktu ({{ $estimasi['hari_pinjam'] }}/{{ $batasHari }} hari) — saran denda Rp 0
                                        </p>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <button
                                        type="button"
                                        data-konfirmasi="{{ json_encode($modalPayload, JSON_UNESCAPED_UNICODE) }}"
                                        onclick="bukaModalKonfirmasi(this)"
                                        class="lp-btn-red h-8 px-4 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold"
                                    >
                                        Konfirmasi Terima
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $tab === 'selesai' ? 7 : 6 }}" class="px-5 py-4">
                                @php
                                    if ($q !== '') {
                                        $pengembalianDesc = 'Tidak ditemukan peminjaman dengan kata kunci "' . $q . '".';
                                    } elseif ($tab === 'verifikasi') {
                                        $pengembalianDesc = 'Saat ini belum ada pengajuan pengembalian dari peminjam yang menunggu verifikasi.';
                                    } elseif ($tab === 'dipinjam') {
                                        $pengembalianDesc = 'Tidak ada peminjaman aktif yang sedang dipinjam saat ini.';
                                    } else {
                                        $pengembalianDesc = 'Belum ada riwayat pengembalian yang selesai dikonfirmasi.';
                                    }
                                @endphp
                                <x-empty-state
                                    :icon="$q !== '' ? 'search' : 'clipboard'"
                                    :title="'Tidak ada data pada tab ' . ($tabs[$tab] ?? $tab) . '.'"
                                    :description="$pengembalianDesc"
                                    :primary-label="$tab !== 'selesai' ? 'Lihat Riwayat Selesai' : 'Lihat Tab Verifikasi'"
                                    :primary-href="$tab !== 'selesai' ? route('petugas.pengembalian.index', ['tab' => 'selesai']) : route('petugas.pengembalian.index', ['tab' => 'verifikasi'])"
                                    :secondary-label="$q !== '' ? 'Reset Pencarian' : null"
                                    :secondary-href="$q !== '' ? route('petugas.pengembalian.index', ['tab' => $tab]) : null"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($peminjamans->hasPages())
        <div class="mt-6">
            {{ $peminjamans->links() }}
        </div>
    @endif

    {{-- Modal Konfirmasi Penerimaan Alat & Denda Hybrid --}}
    <div
        id="konfirmasi-modal"
        class="no-print fixed inset-0 z-50 hidden items-center justify-center bg-[#1F2937]/40 px-4 py-6 overflow-y-auto"
        role="dialog"
        aria-modal="true"
        aria-labelledby="konfirmasi-modal-title"
    >
        <div class="w-full max-w-lg bg-white border border-[#E5E7EB] border-t-4 border-t-[#E53935] rounded-xl p-6 my-auto">
            <div class="flex items-start justify-between gap-4 pb-4 border-b border-[#E5E7EB]">
                <div>
                    <p id="modal-status-label" class="text-[11px] font-semibold uppercase tracking-wider text-[#E53935]">
                        Verifikasi Pengembalian
                    </p>
                    <h2 id="konfirmasi-modal-title" class="text-base font-bold text-[#1F2937] mt-0.5">
                        Konfirmasi Terima Alat <span id="modal-kode"></span>
                    </h2>
                </div>
                <button
                    type="button"
                    id="modal-close-btn"
                    class="p-1 rounded-lg text-[#6B7280] hover:bg-[#F7F7F8] hover:text-[#1F2937] transition"
                    aria-label="Tutup modal"
                >
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            {{-- Ringkasan Transaksi & Daftar Alat --}}
            <div class="mt-4 p-4 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] space-y-3">
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <p class="text-[#6B7280]">Peminjam</p>
                        <p id="modal-peminjam" class="font-semibold text-[#1F2937] mt-0.5">-</p>
                    </div>
                    <div>
                        <p class="text-[#6B7280]">Tanggal Pinjam</p>
                        <p id="modal-tanggal-pinjam" class="font-semibold text-[#1F2937] mt-0.5">-</p>
                    </div>
                </div>

                <div class="pt-3 border-t border-[#E5E7EB]">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#6B7280] mb-1.5">Alat yang Dikembalikan</p>
                    <ul id="modal-items-list" class="space-y-1 text-xs text-[#1F2937]"></ul>
                </div>
            </div>

            <form id="konfirmasi-form" method="POST" action="" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label for="input-tanggal-kembali" class="block text-xs font-semibold text-[#1F2937] mb-1.5">
                        Tanggal Kembali
                    </label>
                    <input
                        id="input-tanggal-kembali"
                        type="date"
                        name="tanggal_kembali"
                        required
                        class="w-full h-10 px-3 rounded-lg bg-white border border-[#E5E7EB] text-sm text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
                    >
                </div>

                {{-- Kotak Saran Denda Otomatis --}}
                <div id="saran-denda-box" class="rounded-lg border border-[#E5E7EB] bg-[#F7F7F8] px-3.5 py-2.5 flex items-start justify-between gap-3">
                    <p id="saran-denda-text" class="text-xs text-[#6B7280] leading-relaxed"></p>
                    <button
                        type="button"
                        id="btn-gunakan-saran"
                        class="hidden shrink-0 text-[11px] font-semibold text-[#E53935] hover:text-[#C62828] underline"
                    >
                        Gunakan Saran
                    </button>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="input-denda" class="block text-xs font-semibold text-[#1F2937]">
                            Nominal Denda (Rp)
                        </label>
                        <span id="denda-preview" class="text-xs font-semibold text-[#6B7280]">Rp 0</span>
                    </div>
                    <input
                        id="input-denda"
                        type="number"
                        name="denda"
                        min="0"
                        step="1"
                        value="0"
                        required
                        class="w-full h-10 px-3 rounded-lg bg-white border border-[#E5E7EB] text-sm font-semibold text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
                    >
                    <p class="text-[11px] text-[#6B7280] mt-1">
                        Isi 0 untuk dispensasi atau tanpa denda, atau sesuaikan nominal jika terdapat kerusakan alat.
                    </p>
                </div>

                <div class="pt-3 border-t border-[#E5E7EB] flex items-center justify-end gap-2.5">
                    <button
                        type="button"
                        id="modal-cancel-btn"
                        class="h-10 px-5 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="h-10 px-5 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold transition"
                    >
                        Konfirmasi &amp; Kembalikan Stok
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const BATAS_HARI = {{ (int) $batasHari }};
            const TARIF_PER_HARI = {{ (int) $tarifPerHari }};

            const modal = document.getElementById('konfirmasi-modal');
            const form = document.getElementById('konfirmasi-form');
            const closeBtn = document.getElementById('modal-close-btn');
            const cancelBtn = document.getElementById('modal-cancel-btn');

            const elStatusLabel = document.getElementById('modal-status-label');
            const elKode = document.getElementById('modal-kode');
            const elPeminjam = document.getElementById('modal-peminjam');
            const elTanggalPinjam = document.getElementById('modal-tanggal-pinjam');
            const elItemsList = document.getElementById('modal-items-list');

            const inputTanggalKembali = document.getElementById('input-tanggal-kembali');
            const inputDenda = document.getElementById('input-denda');
            const elSaranText = document.getElementById('saran-denda-text');
            const elDendaPreview = document.getElementById('denda-preview');
            const btnGunakanSaran = document.getElementById('btn-gunakan-saran');

            let activeTanggalPinjam = null;
            let currentSaranDenda = 0;

            function formatRupiah(amount) {
                const numeric = Math.max(0, parseInt(amount, 10) || 0);
                return 'Rp ' + numeric.toLocaleString('id-ID');
            }

            function parseDateUtc(ymd) {
                if (!ymd || typeof ymd !== 'string') return null;
                const parts = ymd.split('-').map(Number);
                if (parts.length !== 3 || parts.some(Number.isNaN)) return null;
                return Date.UTC(parts[0], parts[1] - 1, parts[2]);
            }

            function updateDendaPreview() {
                const val = parseInt(inputDenda.value, 10);
                elDendaPreview.textContent = formatRupiah(Number.isNaN(val) ? 0 : val);
                if (!Number.isNaN(val) && val !== currentSaranDenda) {
                    btnGunakanSaran.classList.remove('hidden');
                } else {
                    btnGunakanSaran.classList.add('hidden');
                }
            }

            function recalculateSaran(applyToInput) {
                const pinjamMs = parseDateUtc(activeTanggalPinjam);
                const kembaliMs = parseDateUtc(inputTanggalKembali.value);

                let hariPinjam = 0;
                if (pinjamMs !== null && kembaliMs !== null && kembaliMs > pinjamMs) {
                    hariPinjam = Math.round((kembaliMs - pinjamMs) / 86400000);
                }

                const hariTerlambat = Math.max(0, hariPinjam - BATAS_HARI);
                currentSaranDenda = hariTerlambat * TARIF_PER_HARI;

                if (hariTerlambat > 0) {
                    elSaranText.className = 'text-xs text-[#DC2626] font-medium leading-relaxed';
                    elSaranText.textContent =
                        'Terlambat ' + hariTerlambat + ' hari (durasi ' + hariPinjam + ' hari, batas ' + BATAS_HARI + ' hari) — saran ' +
                        formatRupiah(currentSaranDenda) + ', bisa diubah manual untuk dispensasi atau denda kerusakan.';
                } else {
                    elSaranText.className = 'text-xs text-[#16A34A] font-medium leading-relaxed';
                    elSaranText.textContent =
                        'Tepat waktu (durasi ' + hariPinjam + ' hari dari batas ' + BATAS_HARI + ' hari) — saran Rp 0, bisa diubah manual jika ada denda kerusakan.';
                }

                if (applyToInput) {
                    inputDenda.value = String(currentSaranDenda);
                }

                updateDendaPreview();
            }

            window.bukaModalKonfirmasi = function (button) {
                const payload = JSON.parse(button.getAttribute('data-konfirmasi'));

                activeTanggalPinjam = payload.tanggal_pinjam;
                form.action = payload.action_url;

                elStatusLabel.textContent = payload.sudah_diajukan
                    ? 'Pengembalian Diajukan Peminjam'
                    : 'Pengembalian Langsung di Lab';
                elKode.textContent = payload.kode;
                elPeminjam.textContent = payload.peminjam;
                elTanggalPinjam.textContent = payload.tanggal_pinjam_label;

                elItemsList.innerHTML = '';
                payload.items.forEach(function (item) {
                    const li = document.createElement('li');
                    li.className = 'flex items-center justify-between gap-2';

                    const spanNama = document.createElement('span');
                    spanNama.className = 'font-medium text-[#1F2937]';
                    spanNama.textContent = item.nama;

                    const spanQty = document.createElement('span');
                    spanQty.className = 'text-[#6B7280] font-semibold';
                    spanQty.textContent = item.jumlah + ' unit';

                    li.appendChild(spanNama);
                    li.appendChild(spanQty);
                    elItemsList.appendChild(li);
                });

                inputTanggalKembali.min = payload.tanggal_pinjam || '';
                inputTanggalKembali.value = payload.default_tanggal_kembali || '';
                recalculateSaran(true);

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };

            function tutupModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            inputTanggalKembali.addEventListener('change', function () {
                recalculateSaran(true);
            });

            inputDenda.addEventListener('input', updateDendaPreview);

            btnGunakanSaran.addEventListener('click', function () {
                inputDenda.value = String(currentSaranDenda);
                updateDendaPreview();
            });

            closeBtn.addEventListener('click', tutupModal);
            cancelBtn.addEventListener('click', tutupModal);
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    tutupModal();
                }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    tutupModal();
                }
            });
        })();
    </script>
@endpush
