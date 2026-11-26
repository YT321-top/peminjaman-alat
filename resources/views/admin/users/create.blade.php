@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <nav class="text-xs text-[#6B7280] mb-1">
                <a href="{{ route('admin.users.index') }}" class="hover:text-[#E53935] transition">Manajemen User</a>
                <span class="mx-1.5">/</span>
                <span class="text-[#1F2937] font-medium">Tambah User</span>
            </nav>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-bold text-[#1F2937] tracking-tight">Tambah User Baru</h1>
                <span class="px-2.5 py-0.5 rounded-full bg-[#E53935] text-white text-[11px] font-semibold">Data Baru</span>
            </div>
        </div>
        <a
            href="{{ route('admin.users.index') }}"
            class="inline-flex items-center gap-1.5 h-9 px-4 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition self-start sm:self-auto"
        >
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Card Form Utama --}}
        <div class="lg:col-span-8 lp-card-3d bg-white border border-[#E5E7EB] rounded-2xl overflow-hidden">
            <div class="h-1.5 w-full bg-[#E53935]"></div>

            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf

                <div class="p-6 space-y-5">
                    <div class="flex items-center gap-3 pb-4 border-b border-[#F3F4F6]">
                        <span class="w-10 h-10 rounded-xl bg-[#FEF2F2] text-[#E53935] flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-[#1F2937]">Kredensial &amp; Hak Akses</h2>
                            <p class="text-xs text-[#6B7280]">Akun pengguna memakai username dan password untuk masuk ke sistem.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="username" class="block text-xs font-semibold text-[#1F2937] mb-1.5">Username</label>
                            <input
                                id="username"
                                type="text"
                                name="username"
                                value="{{ old('username') }}"
                                required
                                maxlength="50"
                                placeholder="Contoh: siswa_andi"
                                class="w-full h-10 px-3.5 rounded-xl bg-[#F7F7F8] focus:bg-white border border-[#E5E7EB] text-sm text-[#1F2937] placeholder-[#6B7280] focus:border-[#E53935] focus:outline-none transition"
                            >
                            @error('username')
                                <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="role" class="block text-xs font-semibold text-[#1F2937] mb-1.5">Role Pengguna</label>
                            <select
                                id="role"
                                name="role"
                                required
                                class="w-full h-10 px-3.5 rounded-xl bg-white border border-[#E5E7EB] text-sm text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
                            >
                                <option value="">Pilih role</option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="petugas" {{ old('role') === 'petugas' ? 'selected' : '' }}>Petugas</option>
                                <option value="peminjam" {{ old('role') === 'peminjam' ? 'selected' : '' }}>Peminjam</option>
                            </select>
                            @error('role')
                                <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="password" class="block text-xs font-semibold text-[#1F2937] mb-1.5">Password</label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                minlength="6"
                                placeholder="Minimal 6 karakter"
                                class="w-full h-10 px-3.5 rounded-xl bg-[#F7F7F8] focus:bg-white border border-[#E5E7EB] text-sm text-[#1F2937] placeholder-[#6B7280] focus:border-[#E53935] focus:outline-none transition"
                            >
                            @error('password')
                                <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-[#F7F7F8] border-t border-[#E5E7EB] flex items-center justify-end gap-2.5">
                    <a
                        href="{{ route('admin.users.index') }}"
                        class="inline-flex items-center h-9 px-5 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="lp-btn-red h-9 px-6 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold transition"
                    >
                        Simpan User
                    </button>
                </div>
            </form>
        </div>

        {{-- Sidebar Preview Akun & Hak Akses --}}
        <aside class="lg:col-span-4 lp-card-3d bg-white border border-[#E5E7EB] rounded-2xl p-5 space-y-4">
            <span class="block text-[11px] font-bold uppercase tracking-wider text-[#6B7280]">Preview Akun</span>

            <div class="flex items-center gap-3 p-3.5 rounded-xl bg-[#F7F7F8] border border-[#E5E7EB]">
                <span id="pv-user-av" class="w-10 h-10 rounded-full text-sm font-bold text-white bg-[#6B7280] flex items-center justify-center shrink-0">
                    U
                </span>
                <div class="min-w-0">
                    <p id="pv-user-nm" class="text-sm font-bold text-[#1F2937] truncate">
                        {{ old('username') ?: 'username' }}
                    </p>
                    <span id="pv-user-rl" class="inline-block mt-0.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-white bg-[#6B7280]">
                        Belum dipilih
                    </span>
                </div>
            </div>

            <div class="pt-3 border-t border-[#F3F4F6] space-y-1.5">
                <p class="text-xs font-semibold text-[#1F2937]">Cakupan Hak Akses</p>
                <p id="pv-user-ds" class="text-xs text-[#6B7280] leading-relaxed">
                    Pilih role pengguna untuk melihat ringkasan hak akses di dalam sistem.
                </p>
            </div>
        </aside>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const inUser = document.getElementById('username');
            const inRole = document.getElementById('role');

            const pvAv = document.getElementById('pv-user-av');
            const pvNm = document.getElementById('pv-user-nm');
            const pvRl = document.getElementById('pv-user-rl');
            const pvDs = document.getElementById('pv-user-ds');

            const roleMap = {
                admin: {
                    label: 'Admin',
                    color: 'bg-[#E53935]',
                    desc: 'Mengelola data User, Kategori, Alat Lab, Data Peminjaman, Pengembalian, dan memantau Log Aktivitas sistem.',
                },
                petugas: {
                    label: 'Petugas',
                    color: 'bg-[#2563EB]',
                    desc: 'Menyetujui atau menolak pengajuan peminjaman, memverifikasi pengembalian & denda, serta mencetak laporan.',
                },
                peminjam: {
                    label: 'Peminjam',
                    color: 'bg-[#1F2937]',
                    desc: 'Mengakses katalog alat lab, mengajukan peminjaman multi-alat, dan mengajukan pengembalian alat.',
                },
            };

            function syncPreview() {
                const name = (inUser?.value || '').trim() || 'username';
                const role = inRole?.value || '';
                const meta = roleMap[role];

                if (pvNm) pvNm.textContent = name;
                if (pvAv) {
                    pvAv.textContent = name.charAt(0).toUpperCase();
                    pvAv.className = 'w-10 h-10 rounded-full text-sm font-bold text-white flex items-center justify-center shrink-0 ' + (meta ? meta.color : 'bg-[#6B7280]');
                }
                if (pvRl) {
                    pvRl.textContent = meta ? meta.label : 'Belum dipilih';
                    pvRl.className = 'inline-block mt-0.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-white ' + (meta ? meta.color : 'bg-[#6B7280]');
                }
                if (pvDs) {
                    pvDs.textContent = meta ? meta.desc : 'Pilih role pengguna untuk melihat ringkasan hak akses di dalam sistem.';
                }
            }

            if (inUser) inUser.addEventListener('input', syncPreview);
            if (inRole) inRole.addEventListener('change', syncPreview);
            syncPreview();
        })();
    </script>
@endpush
