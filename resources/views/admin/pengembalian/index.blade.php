@extends('layouts.app')

@section('title', 'Data Pengembalian')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-[#1F2937]">Data Pengembalian</h1>
            <p class="text-sm text-[#6B7280] mt-0.5">Kelola catatan pengembalian alat, denda, dan sinkronisasi status peminjaman.</p>
        </div>
        <a
            href="{{ route('admin.pengembalian.create') }}"
            class="lp-btn-red inline-flex items-center justify-center h-9 px-5 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold self-start sm:self-auto"
        >
            + Tambah Pengembalian
        </a>
    </div>

    {{-- Filter Pencarian & Rentang Tanggal --}}
    <section class="lp-panel-lg bg-white border border-[#E5E7EB] rounded-xl p-4 mb-6">
        <form method="GET" action="{{ route('admin.pengembalian.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="lg:col-span-4">
                <label for="q" class="block text-xs font-semibold text-[#6B7280] mb-1">Cari Peminjam / Kode</label>
                <input
                    id="q"
                    type="search"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Username atau #PMJ-1..."
                    class="w-full h-9 px-3 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] text-xs text-[#1F2937] placeholder-[#6B7280] focus:bg-white focus:border-[#E53935] focus:outline-none transition"
                >
            </div>

            <div class="lg:col-span-3">
                <label for="tanggal_mulai" class="block text-xs font-semibold text-[#6B7280] mb-1">Tgl Kembali Mulai</label>
                <input
                    id="tanggal_mulai"
                    type="date"
                    name="tanggal_mulai"
                    value="{{ $tanggalMulai }}"
                    class="w-full h-9 px-3 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] text-xs text-[#1F2937] focus:bg-white focus:border-[#E53935] focus:outline-none transition"
                >
            </div>

            <div class="lg:col-span-3">
                <label for="tanggal_selesai" class="block text-xs font-semibold text-[#6B7280] mb-1">Tgl Kembali Selesai</label>
                <input
                    id="tanggal_selesai"
                    type="date"
                    name="tanggal_selesai"
                    value="{{ $tanggalSelesai }}"
                    class="w-full h-9 px-3 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] text-xs text-[#1F2937] focus:bg-white focus:border-[#E53935] focus:outline-none transition"
                >
            </div>

            <div class="lg:col-span-2 flex items-center gap-2">
                <button
                    type="submit"
                    class="flex-1 h-9 px-4 rounded-lg bg-[#1F2937] hover:bg-[#111827] text-white text-xs font-semibold transition"
                >
                    Filter
                </button>
                @if ($q !== '' || $tanggalMulai || $tanggalSelesai)
                    <a
                        href="{{ route('admin.pengembalian.index') }}"
                        class="h-9 px-3 rounded-lg border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-medium text-[#6B7280] hover:text-[#1F2937] inline-flex items-center justify-center transition"
                    >
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </section>

    {{-- Tabel Data Pengembalian --}}
    <section style="--d: 60ms;" class="lp-panel-lg bg-white border border-[#E5E7EB] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7F7F8] border-b border-[#E5E7EB] text-[11px] font-semibold uppercase tracking-wider text-[#6B7280]">
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Peminjam</th>
                        <th class="px-4 py-3">Daftar Alat</th>
                        <th class="px-4 py-3">Tgl Pinjam</th>
                        <th class="px-4 py-3">Tgl Kembali</th>
                        <th class="px-4 py-3">Petugas Penerima</th>
                        <th class="px-4 py-3 text-right">Denda</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB] text-sm">
                    @forelse ($pengembalians as $item)
                        @php
                            $pinjam = $item->peminjaman;
                            $dendaNominal = (int) $item->denda;
                            $belumVerifikasi = is_null($item->id_petugas) && $pinjam?->status === 'disetujui';
                        @endphp
                        <tr class="lp-tr-row align-top">
                            <td class="px-4 py-4 font-bold text-[#1F2937] whitespace-nowrap">
                                #PMJ-{{ $item->id_peminjaman }}
                            </td>
                            <td class="px-4 py-4 font-semibold text-[#1F2937] whitespace-nowrap">
                                {{ $pinjam?->peminjam?->username ?? '-' }}
                            </td>
                            <td class="px-4 py-4 min-w-[210px]">
                                @if ($pinjam && $pinjam->detailPeminjaman->isNotEmpty())
                                    <ul class="space-y-0.5">
                                        @foreach ($pinjam->detailPeminjaman as $detail)
                                            <li class="text-xs text-[#1F2937]">
                                                <span class="font-medium">{{ $detail->alat?->nama_alat ?? 'Alat tidak ditemukan' }}</span>
                                                <span class="text-[#6B7280]">({{ $detail->jumlah_pinjam }} unit)</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-xs text-[#6B7280]">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-xs text-[#6B7280] whitespace-nowrap">
                                {{ $pinjam?->tanggal_pinjam?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-4 text-xs font-medium text-[#1F2937] whitespace-nowrap">
                                {{ $item->tanggal_kembali?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if ($belumVerifikasi)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#FFFBEB] text-[#D97706]">
                                        Menunggu Verifikasi
                                    </span>
                                @else
                                    <span class="text-xs font-medium text-[#1F2937]">
                                        {{ $item->petugas?->username ?? '-' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-xs text-right font-semibold whitespace-nowrap {{ $dendaNominal > 0 ? 'text-[#DC2626]' : 'text-[#16A34A]' }}">
                                Rp {{ number_format($dendaNominal, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('admin.pengembalian.edit', $item) }}"
                                        class="inline-flex items-center h-8 px-3.5 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.pengembalian.destroy', $item) }}"
                                        data-confirm="Hapus data pengembalian?"
                                        data-confirm-kind="delete"
                                        data-confirm-target="#PMJ-{{ $item->id_peminjaman }}"
                                        data-confirm-message="Status peminjaman akan dikembalikan ke disetujui dan stok alat disesuaikan kembali."
                                        data-confirm-ok="Ya, Hapus"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="h-8 px-3.5 rounded-full border border-[#E5E7EB] bg-white hover:border-[#DC2626] hover:text-[#DC2626] text-xs font-semibold text-[#6B7280] transition"
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-4">
                                @php
                                    $hasPengembalianFilter = request()->filled('q') || request()->filled('tanggal_mulai') || request()->filled('tanggal_selesai');
                                @endphp
                                <x-empty-state
                                    :icon="$hasPengembalianFilter ? 'search' : 'clipboard'"
                                    title="Tidak ada data pengembalian ditemukan."
                                    description="Tambahkan pengembalian baru atau ubah filter pencarian dan tanggal."
                                    primary-label="Tambah Pengembalian"
                                    :primary-href="route('admin.pengembalian.create')"
                                    :secondary-label="$hasPengembalianFilter ? 'Reset Filter' : null"
                                    :secondary-href="$hasPengembalianFilter ? route('admin.pengembalian.index') : null"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($pengembalians->hasPages())
        <div class="mt-6">
            {{ $pengembalians->links() }}
        </div>
    @endif
@endsection
