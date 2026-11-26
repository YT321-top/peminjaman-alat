@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-[#1F2937]">Dashboard Admin</h1>
            <p class="text-sm text-[#6B7280] mt-0.5">Ringkasan inventaris alat laboratorium, transaksi, dan log aktivitas sistem.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('admin.alat.create') }}"
                class="lp-btn-red inline-flex items-center h-9 px-4 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold transition"
            >
                + Tambah Alat
            </a>
            <a
                href="{{ route('admin.peminjaman.index') }}"
                class="inline-flex items-center h-9 px-4 rounded-full border border-[#E5E7EB] bg-white hover:border-[#E53935] hover:text-[#E53935] text-xs font-semibold text-[#1F2937] transition"
            >
                Data Peminjaman
            </a>
            <a
                href="{{ route('admin.users.index') }}"
                class="inline-flex items-center h-9 px-4 rounded-full border border-[#E5E7EB] bg-white hover:border-[#E53935] hover:text-[#E53935] text-xs font-semibold text-[#1F2937] transition"
            >
                Kelola User
            </a>
        </div>
    </div>

    {{-- 4 Quick Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <a
            href="{{ route('admin.users.index') }}"
            style="--d: 0ms;"
            class="lp-card bg-white border border-[#E5E7EB] rounded-xl p-5 flex items-center justify-between"
        >
            <div>
                <p class="text-xs font-medium text-[#6B7280]">Total User</p>
                <p class="text-2xl font-bold text-[#1F2937] mt-1">{{ number_format($stats['total_user']) }}</p>
            </div>
            <div class="lp-icon-box w-10 h-10 rounded-lg bg-[#FEF2F2] text-[#E53935] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
        </a>

        <a
            href="{{ route('admin.alat.index') }}"
            style="--d: 55ms;"
            class="lp-card bg-white border border-[#E5E7EB] rounded-xl p-5 flex items-center justify-between"
        >
            <div>
                <p class="text-xs font-medium text-[#6B7280]">Total Alat</p>
                <p class="text-2xl font-bold text-[#1F2937] mt-1">{{ number_format($stats['total_alat']) }}</p>
            </div>
            <div class="lp-icon-box w-10 h-10 rounded-lg bg-[#FEF2F2] text-[#E53935] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </div>
        </a>

        <a
            href="{{ route('admin.peminjaman.index', ['status' => 'disetujui']) }}"
            style="--d: 110ms;"
            class="lp-card bg-white border border-[#E5E7EB] rounded-xl p-5 flex items-center justify-between"
        >
            <div>
                <p class="text-xs font-medium text-[#6B7280]">Sedang Dipinjam</p>
                <p class="text-2xl font-bold text-[#1F2937] mt-1">{{ number_format($stats['sedang_dipinjam']) }}</p>
            </div>
            <div class="lp-icon-box w-10 h-10 rounded-lg bg-[#FEF2F2] text-[#E53935] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                    <rect x="9" y="3" width="6" height="4" rx="1"/>
                </svg>
            </div>
        </a>

        <a
            href="{{ route('admin.peminjaman.index', ['status' => 'menunggu']) }}"
            style="--d: 165ms;"
            class="lp-card bg-white border border-[#E5E7EB] rounded-xl p-5 flex items-center justify-between"
        >
            <div>
                <p class="text-xs font-medium text-[#6B7280]">Menunggu Persetujuan</p>
                <p class="text-2xl font-bold text-[#1F2937] mt-1">{{ number_format($stats['menunggu_persetujuan']) }}</p>
            </div>
            <div class="lp-icon-box w-10 h-10 rounded-lg bg-[#FEF2F2] text-[#E53935] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/>
                    <polyline points="12 7 12 12 15 14"/>
                </svg>
            </div>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Log Aktivitas Terbaru --}}
        <section style="--d: 200ms;" class="lp-panel-lg lg:col-span-2 bg-white border border-[#E5E7EB] rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-[#E5E7EB] flex items-center justify-between">
                <h2 class="text-sm font-bold text-[#1F2937]">Log Aktivitas Terbaru</h2>
                <a href="{{ route('admin.log-aktivitas.index') }}" class="text-xs font-semibold text-[#E53935] hover:text-[#C62828] transition">
                    Lihat Semua
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F7F7F8] border-b border-[#E5E7EB] text-[11px] font-semibold uppercase tracking-wider text-[#6B7280]">
                            <th class="px-5 py-3">Waktu</th>
                            <th class="px-5 py-3">Pengguna</th>
                            <th class="px-5 py-3">Aktivitas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB] text-sm">
                        @forelse ($logTerbaru as $log)
                            <tr class="lp-tr-row">
                                <td class="px-5 py-3 text-xs text-[#6B7280] whitespace-nowrap">
                                    {{ $log->waktu?->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <span class="font-semibold text-[#1F2937]">{{ $log->user?->username ?? '-' }}</span>
                                    @if ($log->user)
                                        <span class="ml-1 text-[11px] uppercase text-[#6B7280]">({{ $log->user->role }})</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-[#1F2937]">
                                    {{ $log->aksi }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-center text-sm text-[#6B7280]">
                                    Belum ada catatan aktivitas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Alat Stok Menipis --}}
        <section style="--d: 255ms;" class="lp-panel-lg bg-white border border-[#E5E7EB] rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-[#E5E7EB] flex items-center justify-between">
                <h2 class="text-sm font-bold text-[#1F2937]">Alat Stok Menipis</h2>
                <a href="{{ route('admin.alat.index') }}" class="text-xs font-semibold text-[#E53935] hover:text-[#C62828] transition">
                    Kelola
                </a>
            </div>

            <div class="divide-y divide-[#E5E7EB] p-2 space-y-1">
                @forelse ($alatStokMenipis as $alat)
                    <a
                        href="{{ route('admin.alat.edit', $alat) }}"
                        class="lp-sub-item px-3.5 py-2.5 rounded-lg border border-transparent flex items-center justify-between gap-3"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] flex items-center justify-center shrink-0">
                                <x-alat-illustration
                                    :alat-id="$alat->id_alat"
                                    :kategori="$alat->kategori?->nama_kategori ?? ''"
                                    :nama-alat="$alat->nama_alat"
                                    class="lp-mini-svg w-7 h-7"
                                />
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-[#1F2937] truncate">{{ $alat->nama_alat }}</p>
                                <p class="text-xs text-[#6B7280] truncate">{{ $alat->kategori?->nama_kategori ?? '-' }}</p>
                            </div>
                        </div>
                        <span class="shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $alat->jumlah_tersedia === 0 ? 'bg-[#FEF2F2] text-[#DC2626]' : 'bg-[#FFFBEB] text-[#D97706]' }}">
                            {{ $alat->jumlah_tersedia }} unit
                        </span>
                    </a>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-[#6B7280]">
                        Seluruh stok alat berada pada batas aman.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
