@extends('layouts.app')

@section('title', 'Dashboard Peminjam')

@php
    $contohAlatUnggulan = $kategoriUnggulan?->alat
        ?->pluck('nama_alat')
        ->filter()
        ->take(3)
        ->implode(', ');

    $heroSlides = [
        [
            'badge' => 'Inventaris Terintegrasi Semester Genap',
            'title' => 'PERALATAN LAB UNTUK PRAKTIKUM',
            'subtitle' => 'Akses cepat berbagai instrumen laboratorium sekolah yang terkalibrasi dan siap pakai untuk mendukung kegiatan belajar mengajar maupun proyek penelitian siswa.',
        ],
        [
            'badge' => 'Laboratorium Fisika & Elektronika',
            'title' => 'INSTRUMEN UKUR PRESISI TINGGI',
            'subtitle' => 'Pinjam osiloskop digital, multimeter, jangka sorong, dan catu daya DC dengan proses verifikasi cepat oleh laboran sekolah.',
        ],
        [
            'badge' => 'Studio Multimedia & Jaringan',
            'title' => 'PERANGKAT PRODUKSI & KOMPUTASI',
            'subtitle' => 'Dukung tugas akhir dan praktik kejuruan menggunakan kamera DSLR, lighting studio, serta perangkat jaringan standar industri.',
        ],
    ];
@endphp

@section('hero')
    {{-- A. HERO BANNER CAROUSEL FULL-WIDTH (3 Foto Lab Lokal Offline-Safe + Efek Referensi) --}}
    <section
        id="peminjam-hero"
        class="relative w-full h-[360px] sm:h-[410px] bg-slate-900 border-b border-[#E5E7EB] overflow-hidden select-none flex items-center"
        aria-label="Sorotan Inventaris Lab"
    >
        {{-- 3 Foto Latar Belakang Lokal dengan Efek Crossfade & Zoom Drift --}}
        <div id="hero-bg-layer" class="hero-bg-drift absolute inset-0">
            <img
                id="hero-slide-img-0"
                src="{{ asset('images/hero-lab-1.jpg') }}"
                alt="Peralatan Lab untuk Praktikum"
                decoding="async"
                fetchpriority="high"
                class="hero-slide-img absolute inset-0 w-full h-full object-cover object-center transition-opacity duration-700 opacity-100"
            >
            <img
                id="hero-slide-img-1"
                src="{{ asset('images/hero-lab-2.jpg') }}"
                alt="Laboratorium Fisika dan Elektronika"
                loading="lazy"
                decoding="async"
                class="hero-slide-img absolute inset-0 w-full h-full object-cover object-center transition-opacity duration-700 opacity-0"
            >
            <img
                id="hero-slide-img-2"
                src="{{ asset('images/hero-lab-3.jpg') }}"
                alt="Studio Multimedia dan Jaringan"
                loading="lazy"
                decoding="async"
                class="hero-slide-img absolute inset-0 w-full h-full object-cover object-center transition-opacity duration-700 opacity-0"
            >
        </div>

        {{-- Dark Gradient Overlay + Subtle Red Accent Glow --}}
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950/85 via-slate-900/60 to-slate-950/80"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(220,38,38,0.2),transparent_70%)]"></div>

        {{-- Tombol Panah Kiri --}}
        <button
            type="button"
            id="hero-prev"
            aria-label="Slide Sebelumnya"
            class="absolute left-3 sm:left-6 top-1/2 -translate-y-1/2 z-20 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-slate-900/45 hover:bg-slate-900/65 text-white flex items-center justify-center transition-colors"
        >
            <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
        </button>

        {{-- Tombol Panah Kanan --}}
        <button
            type="button"
            id="hero-next"
            aria-label="Slide Berikutnya"
            class="absolute right-3 sm:right-6 top-1/2 -translate-y-1/2 z-20 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-slate-900/45 hover:bg-slate-900/65 text-white flex items-center justify-center transition-colors"
        >
            <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </button>

        {{-- Konten Tengah Hero --}}
        <div class="relative z-10 w-full max-w-4xl mx-auto px-12 sm:px-16 flex flex-col items-center justify-center text-center">
            <span
                id="hero-badge"
                class="hero-text-enter inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900/50 border border-white/15 text-amber-300 text-xs font-semibold tracking-wide uppercase mb-4"
            >
                <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>
                    <path d="M5 3v4"/><path d="M19 17v4"/><path d="M3 5h4"/><path d="M17 19h4"/>
                </svg>
                <span id="hero-badge-text">{{ $heroSlides[0]['badge'] }}</span>
            </span>

            <h1
                id="hero-title"
                class="hero-text-enter text-2xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight uppercase leading-tight"
            >
                {{ $heroSlides[0]['title'] }}
            </h1>

            <p
                id="hero-subtitle"
                class="hero-text-enter mt-3 sm:mt-4 text-sm sm:text-base text-slate-200 max-w-2xl leading-relaxed"
            >
                {{ $heroSlides[0]['subtitle'] }}
            </p>

            {{-- Dua Tombol CTA Berdampingan (Persis Referensi & Screenshot) --}}
            <div class="mt-6 sm:mt-8 flex flex-wrap items-center justify-center gap-3 sm:gap-4">
                <a
                    href="{{ route('peminjam.katalog.index') }}"
                    class="px-6 py-3 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] text-white text-sm font-bold shadow-lg shadow-red-600/30 transition-all hover:-translate-y-0.5 flex items-center gap-2"
                >
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>
                    </svg>
                    <span>Jelajahi Katalog</span>
                </a>

                <a
                    href="{{ route('peminjam.daftar-pinjam.index') }}"
                    class="px-6 py-3 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 text-sm font-bold shadow-lg transition-all hover:-translate-y-0.5 flex items-center gap-2"
                >
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                        <path d="m9 14 2 2 4-4"/>
                    </svg>
                    <span>Daftar Pinjam ({{ $jumlahDaftarPinjam }})</span>
                </a>
            </div>

            {{-- Indikator Slide --}}
            <div class="mt-7 flex items-center gap-2" role="tablist" aria-label="Navigasi slide banner">
                @foreach ($heroSlides as $idx => $slide)
                    <button
                        type="button"
                        data-slide="{{ $idx }}"
                        aria-label="Slide {{ $idx + 1 }}"
                        class="hero-dot h-1.5 rounded-full transition-all {{ $idx === 0 ? 'w-6 bg-red-500' : 'w-2 bg-white/40 hover:bg-white/70' }}"
                    ></button>
                @endforeach
            </div>
        </div>
    </section>
@endsection

@section('content')
    <div class="space-y-10 pb-4">
        {{-- B. RINGKASAN STATUS PEMINJAMAN PRIBADI (3 Kartu Metrik Dinamis dari PostgreSQL) --}}
        <section aria-label="Ringkasan Status Peminjaman" class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5">
            {{-- Kartu 1: Sedang Dipinjam --}}
            <a
                href="{{ route('peminjam.peminjaman-saya.index', ['status' => 'disetujui']) }}"
                style="--d: 0ms;"
                class="lp-card bg-white border border-[#E5E7EB] rounded-xl p-5 flex items-center justify-between gap-4"
            >
                <div class="flex items-center gap-4 min-w-0">
                    <div class="lp-icon-box w-12 h-12 rounded-xl bg-[#FEF2F2] text-[#E53935] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-[#6B7280]">Sedang Dipinjam</p>
                        <p class="text-xl font-bold text-[#1F2937] mt-0.5">{{ number_format($ringkasan['sedang_dipinjam']) }} Transaksi</p>
                        <p class="text-xs text-[#6B7280] mt-0.5 truncate">{{ $ringkasanAktifSubtext }}</p>
                    </div>
                </div>
                <span class="shrink-0 px-2.5 py-1 rounded-full bg-[#EFF6FF] text-[#2563EB] text-xs font-semibold">
                    Aktif
                </span>
            </a>

            {{-- Kartu 2: Menunggu Persetujuan --}}
            <a
                href="{{ route('peminjam.peminjaman-saya.index', ['status' => 'menunggu']) }}"
                style="--d: 55ms;"
                class="lp-card bg-white border border-[#E5E7EB] rounded-xl p-5 flex items-center justify-between gap-4"
            >
                <div class="flex items-center gap-4 min-w-0">
                    <div class="lp-icon-box w-12 h-12 rounded-xl bg-[#FEF2F2] text-[#E53935] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"/>
                            <polyline points="12 7 12 12 15 14"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-[#6B7280]">Menunggu Persetujuan</p>
                        <p class="text-xl font-bold text-[#1F2937] mt-0.5">{{ number_format($ringkasan['menunggu']) }} Pengajuan</p>
                        <p class="text-xs text-[#6B7280] mt-0.5 truncate">
                            {{ $ringkasan['menunggu'] > 0 ? 'Menunggu verifikasi petugas lab' : 'Tidak ada antrean pengajuan' }}
                        </p>
                    </div>
                </div>
                <span class="shrink-0 px-2.5 py-1 rounded-full bg-[#FFFBEB] text-[#D97706] text-xs font-semibold">
                    Proses
                </span>
            </a>

            {{-- Kartu 3: Total Riwayat --}}
            <a
                href="{{ route('peminjam.peminjaman-saya.index') }}"
                style="--d: 110ms;"
                class="lp-card bg-white border border-[#E5E7EB] rounded-xl p-5 flex items-center justify-between gap-4"
            >
                <div class="flex items-center gap-4 min-w-0">
                    <div class="lp-icon-box w-12 h-12 rounded-xl bg-[#FEF2F2] text-[#E53935] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                            <rect x="9" y="3" width="6" height="4" rx="1"/>
                            <line x1="9" y1="12" x2="15" y2="12"/>
                            <line x1="9" y1="16" x2="13" y2="16"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-[#6B7280]">Total Riwayat</p>
                        <p class="text-xl font-bold text-[#1F2937] mt-0.5">{{ number_format($ringkasan['total_riwayat']) }} Transaksi</p>
                        <p class="text-xs text-[#6B7280] mt-0.5 truncate">
                            {{ number_format($ringkasan['selesai']) }} selesai dikembalikan
                        </p>
                    </div>
                </div>
                <span class="shrink-0 px-2.5 py-1 rounded-full bg-[#F0FDF4] text-[#16A34A] text-xs font-semibold">
                    Riwayat
                </span>
            </a>
        </section>

        {{-- C. SECTION 5 ALAT LABORATORIUM TERPOPULER --}}
        <section id="katalog-populer" class="space-y-6">
            <div class="text-center max-w-2xl mx-auto">
                <h2 class="text-lg sm:text-xl font-extrabold tracking-wider text-[#1F2937] uppercase">
                    Alat Laboratorium Terpopuler
                </h2>
                <p class="text-xs sm:text-sm text-[#6B7280] mt-1">
                    Peralatan laboratorium yang paling sering dipinjam beserta status ketersediaan stok saat ini.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                @forelse ($alatPopuler as $alat)
                    @php
                        $stok = (int) $alat->jumlah_tersedia;
                        $diKeranjang = (int) ($daftarPinjam[$alat->id_alat] ?? 0);
                        $habis = $stok <= 0;
                        $menipis = $stok > 0 && $stok <= 3;
                        $maksTercapai = !$habis && $diKeranjang >= $stok;

                        if ($habis) {
                            $statusLabel = 'Habis';
                            $statusBadgeClass = 'bg-[#DC2626] text-white';
                            $stokTextClass = 'text-[#DC2626]';
                        } elseif ($menipis) {
                            $statusLabel = 'Stok Menipis';
                            $statusBadgeClass = 'bg-[#D97706] text-white';
                            $stokTextClass = 'text-[#D97706]';
                        } else {
                            $statusLabel = 'Tersedia';
                            $statusBadgeClass = 'bg-[#16A34A] text-white';
                            $stokTextClass = 'text-[#16A34A]';
                        }

                        $kodeAlat = 'ALT-' . str_pad((string) $alat->id_alat, 3, '0', STR_PAD_LEFT);
                        $kaliDipinjam = (int) $alat->detail_peminjaman_count;
                    @endphp

                    <article
                        style="--d: {{ $loop->index * 55 }}ms;"
                        class="lp-card bg-white border border-[#E5E7EB] rounded-xl p-4 flex flex-col justify-between"
                    >
                        <div>
                            {{-- Kode Alat & Penanda Keranjang --}}
                            <div class="flex items-center justify-between gap-2 text-[11px] text-[#6B7280] mb-2.5">
                                <span class="font-semibold">{{ $kodeAlat }}</span>
                                @if ($diKeranjang > 0)
                                    <span class="px-2 py-0.5 rounded-full bg-[#1F2937] text-white text-[10px] font-semibold">
                                        {{ $diKeranjang }} di daftar
                                    </span>
                                @endif
                            </div>

                            {{-- Preview Ilustrasi SVG Offline-Safe --}}
                            <a
                                href="{{ route('peminjam.katalog.show', $alat) }}"
                                class="lp-illu-stage block rounded-lg bg-[#F7F7F8] h-32 flex items-center justify-center mb-3.5"
                            >
                                <x-alat-illustration
                                    :alat-id="$alat->id_alat"
                                    :kategori="$alat->kategori?->nama_kategori ?? ''"
                                    :nama-alat="$alat->nama_alat"
                                    class="lp-illu-svg w-20 h-20"
                                />
                            </a>

                            {{-- Badge Status Ketersediaan Stok --}}
                            <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wide {{ $statusBadgeClass }}">
                                {{ $statusLabel }}
                            </span>

                            {{-- Nama Alat & Kategori --}}
                            <h3 class="mt-2">
                                <a
                                    href="{{ route('peminjam.katalog.show', $alat) }}"
                                    class="text-sm font-bold text-[#1F2937] hover:text-[#E53935] transition line-clamp-2 leading-snug"
                                >
                                    {{ $alat->nama_alat }}
                                </a>
                            </h3>
                            <p class="text-xs text-[#6B7280] mt-0.5 truncate">
                                {{ $alat->kategori?->nama_kategori ?? 'Umum' }}
                            </p>

                            {{-- Indikator Stok & Frekuensi Pinjam Nyata --}}
                            <div class="mt-2.5 text-xs font-bold {{ $stokTextClass }}">
                                {{ $habis ? 'Stok Habis' : 'Tersedia ' . $stok . ' unit' }}
                            </div>
                            <p class="text-[11px] text-[#6B7280] mt-0.5">
                                {{ $kaliDipinjam > 0 ? 'Dipinjam ' . $kaliDipinjam . 'x transaksi' : 'Siap dipinjam' }}
                            </p>
                        </div>

                        {{-- Form Tambah ke Daftar Pinjam (Terhubung ke Endpoint Session Cart) --}}
                        <form method="POST" action="{{ route('peminjam.daftar-pinjam.tambah') }}" class="mt-4 pt-3 border-t border-[#E5E7EB]">
                            @csrf
                            <input type="hidden" name="id_alat" value="{{ $alat->id_alat }}">
                            <input type="hidden" name="jumlah" value="1">
                            <button
                                type="submit"
                                @disabled($habis || $maksTercapai)
                                class="w-full h-9 px-3 rounded-full text-xs font-semibold flex items-center justify-center gap-1.5 transition {{ ($habis || $maksTercapai) ? 'bg-[#F7F7F8] border border-[#E5E7EB] text-[#6B7280] cursor-not-allowed' : 'lp-btn-red bg-[#E53935] hover:bg-[#C62828] text-white' }}"
                            >
                                @if ($habis)
                                    <span>Stok Habis</span>
                                @elseif ($maksTercapai)
                                    <span>Maks. di Daftar</span>
                                @else
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"/>
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                    <span>Pinjam</span>
                                @endif
                            </button>
                        </form>
                    </article>
                @empty
                    <div class="col-span-full bg-white border border-[#E5E7EB] rounded-xl p-10 text-center">
                        <p class="text-sm font-semibold text-[#1F2937]">Belum ada data alat laboratorium di inventaris.</p>
                    </div>
                @endforelse
            </div>

            {{-- Tombol Lihat Semua Peralatan Lab --}}
            <div class="pt-1 flex justify-center">
                <a
                    href="{{ route('peminjam.katalog.index') }}"
                    class="inline-flex items-center gap-2 h-10 px-7 rounded-full bg-[#1F2937] hover:bg-[#111827] text-white text-xs sm:text-sm font-semibold transition"
                >
                    <span>Lihat Semua Peralatan Lab ({{ $totalAlat }})</span>
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                </a>
            </div>
        </section>

        {{-- D. 2 BANNER INFORMASI DI BAGIAN BAWAH --}}
        <section class="grid grid-cols-1 md:grid-cols-2 gap-5">
            {{-- Banner Kiri: Panduan Peminjaman Lab --}}
            <button
                type="button"
                id="btn-buka-panduan"
                style="--d: 120ms;"
                class="lp-card-lg group text-left relative min-h-[225px] rounded-xl bg-[#1F2937] border border-[#E5E7EB] p-6 sm:p-7 flex flex-col justify-between overflow-hidden"
            >
                <img
                    src="{{ asset('images/hero-lab-1.jpg') }}"
                    alt="Panduan Peminjaman Lab"
                    class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-[#111827] via-[#1F2937]/75 to-[#1F2937]/30"></div>

                <div class="relative z-10 w-full flex items-start justify-between gap-3">
                    <span class="px-2.5 py-1 rounded-md bg-[#E53935] text-white text-[11px] font-bold uppercase tracking-wider">
                        SOP &amp; Aturan
                    </span>
                    <span class="w-9 h-9 rounded-full bg-white/15 group-hover:bg-[#E53935] text-white flex items-center justify-center transition">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="7" y1="17" x2="17" y2="7"/>
                            <polyline points="7 7 17 7 17 17"/>
                        </svg>
                    </span>
                </div>

                <div class="relative z-10 max-w-md mt-8">
                    <h3 class="text-lg font-extrabold text-white uppercase tracking-tight">
                        Panduan Peminjaman Lab
                    </h3>
                    <p class="text-xs sm:text-sm text-white/80 mt-1.5 leading-relaxed">
                        Pelajari alur pengajuan, batas waktu pinjam standar 3 hari, tarif denda keterlambatan Rp 2.000/hari, dan prosedur pengembalian alat.
                    </p>
                </div>
            </button>

            {{-- Banner Kanan: Kategori Unggulan Dinamis dari PostgreSQL --}}
            <a
                href="{{ route('peminjam.katalog.index', $kategoriUnggulan ? ['kategori' => $kategoriUnggulan->id_kategori] : []) }}"
                style="--d: 180ms;"
                class="lp-card-lg group relative min-h-[225px] rounded-xl bg-[#1F2937] border border-[#E5E7EB] p-6 sm:p-7 flex flex-col justify-between overflow-hidden"
            >
                <img
                    src="{{ asset('images/hero-lab-2.jpg') }}"
                    alt="{{ $kategoriUnggulan?->nama_kategori ?? 'Kategori Unggulan' }}"
                    class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-[#111827] via-[#1F2937]/75 to-[#1F2937]/30"></div>

                <div class="relative z-10 w-full flex items-start justify-between gap-3">
                    <span class="px-2.5 py-1 rounded-md bg-white text-[#1F2937] text-[11px] font-bold uppercase tracking-wider">
                        Kategori Unggulan
                    </span>
                    <span class="w-9 h-9 rounded-full bg-white/15 group-hover:bg-[#E53935] text-white flex items-center justify-center transition">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="7" y1="17" x2="17" y2="7"/>
                            <polyline points="7 7 17 7 17 17"/>
                        </svg>
                    </span>
                </div>

                <div class="relative z-10 max-w-md mt-8">
                    <h3 class="text-lg font-extrabold text-white uppercase tracking-tight">
                        {{ $kategoriUnggulan?->nama_kategori ?? 'Katalog Laboratorium' }}
                    </h3>
                    <p class="text-xs sm:text-sm text-white/80 mt-1.5 leading-relaxed">
                        @if ($kategoriUnggulan && $contohAlatUnggulan)
                            Tersedia {{ $kategoriUnggulan->alat_count }} peralatan lab termasuk {{ $contohAlatUnggulan }} untuk kebutuhan praktikum sekolah.
                        @else
                            Jelajahi seluruh peralatan laboratorium berdasarkan kategori dan cek ketersediaan stok terkini.
                        @endif
                    </p>
                </div>
            </a>
        </section>
    </div>

    {{-- Modal Panduan & SOP Peminjaman Lab --}}
    <div
        id="panduan-modal"
        class="no-print fixed inset-0 z-50 hidden items-center justify-center bg-[#1F2937]/40 px-4 py-6"
        role="dialog"
        aria-modal="true"
        aria-labelledby="panduan-modal-title"
    >
        <div class="w-full max-w-lg bg-white border border-[#E5E7EB] border-t-4 border-t-[#E53935] rounded-xl p-6">
            <div class="flex items-start justify-between gap-4 pb-3 border-b border-[#E5E7EB]">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#E53935]">SOP Laboratorium</p>
                    <h2 id="panduan-modal-title" class="text-base font-bold text-[#1F2937] mt-0.5">
                        Panduan Peminjaman Alat Lab
                    </h2>
                </div>
                <button
                    type="button"
                    id="panduan-modal-close"
                    class="p-1 rounded-lg text-[#6B7280] hover:bg-[#F7F7F8] hover:text-[#1F2937] transition"
                    aria-label="Tutup panduan"
                >
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <ol class="mt-4 space-y-3 text-xs text-[#1F2937]">
                <li class="flex gap-3 p-3 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB]">
                    <span class="w-6 h-6 rounded-full bg-[#E53935] text-white font-bold flex items-center justify-center shrink-0">1</span>
                    <div>
                        <p class="font-semibold">Pilih Alat di Katalog</p>
                        <p class="text-[#6B7280] mt-0.5">Tambahkan satu atau beberapa jenis alat ke Daftar Pinjam sesuai jumlah yang tersedia.</p>
                    </div>
                </li>
                <li class="flex gap-3 p-3 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB]">
                    <span class="w-6 h-6 rounded-full bg-[#E53935] text-white font-bold flex items-center justify-center shrink-0">2</span>
                    <div>
                        <p class="font-semibold">Ajukan &amp; Tunggu Persetujuan Petugas</p>
                        <p class="text-[#6B7280] mt-0.5">Pilih tanggal pinjam lalu kirim pengajuan. Stok alat dikurangi setelah Petugas menyetujui.</p>
                    </div>
                </li>
                <li class="flex gap-3 p-3 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB]">
                    <span class="w-6 h-6 rounded-full bg-[#E53935] text-white font-bold flex items-center justify-center shrink-0">3</span>
                    <div>
                        <p class="font-semibold">Kembalikan Tepat Waktu (Maksimal 3 Hari)</p>
                        <p class="text-[#6B7280] mt-0.5">Klik tombol Kembalikan Alat di Peminjaman Saya lalu serahkan alat ke petugas. Keterlambatan dikenakan saran denda Rp 2.000/hari.</p>
                    </div>
                </li>
            </ol>

            <div class="mt-5 flex items-center justify-end gap-2.5">
                <button
                    type="button"
                    id="panduan-modal-ok"
                    class="h-9 px-5 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold transition"
                >
                    Mengerti
                </button>
            </div>
        </div>
    </div>

    @include('peminjam.partials.added-modal')
@endsection

@push('scripts')
    <script>
        (function () {
            const slides = @json($heroSlides);
            let currentSlide = 0;
            let autoTimer = null;

            const heroSection = document.getElementById('peminjam-hero');
            const bgLayerEl = document.getElementById('hero-bg-layer');
            const slideImgs = document.querySelectorAll('.hero-slide-img');
            const badgeEl = document.getElementById('hero-badge');
            const badgeTextEl = document.getElementById('hero-badge-text');
            const titleEl = document.getElementById('hero-title');
            const subtitleEl = document.getElementById('hero-subtitle');
            const dots = document.querySelectorAll('.hero-dot');
            const prevBtn = document.getElementById('hero-prev');
            const nextBtn = document.getElementById('hero-next');

            let animRaf = 0;

            function renderSlide(idx) {
                currentSlide = (idx + slides.length) % slides.length;
                const slide = slides[currentSlide];

                if (badgeTextEl) badgeTextEl.textContent = slide.badge;
                if (titleEl) titleEl.textContent = slide.title;
                if (subtitleEl) subtitleEl.textContent = slide.subtitle;

                slideImgs.forEach(function (img, i) {
                    img.classList.toggle('opacity-100', i === currentSlide);
                    img.classList.toggle('opacity-0', i !== currentSlide);
                });

                if (bgLayerEl) bgLayerEl.classList.remove('hero-bg-drift');
                [badgeEl, titleEl, subtitleEl].forEach(function (el) {
                    if (el) el.classList.remove('hero-text-enter');
                });

                if (animRaf) cancelAnimationFrame(animRaf);
                animRaf = requestAnimationFrame(function () {
                    animRaf = 0;
                    if (bgLayerEl) bgLayerEl.classList.add('hero-bg-drift');
                    [badgeEl, titleEl, subtitleEl].forEach(function (el) {
                        if (el) el.classList.add('hero-text-enter');
                    });
                });

                dots.forEach(function (dot, i) {
                    if (i === currentSlide) {
                        dot.className = 'hero-dot h-1.5 w-6 rounded-full bg-red-500 transition-all';
                    } else {
                        dot.className = 'hero-dot h-1.5 w-2 rounded-full bg-white/40 hover:bg-white/70 transition-all';
                    }
                });
            }

            function startAutoSlide() {
                stopAutoSlide();
                if (document.hidden) return;
                autoTimer = setInterval(function () {
                    renderSlide(currentSlide + 1);
                }, 7000);
            }

            function stopAutoSlide() {
                if (autoTimer) {
                    clearInterval(autoTimer);
                    autoTimer = null;
                }
            }

            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    stopAutoSlide();
                } else {
                    startAutoSlide();
                }
            });

            if (prevBtn) {
                prevBtn.addEventListener('click', function () {
                    renderSlide(currentSlide - 1);
                    startAutoSlide();
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function () {
                    renderSlide(currentSlide + 1);
                    startAutoSlide();
                });
            }

            dots.forEach(function (dot) {
                dot.addEventListener('click', function () {
                    renderSlide(parseInt(dot.dataset.slide, 10) || 0);
                    startAutoSlide();
                });
            });

            if (heroSection) {
                heroSection.addEventListener('mouseenter', stopAutoSlide);
                heroSection.addEventListener('mouseleave', startAutoSlide);
            }

            startAutoSlide();

            // Modal Panduan Peminjaman Lab
            const panduanModal = document.getElementById('panduan-modal');
            const btnBukaPanduan = document.getElementById('btn-buka-panduan');
            const btnClosePanduan = document.getElementById('panduan-modal-close');
            const btnOkPanduan = document.getElementById('panduan-modal-ok');

            function bukaPanduan() {
                if (!panduanModal) return;
                panduanModal.classList.remove('hidden');
                panduanModal.classList.add('flex');
            }

            function tutupPanduan() {
                if (!panduanModal) return;
                panduanModal.classList.add('hidden');
                panduanModal.classList.remove('flex');
            }

            if (btnBukaPanduan) btnBukaPanduan.addEventListener('click', bukaPanduan);
            if (btnClosePanduan) btnClosePanduan.addEventListener('click', tutupPanduan);
            if (btnOkPanduan) btnOkPanduan.addEventListener('click', tutupPanduan);
            if (panduanModal) {
                panduanModal.addEventListener('click', function (event) {
                    if (event.target === panduanModal) tutupPanduan();
                });
            }
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && panduanModal && !panduanModal.classList.contains('hidden')) {
                    tutupPanduan();
                }
            });
        })();
    </script>
@endpush
