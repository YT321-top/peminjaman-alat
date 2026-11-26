@extends('layouts.app')

@section('title', 'Log Aktivitas')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-[#1F2937]">Log Aktivitas Sistem</h1>
            <p class="text-sm text-[#6B7280] mt-0.5">Rekam jejak otomatis seluruh aktivitas login, logout, pengelolaan master, dan transaksi (read-only).</p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full bg-white border border-[#E5E7EB] text-xs font-semibold text-[#6B7280] self-start sm:self-auto">
            Total: {{ number_format($logs->total()) }} catatan
        </span>
    </div>

    {{-- Filter Log Aktivitas --}}
    <section class="lp-panel-lg bg-white border border-[#E5E7EB] rounded-xl p-4 mb-6">
        <form method="GET" action="{{ route('admin.log-aktivitas.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="lg:col-span-2">
                <label for="id_user" class="block text-xs font-semibold text-[#6B7280] mb-1">Pengguna</label>
                <select
                    id="id_user"
                    name="id_user"
                    class="w-full h-9 px-3 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] text-xs font-medium text-[#1F2937] focus:bg-white focus:border-[#E53935] focus:outline-none transition"
                >
                    <option value="">Semua User</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id_user }}" {{ (string) $userId === (string) $u->id_user ? 'selected' : '' }}>
                            {{ $u->username }} ({{ $u->role }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="role" class="block text-xs font-semibold text-[#6B7280] mb-1">Role</label>
                <select
                    id="role"
                    name="role"
                    class="w-full h-9 px-3 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] text-xs font-medium text-[#1F2937] focus:bg-white focus:border-[#E53935] focus:outline-none transition"
                >
                    <option value="semua" {{ $role === 'semua' ? 'selected' : '' }}>Semua Role</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="petugas" {{ $role === 'petugas' ? 'selected' : '' }}>Petugas</option>
                    <option value="peminjam" {{ $role === 'peminjam' ? 'selected' : '' }}>Peminjam</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="tanggal_mulai" class="block text-xs font-semibold text-[#6B7280] mb-1">Tanggal Mulai</label>
                <input
                    id="tanggal_mulai"
                    type="date"
                    name="tanggal_mulai"
                    value="{{ $tanggalMulai }}"
                    class="w-full h-9 px-3 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] text-xs text-[#1F2937] focus:bg-white focus:border-[#E53935] focus:outline-none transition"
                >
            </div>

            <div class="lg:col-span-2">
                <label for="tanggal_selesai" class="block text-xs font-semibold text-[#6B7280] mb-1">Tanggal Selesai</label>
                <input
                    id="tanggal_selesai"
                    type="date"
                    name="tanggal_selesai"
                    value="{{ $tanggalSelesai }}"
                    class="w-full h-9 px-3 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] text-xs text-[#1F2937] focus:bg-white focus:border-[#E53935] focus:outline-none transition"
                >
            </div>

            <div class="lg:col-span-2">
                <label for="q" class="block text-xs font-semibold text-[#6B7280] mb-1">Cari Teks Aksi</label>
                <input
                    id="q"
                    type="search"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Contoh: Login, #PMJ..."
                    class="w-full h-9 px-3 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] text-xs text-[#1F2937] placeholder-[#6B7280] focus:bg-white focus:border-[#E53935] focus:outline-none transition"
                >
            </div>

            <div class="lg:col-span-2 flex items-center gap-2">
                <button
                    type="submit"
                    class="flex-1 h-9 px-4 rounded-lg bg-[#1F2937] hover:bg-[#111827] text-white text-xs font-semibold transition"
                >
                    Filter
                </button>
                @if ($userId !== '' || $role !== 'semua' || $tanggalMulai || $tanggalSelesai || $q !== '')
                    <a
                        href="{{ route('admin.log-aktivitas.index') }}"
                        class="h-9 px-3 rounded-lg border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-medium text-[#6B7280] hover:text-[#1F2937] inline-flex items-center justify-center transition"
                    >
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </section>

    {{-- Tabel Log Aktivitas (Read-Only) --}}
    <section style="--d: 60ms;" class="lp-panel-lg bg-white border border-[#E5E7EB] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7F7F8] border-b border-[#E5E7EB] text-[11px] font-semibold uppercase tracking-wider text-[#6B7280]">
                        <th class="px-5 py-3">Waktu</th>
                        <th class="px-5 py-3">Username</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB] text-sm">
                    @forelse ($logs as $log)
                        @php
                            $logRole = $log->user?->role;
                            $roleBadgeClass = match ($logRole) {
                                'admin' => 'bg-[#FEF2F2] text-[#DC2626]',
                                'petugas' => 'bg-[#EFF6FF] text-[#2563EB]',
                                'peminjam' => 'bg-[#F0FDF4] text-[#16A34A]',
                                default => 'bg-[#F7F7F8] text-[#6B7280]',
                            };
                            $roleText = match ($logRole) {
                                'admin' => 'Admin',
                                'petugas' => 'Petugas',
                                'peminjam' => 'Peminjam',
                                default => '-',
                            };
                        @endphp
                        <tr class="lp-tr-row">
                            <td class="px-5 py-3.5 text-xs text-[#6B7280] whitespace-nowrap">
                                {{ $log->waktu?->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-[#1F2937] whitespace-nowrap">
                                {{ $log->user?->username ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $roleBadgeClass }}">
                                    {{ $roleText }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-[#1F2937]">
                                {{ $log->aksi }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-4">
                                <x-empty-state
                                    icon="search"
                                    title="Tidak ada catatan log aktivitas yang sesuai filter."
                                    description="Ubah rentang tanggal, user, role, atau kata kunci pencarian."
                                    secondary-label="Reset Filter"
                                    :secondary-href="route('admin.log-aktivitas.index')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($logs->hasPages())
        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    @endif
@endsection
