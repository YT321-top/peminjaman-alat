@extends('layouts.app')

@section('title', 'Manajemen Kategori')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-[#1F2937]">Manajemen Kategori</h1>
            <p class="text-sm text-[#6B7280] mt-0.5">Kelola pengelompokan kategori peralatan laboratorium sekolah.</p>
        </div>
        <a
            href="{{ route('admin.kategori.create') }}"
            class="lp-btn-red inline-flex items-center justify-center h-9 px-5 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold self-start sm:self-auto"
        >
            + Tambah Kategori
        </a>
    </div>

    <div class="lp-panel-lg bg-white border border-[#E5E7EB] rounded-xl overflow-hidden">
        {{-- Bar Pencarian --}}
        <div class="px-5 py-3.5 border-b border-[#E5E7EB] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <p class="text-xs font-medium text-[#6B7280]">
                Total: <strong class="font-semibold text-[#1F2937]">{{ $kategoris->total() }}</strong> kategori
            </p>

            <form method="GET" action="{{ route('admin.kategori.index') }}" class="flex items-center gap-2">
                <div class="relative flex-1 sm:flex-initial">
                    <svg class="w-4 h-4 text-[#6B7280] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"/>
                        <line x1="20" y1="20" x2="16.2" y2="16.2"/>
                    </svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ $q }}"
                        placeholder="Cari nama kategori..."
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
                        href="{{ route('admin.kategori.index') }}"
                        class="h-9 px-3 rounded-lg border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-medium text-[#6B7280] hover:text-[#1F2937] inline-flex items-center transition shrink-0"
                    >
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Tabel Kategori --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7F7F8] border-b border-[#E5E7EB] text-[11px] font-semibold uppercase tracking-wider text-[#6B7280]">
                        <th class="px-5 py-3 w-20">ID</th>
                        <th class="px-5 py-3">Nama Kategori</th>
                        <th class="px-5 py-3">Jumlah Alat</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB] text-sm">
                    @forelse ($kategoris as $item)
                        <tr class="lp-tr-row">
                            <td class="px-5 py-3.5 text-xs font-semibold text-[#6B7280] whitespace-nowrap">
                                #{{ $item->id_kategori }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] flex items-center justify-center shrink-0 overflow-hidden p-0.5">
                                        <x-alat-illustration :kategori-id="$item->id_kategori" :kategori="$item->nama_kategori" class="w-6 h-6" />
                                    </div>
                                    <span class="font-semibold text-[#1F2937]">{{ $item->nama_kategori }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-[#1F2937] whitespace-nowrap">
                                <a
                                    href="{{ route('admin.alat.index', ['kategori' => $item->id_kategori]) }}"
                                    class="font-semibold hover:text-[#E53935] transition"
                                >
                                    {{ number_format($item->alat_count) }} alat
                                </a>
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('admin.kategori.edit', $item) }}"
                                        class="inline-flex items-center h-8 px-3.5 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.kategori.destroy', $item) }}"
                                        data-confirm="Yakin ingin menghapus kategori?"
                                        data-confirm-kind="delete"
                                        data-confirm-target="{{ $item->nama_kategori }}"
                                        data-confirm-message="Data kategori yang dihapus tidak dapat dikembalikan."
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
                            <td colspan="4" class="px-5 py-4">
                                <x-empty-state
                                    :icon="$q !== '' ? 'search' : 'box'"
                                    title="Tidak ada data kategori ditemukan."
                                    description="Tambahkan kategori baru atau ubah kata kunci pencarian."
                                    primary-label="Tambah Kategori"
                                    :primary-href="route('admin.kategori.create')"
                                    :secondary-label="$q !== '' ? 'Reset Pencarian' : null"
                                    :secondary-href="$q !== '' ? route('admin.kategori.index') : null"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($kategoris->hasPages())
        <div class="mt-6">
            {{ $kategoris->links() }}
        </div>
    @endif
@endsection
