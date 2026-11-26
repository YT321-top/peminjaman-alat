@extends('layouts.app')

@section('title', 'Persetujuan Peminjaman')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-[#1F2937]">Persetujuan Peminjaman</h1>
            <p class="text-sm text-[#6B7280] mt-0.5">Periksa ketersediaan stok alat lalu setujui atau tolak pengajuan peminjaman.</p>
        </div>
        <a
            href="{{ route('petugas.pengembalian.index') }}"
            class="inline-flex items-center justify-center h-9 px-4 rounded-full border border-[#E5E7EB] bg-white hover:border-[#E53935] hover:text-[#E53935] text-xs font-semibold text-[#1F2937] transition self-start sm:self-auto"
        >
            Pantau Pengembalian
        </a>
    </div>

    {{-- Tab Status & Bar Pencarian --}}
    @php
        $tabs = [
            'menunggu' => 'Menunggu',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'semua' => 'Semua',
        ];
    @endphp

    <div class="lp-panel-lg bg-white border border-[#E5E7EB] rounded-xl mb-6 overflow-hidden">
        <div class="px-4 border-b border-[#E5E7EB] flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <div class="flex items-center gap-6 overflow-x-auto">
                @foreach ($tabs as $key => $label)
                    @php
                        $isActive = $status === $key;
                        $tabParams = array_filter([
                            'status' => $key,
                            'q' => $q !== '' ? $q : null,
                        ], fn ($v) => $v !== null);
                    @endphp
                    <a
                        href="{{ route('petugas.persetujuan.index', $tabParams) }}"
                        class="py-3.5 text-sm whitespace-nowrap border-b-2 transition flex items-center gap-2 {{ $isActive ? 'border-[#E53935] text-[#1F2937] font-semibold' : 'border-transparent text-[#6B7280] hover:text-[#1F2937] font-medium' }}"
                    >
                        <span>{{ $label }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $isActive ? 'bg-[#FEF2F2] text-[#E53935]' : 'bg-[#F7F7F8] text-[#6B7280]' }}">
                            {{ $counts[$key] ?? 0 }}
                        </span>
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('petugas.persetujuan.index') }}" class="flex items-center gap-2 py-3 lg:py-2">
                <input type="hidden" name="status" value="{{ $status }}">
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
                        href="{{ route('petugas.persetujuan.index', ['status' => $status]) }}"
                        class="h-9 px-3 rounded-lg border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-medium text-[#6B7280] hover:text-[#1F2937] inline-flex items-center transition shrink-0"
                    >
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Tabel Daftar Pengajuan --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7F7F8] border-b border-[#E5E7EB] text-[11px] font-semibold uppercase tracking-wider text-[#6B7280]">
                        <th class="px-5 py-3">Kode</th>
                        <th class="px-5 py-3">Peminjam</th>
                        <th class="px-5 py-3">Tanggal Pinjam</th>
                        <th class="px-5 py-3">Rincian Alat &amp; Sisa Stok</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB] text-sm">
                    @forelse ($peminjamans as $pinjam)
                        @php
                            $badgeClass = match ($pinjam->status) {
                                'menunggu' => 'bg-[#FFFBEB] text-[#D97706]',
                                'disetujui' => 'bg-[#EFF6FF] text-[#2563EB]',
                                'selesai' => 'bg-[#F0FDF4] text-[#16A34A]',
                                default => 'bg-[#FEF2F2] text-[#DC2626]',
                            };

                            $badgeLabel = match ($pinjam->status) {
                                'menunggu' => 'Menunggu',
                                'disetujui' => 'Disetujui',
                                'selesai' => 'Selesai',
                                default => 'Ditolak',
                            };

                            $isMenunggu = $pinjam->status === 'menunggu';
                            $adaStokKurang = false;
                            if ($isMenunggu) {
                                foreach ($pinjam->detailPeminjaman as $d) {
                                    if ($d->jumlah_pinjam > (int) ($d->alat?->jumlah_tersedia ?? 0)) {
                                        $adaStokKurang = true;
                                        break;
                                    }
                                }
                            }
                        @endphp
                        <tr class="lp-tr-row align-top">
                            <td class="px-5 py-4 font-bold text-[#1F2937] whitespace-nowrap">
                                #PMJ-{{ $pinjam->id_peminjaman }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="text-sm font-semibold text-[#1F2937]">{{ $pinjam->peminjam?->username ?? '-' }}</p>
                                <p class="text-[11px] text-[#6B7280] mt-0.5">
                                    {{ $pinjam->detailPeminjaman->count() }} jenis ({{ $pinjam->detailPeminjaman->sum('jumlah_pinjam') }} unit)
                                </p>
                            </td>
                            <td class="px-5 py-4 text-xs text-[#6B7280] whitespace-nowrap">
                                {{ $pinjam->tanggal_pinjam?->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-4 min-w-[280px]">
                                <ul class="space-y-1.5">
                                    @foreach ($pinjam->detailPeminjaman as $detail)
                                        @php
                                            $stokTersedia = (int) ($detail->alat?->jumlah_tersedia ?? 0);
                                            $melebihiStok = $detail->jumlah_pinjam > $stokTersedia;
                                        @endphp
                                        <li class="text-xs flex flex-wrap items-center justify-between gap-x-3 gap-y-0.5 pb-1.5 last:pb-0 border-b border-[#E5E7EB]/60 last:border-b-0">
                                            <span class="font-medium text-[#1F2937]">
                                                {{ $detail->alat?->nama_alat ?? 'Alat tidak ditemukan' }}
                                            </span>
                                            <span class="whitespace-nowrap">
                                                <span class="font-semibold {{ ($isMenunggu && $melebihiStok) ? 'text-[#DC2626]' : 'text-[#1F2937]' }}">
                                                    Pinjam {{ $detail->jumlah_pinjam }} unit
                                                </span>
                                                <span class="mx-1 text-[#6B7280]">/</span>
                                                @if ($isMenunggu && $melebihiStok)
                                                    <span class="font-semibold text-[#DC2626]">
                                                        Stok {{ $stokTersedia }} unit (stok kurang)
                                                    </span>
                                                @else
                                                    <span class="{{ $melebihiStok ? 'text-[#DC2626] font-medium' : 'text-[#6B7280]' }}">
                                                        Stok saat ini: {{ $stokTersedia }} unit
                                                    </span>
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>

                                @if ($adaStokKurang)
                                    <p class="mt-2 text-[11px] font-semibold text-[#DC2626]">
                                        Sebagian alat melebihi stok tersedia saat ini.
                                    </p>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                    {{ $badgeLabel }}
                                </span>
                                @if ($pinjam->petugas)
                                    <p class="text-[11px] text-[#6B7280] mt-1">
                                        Petugas: {{ $pinjam->petugas->username }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                @if ($isMenunggu)
                                    <div class="inline-flex items-center justify-end gap-2">
                                        <form
                                            method="POST"
                                            action="{{ route('petugas.persetujuan.setujui', $pinjam) }}"
                                            data-confirm="Setujui peminjaman ini?"
                                            data-confirm-kind="approve"
                                            data-confirm-target="#PMJ-{{ $pinjam->id_peminjaman }}"
                                            data-confirm-message="Stok alat laboratorium yang dipinjam akan dikurangi otomatis."
                                            data-confirm-ok="Ya, Setujui"
                                        >
                                            @csrf
                                            <button
                                                type="submit"
                                                class="lp-btn-red h-8 px-4 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold"
                                            >
                                                Setujui
                                            </button>
                                        </form>

                                        <form
                                            method="POST"
                                            action="{{ route('petugas.persetujuan.tolak', $pinjam) }}"
                                            data-confirm="Tolak pengajuan ini?"
                                            data-confirm-kind="reject"
                                            data-confirm-target="#PMJ-{{ $pinjam->id_peminjaman }}"
                                            data-confirm-message="Pengajuan peminjaman ini akan ditandai ditolak tanpa mengubah stok."
                                            data-confirm-ok="Ya, Tolak"
                                        >
                                            @csrf
                                            <button
                                                type="submit"
                                                class="h-8 px-3.5 rounded-full border border-[#E5E7EB] bg-white hover:border-[#DC2626] hover:text-[#DC2626] text-xs font-semibold text-[#1F2937] transition"
                                            >
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-[#6B7280]">Sudah diproses</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-4">
                                @php
                                    $persetujuanDesc = $q !== ''
                                        ? 'Tidak ditemukan peminjaman dengan kata kunci "' . $q . '".'
                                        : 'Belum ada pengajuan peminjaman berstatus ' . strtolower($tabs[$status] ?? $status) . '.';
                                @endphp
                                <x-empty-state
                                    :icon="$q !== '' ? 'search' : 'clipboard'"
                                    title="Tidak ada data peminjaman pada filter ini."
                                    :description="$persetujuanDesc"
                                    :primary-label="$status !== 'semua' ? 'Lihat Semua Status' : null"
                                    :primary-href="$status !== 'semua' ? route('petugas.persetujuan.index', ['status' => 'semua']) : null"
                                    :secondary-label="$q !== '' ? 'Reset Pencarian' : null"
                                    :secondary-href="$q !== '' ? route('petugas.persetujuan.index', ['status' => $status]) : null"
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
@endsection
