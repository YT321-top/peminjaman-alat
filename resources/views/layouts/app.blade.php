<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — LabPinjam</title>
    <script>document.documentElement.classList.add('lp-js');</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    </noscript>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F7F7F8] text-[#1F2937] antialiased">
@php
    $user = auth()->user();
    $role = $user?->role;

    $daftarPinjam = session('daftar_pinjam', []);
    $jumlahDaftarPinjam = is_array($daftarPinjam) ? array_sum(array_map('intval', $daftarPinjam)) : 0;
    $homeRoute = match ($role) {
        'peminjam' => route('peminjam.dashboard'),
        'petugas' => route('petugas.dashboard'),
        'admin' => route('admin.dashboard'),
        default => route('dashboard'),
    };

    if ($role === 'admin') {
        $navItems = [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => request()->routeIs('dashboard', 'admin.dashboard'), 'icon' => 'dashboard'],
            ['label' => 'User', 'route' => 'admin.users.index', 'active' => request()->routeIs('admin.users.*'), 'icon' => 'users'],
            ['label' => 'Kategori', 'route' => 'admin.kategori.index', 'active' => request()->routeIs('admin.kategori.*'), 'icon' => 'tag'],
            ['label' => 'Alat', 'route' => 'admin.alat.index', 'active' => request()->routeIs('admin.alat.*'), 'icon' => 'box'],
            ['label' => 'Peminjaman', 'route' => 'admin.peminjaman.index', 'active' => request()->routeIs('admin.peminjaman.*'), 'icon' => 'clipboard'],
            ['label' => 'Pengembalian', 'route' => 'admin.pengembalian.index', 'active' => request()->routeIs('admin.pengembalian.*'), 'icon' => 'return'],
            ['label' => 'Log Aktivitas', 'route' => 'admin.log-aktivitas.index', 'active' => request()->routeIs('admin.log-aktivitas.*'), 'icon' => 'clock'],
        ];
        $searchAction = match (true) {
            request()->routeIs('admin.users.*') => route('admin.users.index'),
            request()->routeIs('admin.kategori.*') => route('admin.kategori.index'),
            request()->routeIs('admin.peminjaman.*') => route('admin.peminjaman.index'),
            request()->routeIs('admin.pengembalian.*') => route('admin.pengembalian.index'),
            request()->routeIs('admin.log-aktivitas.*') => route('admin.log-aktivitas.index'),
            default => route('admin.alat.index'),
        };
        $searchPlaceholder = match (true) {
            request()->routeIs('admin.users.*') => 'Cari username pengguna...',
            request()->routeIs('admin.kategori.*') => 'Cari nama kategori laboratorium...',
            request()->routeIs('admin.peminjaman.*') => 'Cari kode #PMJ atau username peminjam...',
            request()->routeIs('admin.pengembalian.*') => 'Cari kode #PMJ atau username peminjam...',
            request()->routeIs('admin.log-aktivitas.*') => 'Cari teks aktivitas atau username...',
            default => 'Cari alat laboratorium atau inventaris...',
        };
    } elseif ($role === 'petugas') {
        $navItems = [
            ['label' => 'Dashboard', 'route' => 'petugas.dashboard', 'active' => request()->routeIs('dashboard', 'petugas.dashboard'), 'icon' => 'dashboard'],
            ['label' => 'Persetujuan', 'route' => 'petugas.persetujuan.index', 'active' => request()->routeIs('petugas.persetujuan.*', 'petugas.peminjaman'), 'icon' => 'check'],
            ['label' => 'Pengembalian', 'route' => 'petugas.pengembalian.index', 'active' => request()->routeIs('petugas.pengembalian.*'), 'icon' => 'return'],
            ['label' => 'Laporan', 'route' => 'petugas.laporan.index', 'active' => request()->routeIs('petugas.laporan.*'), 'icon' => 'report'],
        ];
        $searchAction = request()->routeIs('petugas.pengembalian.*')
            ? route('petugas.pengembalian.index')
            : route('petugas.persetujuan.index');
        $searchPlaceholder = 'Cari kode #PMJ atau username peminjam...';
    } else {
        $navItems = [
            ['label' => 'Dashboard', 'route' => 'peminjam.dashboard', 'active' => request()->routeIs('dashboard', 'peminjam.dashboard'), 'icon' => 'dashboard'],
            ['label' => 'Katalog Alat', 'route' => 'peminjam.katalog.index', 'active' => request()->routeIs('peminjam.katalog.*'), 'icon' => 'box'],
            ['label' => 'Daftar Pinjam', 'route' => 'peminjam.daftar-pinjam.index', 'active' => request()->routeIs('peminjam.daftar-pinjam.*'), 'icon' => 'bag'],
            ['label' => 'Peminjaman Saya', 'route' => 'peminjam.peminjaman-saya.index', 'active' => request()->routeIs('peminjam.peminjaman-saya.*'), 'icon' => 'clipboard'],
        ];
        $searchAction = route('peminjam.katalog.index');
        $searchPlaceholder = 'Cari peralatan lab (mikroskop, multimeter, proyektor)...';
    }

    $roleLabel = match ($role) {
        'admin' => 'Admin',
        'petugas' => 'Petugas',
        default => 'Peminjam',
    };
@endphp

    {{-- Rail Merah Tipis Sisi Kiri (56px / w-14) --}}
    <aside class="no-print fixed inset-y-0 left-0 z-40 w-14 bg-[#E53935] text-white flex flex-col items-center justify-between py-3">
        {{-- Atas: Toggle Drawer + Quick Search (tinggi h-[88px] sejajar dengan header drawer) --}}
        <div class="h-[88px] flex flex-col items-center justify-start gap-2">
            <button
                type="button"
                id="mobile-drawer-toggle"
                class="w-10 h-10 rounded-lg flex items-center justify-center text-white/90 hover:bg-white/20 transition"
                title="Buka Menu Navigasi"
                aria-label="Buka Menu Navigasi"
                aria-expanded="false"
            >
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <line class="ham-line ham-top" x1="4" y1="6" x2="20" y2="6"/>
                    <line class="ham-line ham-mid" x1="4" y1="12" x2="20" y2="12"/>
                    <line class="ham-line ham-bot" x1="4" y1="18" x2="20" y2="18"/>
                </svg>
            </button>

            <button
                type="button"
                id="quick-search-trigger"
                class="w-10 h-10 rounded-lg flex items-center justify-center text-white/90 hover:bg-white/20 transition"
                title="Fokus Pencarian"
                aria-label="Fokus Pencarian"
            >
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7"/>
                    <line x1="20" y1="20" x2="16.2" y2="16.2"/>
                </svg>
            </button>
        </div>

        {{-- Tengah: Ikon Navigasi Dinamis Per Role (sejajar 1:1 dengan item drawer) --}}
        <nav class="flex-1 flex flex-col items-center justify-center gap-1.5 w-full px-2" aria-label="Navigasi Cepat">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    data-nav-idx="{{ $loop->index }}"
                    title="{{ $item['label'] }}"
                    aria-label="{{ $item['label'] }}"
                    class="rail-nav-item relative w-10 h-10 rounded-lg flex items-center justify-center transition-all duration-150 {{ $item['active'] ? 'bg-white/20 text-white' : 'text-white/80 hover:bg-white/15 hover:text-white' }}"
                >
                    @if ($item['active'])
                        <span class="absolute -left-2 top-2 bottom-2 w-1 bg-white rounded-r"></span>
                    @endif

                    @include('partials.nav-icon', ['icon' => $item['icon'], 'class' => 'w-5 h-5'])
                </a>
            @endforeach
        </nav>

        {{-- Bawah: Tombol Logout Cepat (tinggi h-[64px] sejajar dengan footer drawer) --}}
        <form method="POST" action="{{ route('logout') }}" class="h-[64px] flex items-center justify-center">
            @csrf
            <button
                type="submit"
                id="rail-logout-btn"
                class="w-10 h-10 rounded-lg flex items-center justify-center text-white/85 hover:bg-white/20 hover:text-white transition-all duration-150"
                title="Logout"
                aria-label="Logout"
            >
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </button>
        </form>
    </aside>

    {{-- Drawer Navigasi (Sejajar 1:1 dengan Rail Merah & Animasi Elegan) --}}
    <div id="mobile-drawer-backdrop" class="no-print drawer-backdrop fixed inset-0 z-30 bg-[#1F2937]/30 opacity-0 invisible pointer-events-none"></div>
    <div id="mobile-drawer" class="no-print drawer-panel drawer-closed fixed inset-y-0 left-14 z-30 w-64 bg-white border-r border-[#E5E7EB] transform -translate-x-full opacity-0 invisible pointer-events-none py-3 flex flex-col justify-between">
        {{-- Zona Atas: Tinggi h-[88px] sejajar dengan zona atas rail --}}
        <div class="h-[88px] px-4 border-b border-[#E5E7EB] flex items-center justify-between">
            <a href="{{ $homeRoute }}" class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-md bg-[#E53935] text-white flex items-center justify-center font-bold text-xs tracking-tight">LP</span>
                <div class="leading-tight">
                    <span class="block font-bold text-base tracking-tight text-[#1F2937]">Lab<span class="text-[#E53935]">Pinjam</span></span>
                    <span class="block text-[10px] font-medium uppercase tracking-wider text-[#6B7280]">Navigasi {{ $roleLabel }}</span>
                </div>
            </a>
            <button type="button" id="mobile-drawer-close" class="p-1.5 rounded-lg text-[#6B7280] hover:bg-[#F7F7F8] hover:text-[#1F2937] transition" aria-label="Tutup Menu">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        {{-- Zona Tengah: flex-1 justify-center gap-1.5 & item h-10 sejajar 1:1 dengan ikon rail --}}
        <div class="flex-1 flex flex-col justify-center gap-1.5 px-3 overflow-y-auto">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    data-nav-idx="{{ $loop->index }}"
                    style="--stagger: {{ $loop->index * 70 + 120 }}ms;"
                    class="drawer-nav-item nav-label-item h-10 px-3.5 rounded-lg flex items-center justify-between text-sm {{ $item['active'] ? 'bg-[#FEF2F2] text-[#E53935] font-semibold' : 'text-[#1F2937] hover:bg-[#F7F7F8] hover:translate-x-0.5 font-medium' }}"
                >
                    <span>{{ $item['label'] }}</span>
                    @if ($role === 'peminjam' && $item['route'] === 'peminjam.daftar-pinjam.index' && $jumlahDaftarPinjam > 0)
                        <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-[#E53935] text-white">{{ $jumlahDaftarPinjam }}</span>
                    @elseif ($item['active'])
                        <span class="w-1.5 h-1.5 rounded-full bg-[#E53935]"></span>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Zona Bawah: Tinggi h-[64px] sejajar dengan tombol logout di rail --}}
        <div class="h-[64px] px-4 border-t border-[#E5E7EB] bg-[#F7F7F8] flex items-center justify-between gap-3">
            <div class="min-w-0 leading-tight">
                <p class="text-sm font-semibold text-[#1F2937] truncate">{{ $user?->username }}</p>
                <p class="text-[11px] text-[#6B7280] uppercase tracking-wider">{{ $roleLabel }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                @csrf
                <button
                    type="submit"
                    id="drawer-logout-btn"
                    class="py-1.5 px-3 rounded-lg border border-[#E5E7EB] bg-white text-xs font-semibold text-[#1F2937] hover:border-[#E53935] hover:text-[#E53935] transition"
                >
                    Logout
                </button>
            </form>
        </div>
    </div>

    {{-- Top Header Putih --}}
    <header class="no-print sticky top-0 z-20 bg-white border-b border-[#E5E7EB] pl-14">
        {{-- Baris Utama: Logo, Search Bar Tengah, Aksi Kanan --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
            {{-- Kiri: Logo LabPinjam --}}
            <a href="{{ $homeRoute }}" class="flex items-center gap-2.5 shrink-0">
                <span class="w-8 h-8 rounded-lg bg-[#E53935] text-white flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 3h6"/>
                        <path d="M10 3v6.5L5.2 18.2A2 2 0 0 0 7 21h10a2 2 0 0 0 1.8-2.8L14 9.5V3"/>
                    </svg>
                </span>
                <div class="leading-none">
                    <span class="text-base font-bold tracking-tight text-[#1F2937]">Lab<span class="text-[#E53935]">Pinjam</span></span>
                    <span class="hidden sm:block text-[11px] text-[#6B7280] mt-0.5">Inventaris Lab Sekolah</span>
                </div>
            </a>

            {{-- Kanan: Search Ekspandabel, Badge Daftar Pinjam (Peminjam), Profil User & Logout --}}
            @php
                $userInitial = mb_strtoupper(mb_substr($user?->username ?? 'U', 0, 1));
                $roleBadgeColor = match ($role) {
                    'admin' => 'bg-[#E53935] text-white',
                    'petugas' => 'bg-[#2563EB] text-white',
                    default => 'bg-[#1F2937] text-white',
                };
                $hasSearchQuery = filled(request('q'));
            @endphp
            <div class="flex items-center gap-2.5 shrink-0">
                <form
                    id="header-expand-search"
                    action="{{ $searchAction }}"
                    method="GET"
                    data-expanded="{{ $hasSearchQuery ? '1' : '0' }}"
                    class="lp-expand-search relative flex items-center h-9 rounded-full border overflow-hidden shrink-0 {{ $hasSearchQuery ? 'w-[260px] md:w-[320px] lg:w-[360px] bg-white border-[#E53935] ring-2 ring-[#E53935]/10' : 'w-9 bg-[#F7F7F8] border-[#E5E7EB] hover:border-[#CBD5E1]' }}"
                >
                    <button
                        type="button"
                        id="header-expand-search-trigger"
                        title="Cari ({{ $searchPlaceholder }})"
                        aria-label="Buka pencarian"
                        class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 cursor-pointer transition-colors {{ $hasSearchQuery ? 'text-[#E53935]' : 'text-[#4B5563] hover:text-[#E53935]' }}"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/>
                            <line x1="20" y1="20" x2="16.2" y2="16.2"/>
                        </svg>
                    </button>

                    <div
                        id="header-expand-search-fields"
                        class="lp-expand-fields flex items-center flex-1 min-w-0 pr-1.5 {{ $hasSearchQuery ? 'opacity-100' : 'opacity-0 pointer-events-none' }}"
                    >
                        <input
                            id="global-search-input"
                            type="search"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="{{ $searchPlaceholder }}"
                            class="w-full bg-transparent text-xs sm:text-sm text-[#1F2937] placeholder-[#6B7280] focus:outline-none pr-1.5"
                        >
                        <button
                            type="button"
                            id="header-expand-search-close"
                            title="Tutup pencarian (Esc)"
                            aria-label="Tutup pencarian"
                            class="w-6 h-6 rounded-full flex items-center justify-center text-[#6B7280] hover:text-[#1F2937] hover:bg-[#F3F4F6] shrink-0 cursor-pointer transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                </form>
                @if ($role === 'peminjam')
                    <a
                        href="{{ route('peminjam.daftar-pinjam.index') }}"
                        class="relative inline-flex items-center justify-center w-9 h-9 rounded-full border border-[#E5E7EB] bg-white text-[#1F2937] hover:border-[#E53935] hover:text-[#E53935] transition-colors"
                        title="Daftar Pinjam"
                        aria-label="Daftar Pinjam"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <path d="M16 10a4 4 0 0 1-8 0"/>
                        </svg>
                        <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-[#E53935] text-white text-[10px] font-bold flex items-center justify-center">
                            {{ $jumlahDaftarPinjam }}
                        </span>
                    </a>
                @endif

                <div class="relative" id="account-menu-wrapper">
                    <button
                        type="button"
                        id="account-menu-trigger"
                        aria-haspopup="menu"
                        aria-expanded="false"
                        class="flex items-center gap-2 pl-1.5 pr-3 py-1 rounded-full bg-[#F7F7F8] border border-[#E5E7EB] hover:bg-white hover:border-[#CBD5E1] transition-colors cursor-pointer focus:outline-none"
                    >
                        <span class="w-7 h-7 rounded-full text-xs font-bold flex items-center justify-center shrink-0 {{ $roleBadgeColor }}">
                            {{ $userInitial }}
                        </span>
                        <span class="text-xs font-semibold text-[#1F2937] max-w-[96px] sm:max-w-[130px] truncate">{{ $user?->username }}</span>
                        <span class="hidden sm:inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $roleBadgeColor }}">{{ $roleLabel }}</span>
                        <svg id="account-menu-chevron" class="w-3.5 h-3.5 text-[#6B7280] shrink-0 transition-transform duration-150" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>

                    <div
                        id="account-menu-popover"
                        role="menu"
                        class="hidden lp-dd-menu absolute right-0 mt-1.5 w-52 bg-white rounded-xl border border-[#E5E7EB] shadow-lg p-1.5 z-50"
                    >
                        <div class="px-2.5 py-2 border-b border-[#F3F4F6] mb-1">
                            <p class="text-xs font-bold text-[#1F2937] truncate">{{ $user?->username }}</p>
                            <p class="text-[11px] text-[#6B7280]">Masuk sebagai {{ $roleLabel }}</p>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                role="menuitem"
                                class="w-full px-2.5 py-2 rounded-lg text-left text-xs font-semibold text-[#E53935] hover:bg-[#FEF2F2] flex items-center gap-2 transition-colors cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sub-bar Navigasi Horizontal Dinamis Sesuai Role --}}
        @if ($role === 'peminjam')
            <nav class="border-t border-[#E5E7EB] bg-white" aria-label="Menu Utama Peminjam">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 py-2 flex items-center justify-between gap-3">
                    <div
                        id="peminjam-nav-track"
                        class="lp-peminjam-track relative inline-flex items-center gap-1 p-1 rounded-full bg-[#F7F7F8] border border-[#E5E7EB] overflow-x-auto max-w-full"
                    >
                        <div
                            id="peminjam-nav-glider"
                            aria-hidden="true"
                            class="lp-peminjam-glider opacity-0 absolute top-1 bottom-1 left-0 z-0 rounded-full bg-white border border-[#E5E7EB]/80 shadow-sm pointer-events-none"
                            style="width: 0px; transform: translate3d(0px, 0, 0);"
                        ></div>

                        @foreach ($navItems as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                data-peminjam-tab
                                data-active="{{ $item['active'] ? '1' : '0' }}"
                                class="lp-peminjam-tab relative z-10 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs sm:text-sm whitespace-nowrap transition-colors {{ $item['active'] ? 'bg-white shadow-sm text-[#1F2937] font-semibold' : 'text-[#6B7280] hover:text-[#1F2937] font-medium' }}"
                            >
                                @include('partials.nav-icon', [
                                    'icon' => $item['icon'],
                                    'class' => 'w-3.5 h-3.5 shrink-0 transition-colors ' . ($item['active'] ? 'text-[#E53935]' : 'text-[#6B7280]'),
                                ])
                                <span>{{ $item['label'] }}</span>
                                @if ($item['route'] === 'peminjam.daftar-pinjam.index' && $jumlahDaftarPinjam > 0)
                                    <span class="ml-0.5 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-[#E53935] text-white leading-none">
                                        {{ $jumlahDaftarPinjam }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>

                    @if ($jumlahDaftarPinjam > 0)
                        <a
                            href="{{ route('peminjam.daftar-pinjam.index') }}"
                            class="hidden md:inline-flex items-center gap-1.5 text-xs font-medium text-[#6B7280] hover:text-[#1F2937] whitespace-nowrap transition-colors shrink-0"
                        >
                            <span class="w-1.5 h-1.5 rounded-full bg-[#E53935]"></span>
                            <span>{{ $jumlahDaftarPinjam }} unit siap diajukan</span>
                        </a>
                    @endif
                </div>
            </nav>
        @else
            <nav class="border-t border-[#E5E7EB] bg-white" aria-label="Menu Utama">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center gap-6 overflow-x-auto">
                    @foreach ($navItems as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            class="relative py-3 text-sm whitespace-nowrap transition border-b-2 {{ $item['active'] ? 'border-[#E53935] text-[#1F2937] font-semibold' : 'border-transparent text-[#6B7280] hover:text-[#1F2937] font-medium' }}"
                        >
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </nav>
        @endif
    </header>

    {{-- Area Konten Utama --}}
    <main class="pl-14 min-h-[calc(100vh-7rem)] bg-[#F7F7F8]">
        @yield('hero')
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
            {{-- Notifikasi Toast Mengambang --}}
            @include('partials.toast')

            @yield('content')
        </div>
    </main>
    <script>
        (function () {
            var mainEl = document.querySelector('main');
            if (!mainEl) return;
            mainEl.querySelectorAll('.lp-card-3d, .lp-card, .lp-card-lg, .lp-panel-lg, article, section').forEach(function (el) {
                if (el.closest('.fixed, [role="dialog"], .no-reveal') || el.classList.contains('hidden')) return;
                if (el.tagName === 'SECTION' && !el.classList.contains('lp-panel-lg') && el.querySelector('.lp-card-3d, .lp-card, .lp-card-lg, .lp-panel-lg, article')) return;
                el.classList.add('lp-reveal');
            });
        })();
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const drawer = document.getElementById('mobile-drawer');
            const backdrop = document.getElementById('mobile-drawer-backdrop');
            const toggleBtn = document.getElementById('mobile-drawer-toggle');
            const closeBtn = document.getElementById('mobile-drawer-close');

            const railLogoutBtn = document.getElementById('rail-logout-btn');
            const drawerLogoutBtn = document.getElementById('drawer-logout-btn');
            const railItems = document.querySelectorAll('.rail-nav-item');
            const drawerItems = document.querySelectorAll('.drawer-nav-item');

            function openDrawer() {
                drawer.classList.remove('-translate-x-full', 'opacity-0', 'invisible', 'pointer-events-none', 'drawer-closed');
                drawer.classList.add('translate-x-0', 'opacity-100', 'drawer-open');
                backdrop.classList.remove('opacity-0', 'invisible', 'pointer-events-none');
                backdrop.classList.add('opacity-100');
                if (toggleBtn) {
                    toggleBtn.classList.add('is-open', 'bg-white/20');
                    toggleBtn.setAttribute('aria-expanded', 'true');
                }
            }

            function closeDrawer() {
                drawer.classList.add('-translate-x-full', 'opacity-0', 'invisible', 'pointer-events-none', 'drawer-closed');
                drawer.classList.remove('translate-x-0', 'opacity-100', 'drawer-open');
                backdrop.classList.add('opacity-0', 'invisible', 'pointer-events-none');
                backdrop.classList.remove('opacity-100');
                if (toggleBtn) {
                    toggleBtn.classList.remove('is-open', 'bg-white/20');
                    toggleBtn.setAttribute('aria-expanded', 'false');
                }
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    if (drawer.classList.contains('drawer-closed')) {
                        openDrawer();
                    } else {
                        closeDrawer();
                    }
                });
            }

            if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
            if (backdrop) backdrop.addEventListener('click', closeDrawer);

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && drawer && drawer.classList.contains('drawer-open')) {
                    closeDrawer();
                }
            });

            // Synchronized hover antara ikon rail merah dan baris menu drawer
            railItems.forEach(function (railEl) {
                const idx = railEl.getAttribute('data-nav-idx');
                const drawerEl = document.querySelector('.drawer-nav-item[data-nav-idx="' + idx + '"]');
                if (!drawerEl) return;

                railEl.addEventListener('mouseenter', function () {
                    railEl.classList.add('sync-rail-hover');
                    drawerEl.classList.add('sync-drawer-hover');
                });
                railEl.addEventListener('mouseleave', function () {
                    railEl.classList.remove('sync-rail-hover');
                    drawerEl.classList.remove('sync-drawer-hover');
                });
                drawerEl.addEventListener('mouseenter', function () {
                    railEl.classList.add('sync-rail-hover');
                    drawerEl.classList.add('sync-drawer-hover');
                });
                drawerEl.addEventListener('mouseleave', function () {
                    railEl.classList.remove('sync-rail-hover');
                    drawerEl.classList.remove('sync-drawer-hover');
                });
            });

            if (railLogoutBtn && drawerLogoutBtn) {
                railLogoutBtn.addEventListener('mouseenter', function () {
                    railLogoutBtn.classList.add('sync-rail-hover');
                    drawerLogoutBtn.classList.add('border-[#E53935]', 'text-[#E53935]');
                });
                railLogoutBtn.addEventListener('mouseleave', function () {
                    railLogoutBtn.classList.remove('sync-rail-hover');
                    drawerLogoutBtn.classList.remove('border-[#E53935]', 'text-[#E53935]');
                });
                drawerLogoutBtn.addEventListener('mouseenter', function () {
                    railLogoutBtn.classList.add('sync-rail-hover');
                });
                drawerLogoutBtn.addEventListener('mouseleave', function () {
                    railLogoutBtn.classList.remove('sync-rail-hover');
                });
            }

            // Unified Account Dropdown (Opsi B)
            const accWrapper = document.getElementById('account-menu-wrapper');
            const accTrigger = document.getElementById('account-menu-trigger');
            const accPopover = document.getElementById('account-menu-popover');
            const accChevron = document.getElementById('account-menu-chevron');

            function setAccountMenu(open) {
                if (!accTrigger || !accPopover) return;
                accTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (open) {
                    accPopover.classList.remove('hidden');
                    accTrigger.classList.remove('bg-[#F7F7F8]', 'border-[#E5E7EB]', 'hover:border-[#CBD5E1]');
                    accTrigger.classList.add('bg-white', 'border-[#E53935]', 'ring-2', 'ring-[#E53935]/10');
                    if (accChevron) {
                        accChevron.classList.add('rotate-180', 'text-[#E53935]');
                        accChevron.classList.remove('text-[#6B7280]');
                    }
                } else {
                    accPopover.classList.add('hidden');
                    accTrigger.classList.add('bg-[#F7F7F8]', 'border-[#E5E7EB]', 'hover:border-[#CBD5E1]');
                    accTrigger.classList.remove('bg-white', 'border-[#E53935]', 'ring-2', 'ring-[#E53935]/10');
                    if (accChevron) {
                        accChevron.classList.remove('rotate-180', 'text-[#E53935]');
                        accChevron.classList.add('text-[#6B7280]');
                    }
                }
            }

            if (accTrigger && accPopover) {
                accTrigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const isOpen = !accPopover.classList.contains('hidden');
                    setAccountMenu(!isOpen);
                });

                document.addEventListener('click', function (e) {
                    if (accWrapper && !accWrapper.contains(e.target) && !accPopover.classList.contains('hidden')) {
                        setAccountMenu(false);
                    }
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && !accPopover.classList.contains('hidden')) {
                        setAccountMenu(false);
                        accTrigger.focus();
                    }
                });
            }
        });
    </script>
    @include('partials.confirm-dialog')
    @include('partials.quick-search-modal')
    @stack('scripts')
</body>
</html>
