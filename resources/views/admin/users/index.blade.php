@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-[#1F2937]">Manajemen User</h1>
            <p class="text-sm text-[#6B7280] mt-0.5">Kelola akun Admin, Petugas laboratorium, dan Peminjam.</p>
        </div>
        <a
            href="{{ route('admin.users.create') }}"
            class="lp-btn-red inline-flex items-center justify-center h-9 px-5 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold self-start sm:self-auto"
        >
            + Tambah User
        </a>
    </div>

    @php
        $roleTabs = [
            'semua' => 'Semua',
            'admin' => 'Admin',
            'petugas' => 'Petugas',
            'peminjam' => 'Peminjam',
        ];
    @endphp

    <div class="lp-panel-lg bg-white border border-[#E5E7EB] rounded-xl overflow-hidden">
        {{-- Tab Role & Pencarian --}}
        <div class="px-4 border-b border-[#E5E7EB] flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <div class="flex items-center gap-6 overflow-x-auto">
                @foreach ($roleTabs as $key => $label)
                    @php
                        $isActive = $role === $key;
                        $params = array_filter([
                            'role' => $key !== 'semua' ? $key : null,
                            'q' => $q !== '' ? $q : null,
                        ], fn ($v) => $v !== null);
                    @endphp
                    <a
                        href="{{ route('admin.users.index', $params) }}"
                        class="py-3.5 text-sm whitespace-nowrap border-b-2 transition flex items-center gap-2 {{ $isActive ? 'border-[#E53935] text-[#1F2937] font-semibold' : 'border-transparent text-[#6B7280] hover:text-[#1F2937] font-medium' }}"
                    >
                        <span>{{ $label }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $isActive ? 'bg-[#FEF2F2] text-[#E53935]' : 'bg-[#F7F7F8] text-[#6B7280]' }}">
                            {{ $counts[$key] ?? 0 }}
                        </span>
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2 py-3 lg:py-2">
                @if ($role !== 'semua')
                    <input type="hidden" name="role" value="{{ $role }}">
                @endif
                <div class="relative flex-1 sm:flex-initial">
                    <svg class="w-4 h-4 text-[#6B7280] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"/>
                        <line x1="20" y1="20" x2="16.2" y2="16.2"/>
                    </svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ $q }}"
                        placeholder="Cari username..."
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
                        href="{{ route('admin.users.index', $role !== 'semua' ? ['role' => $role] : []) }}"
                        class="h-9 px-3 rounded-lg border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-medium text-[#6B7280] hover:text-[#1F2937] inline-flex items-center transition shrink-0"
                    >
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Tabel User --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7F7F8] border-b border-[#E5E7EB] text-[11px] font-semibold uppercase tracking-wider text-[#6B7280]">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Username</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Jumlah Peminjaman</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB] text-sm">
                    @forelse ($users as $item)
                        @php
                            $isSelf = $item->id_user === auth()->id();
                            $roleBadgeClass = match ($item->role) {
                                'admin' => 'bg-[#FEF2F2] text-[#DC2626]',
                                'petugas' => 'bg-[#EFF6FF] text-[#2563EB]',
                                default => 'bg-[#F0FDF4] text-[#16A34A]',
                            };
                            $roleText = match ($item->role) {
                                'admin' => 'Admin',
                                'petugas' => 'Petugas',
                                default => 'Peminjam',
                            };
                        @endphp
                        <tr class="lp-tr-row">
                            <td class="px-5 py-3.5 text-xs font-semibold text-[#6B7280] whitespace-nowrap">
                                #{{ $item->id_user }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="font-semibold text-[#1F2937]">{{ $item->username }}</span>
                                @if ($isSelf)
                                    <span class="ml-1.5 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#F7F7F8] border border-[#E5E7EB] text-[#6B7280]">
                                        Akun Anda
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $roleBadgeClass }}">
                                    {{ $roleText }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-[#1F2937] whitespace-nowrap">
                                {{ number_format($item->peminjaman_count) }} transaksi
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('admin.users.edit', $item) }}"
                                        class="inline-flex items-center h-8 px-3.5 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition"
                                    >
                                        Edit
                                    </a>

                                    @if (!$isSelf)
                                        <form
                                            method="POST"
                                            action="{{ route('admin.users.destroy', $item) }}"
                                            data-confirm="Yakin ingin menghapus pengguna?"
                                            data-confirm-kind="delete"
                                            data-confirm-target="{{ $item->username }}"
                                            data-confirm-message="Tindakan ini permanen dan menghapus akses akun pengguna ini."
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
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-4">
                                <x-empty-state
                                    :icon="($q !== '' || $role !== '') ? 'search' : 'clipboard'"
                                    title="Tidak ada data user yang sesuai filter."
                                    description="Coba ubah kata kunci pencarian atau filter role."
                                    primary-label="Tambah User"
                                    :primary-href="route('admin.users.create')"
                                    :secondary-label="($q !== '' || $role !== '') ? 'Reset Filter' : null"
                                    :secondary-href="($q !== '' || $role !== '') ? route('admin.users.index') : null"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($users->hasPages())
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    @endif
@endsection
