@extends('layouts.app')

@section('title', 'Manajemen Alat Lab')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-[#1F2937]">Manajemen Alat Laboratorium</h1>
            <p class="text-sm text-[#6B7280] mt-0.5">Kelola daftar inventaris peralatan lab beserta kategori dan jumlah stok tersedia.</p>
        </div>
        <a
            href="{{ route('admin.alat.create') }}"
            class="lp-btn-red inline-flex items-center justify-center h-9 px-5 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold self-start sm:self-auto"
        >
            + Tambah Alat
        </a>
    </div>

    <div class="lp-panel-lg bg-white border border-[#E5E7EB] rounded-xl overflow-hidden">
        {{-- Bar Filter Kategori & Pencarian --}}
        <div class="px-5 py-3.5 border-b border-[#E5E7EB] flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <p class="text-xs font-medium text-[#6B7280]">
                Menampilkan <strong class="font-semibold text-[#1F2937]">{{ $alats->total() }}</strong> alat laboratorium
            </p>

            <form method="GET" action="{{ route('admin.alat.index') }}" class="flex flex-wrap items-center gap-2">
                <select
                    name="kategori"
                    onchange="this.form.submit()"
                    aria-label="Filter kategori"
                    class="w-full sm:w-48 shrink-0 h-9 px-3 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] text-xs font-medium text-[#1F2937] focus:bg-white focus:border-[#E53935] focus:outline-none transition"
                >
                    <option value="">Semua Kategori</option>
                    @foreach ($kategoris as $kat)
                        <option value="{{ $kat->id_kategori }}" {{ (string) $kategoriId === (string) $kat->id_kategori ? 'selected' : '' }}>
                            {{ $kat->nama_kategori }}
                        </option>
                    @endforeach
                </select>

                <div class="relative flex-1 sm:flex-initial">
                    <svg class="w-4 h-4 text-[#6B7280] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"/>
                        <line x1="20" y1="20" x2="16.2" y2="16.2"/>
                    </svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ $q }}"
                        placeholder="Cari nama alat..."
                        class="w-full sm:w-56 h-9 pl-9 pr-3 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] text-xs text-[#1F2937] placeholder-[#6B7280] focus:bg-white focus:border-[#E53935] focus:outline-none transition"
                    >
                </div>

                <button
                    type="submit"
                    class="h-9 px-4 rounded-lg bg-[#1F2937] hover:bg-[#111827] text-white text-xs font-semibold transition shrink-0"
                >
                    Cari
                </button>

                @if ($q !== '' || $kategoriId !== '')
                    <a
                        href="{{ route('admin.alat.index') }}"
                        class="h-9 px-3 rounded-lg border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-medium text-[#6B7280] hover:text-[#1F2937] inline-flex items-center transition shrink-0"
                    >
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Tabel Daftar Alat --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7F7F8] border-b border-[#E5E7EB] text-[11px] font-semibold uppercase tracking-wider text-[#6B7280]">
                        <th class="px-5 py-3 w-20">ID</th>
                        <th class="px-5 py-3">Alat Laboratorium</th>
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3">Stok Tersedia</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB] text-sm">
                    @forelse ($alats as $item)
                        @php
                            $stokHabis = $item->jumlah_tersedia === 0;
                            $stokMenipis = $item->jumlah_tersedia > 0 && $item->jumlah_tersedia <= 3;
                        @endphp
                        <tr class="lp-tr-row">
                            <td class="px-5 py-3.5 text-xs font-semibold text-[#6B7280] whitespace-nowrap">
                                #{{ $item->id_alat }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-11 h-11 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] flex items-center justify-center shrink-0">
                                        <x-alat-illustration
                                            :alat-id="$item->id_alat"
                                            :kategori="$item->kategori?->nama_kategori ?? ''"
                                            :nama-alat="$item->nama_alat"
                                            class="w-8 h-8"
                                        />
                                    </div>
                                    <div>
                                        <p class="font-semibold text-[#1F2937]">{{ $item->nama_alat }}</p>
                                        <p class="text-[11px] text-[#6B7280] mt-0.5">
                                            Riwayat pinjam: {{ number_format($item->detail_peminjaman_count) }} transaksi
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-[#6B7280] whitespace-nowrap">
                                {{ $item->kategori?->nama_kategori ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if ($stokHabis)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#FEF2F2] text-[#DC2626]">
                                        0 unit (Habis)
                                    </span>
                                @elseif ($stokMenipis)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#FFFBEB] text-[#D97706]">
                                        {{ number_format($item->jumlah_tersedia) }} unit
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#F0FDF4] text-[#16A34A]">
                                        {{ number_format($item->jumlah_tersedia) }} unit
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('admin.alat.edit', $item) }}"
                                        class="inline-flex items-center h-8 px-3.5 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.alat.destroy', $item) }}"
                                        data-confirm="Yakin ingin menghapus alat?"
                                        data-confirm-kind="delete"
                                        data-confirm-target="{{ $item->nama_alat }}"
                                        data-confirm-message="Data alat yang dihapus tidak dapat dikembalikan."
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
                            <td colspan="5" class="px-5 py-4">
                                <x-empty-state
                                    :icon="($q !== '' || !empty($kategoriId)) ? 'search' : 'box'"
                                    title="Tidak ada data alat laboratorium yang sesuai filter."
                                    description="Tambahkan alat baru atau ubah filter kategori dan pencarian."
                                    primary-label="Tambah Alat"
                                    :primary-href="route('admin.alat.create')"
                                    :secondary-label="($q !== '' || !empty($kategoriId)) ? 'Reset Filter' : null"
                                    :secondary-href="($q !== '' || !empty($kategoriId)) ? route('admin.alat.index') : null"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($alats->hasPages())
        <div class="mt-6">
            {{ $alats->links() }}
        </div>
    @endif
@endsection
