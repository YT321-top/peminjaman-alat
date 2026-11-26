@extends('layouts.app')

@section('title', 'Katalog Alat Lab')

@section('content')
    {{-- Bar Kategori Lab Cepat (Gaya Aments Bersih) --}}
    <section class="mb-6" aria-label="Kategori Laboratorium">
        <div class="flex items-center justify-between mb-3">
            <h1 class="text-xl font-bold text-[#1F2937]">Katalog Alat Laboratorium</h1>
            <a
                href="{{ route('peminjam.daftar-pinjam.index') }}"
                class="text-xs font-semibold text-[#E53935] hover:text-[#C62828] transition"
            >
                Buka Daftar Pinjam
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @php
                $baseParams = array_filter([
                    'q' => $q ?: null,
                    'ketersediaan' => $ketersediaan !== 'semua' ? $ketersediaan : null,
                    'sort' => $sort !== 'nama_asc' ? $sort : null,
                ]);
            @endphp

            <a
                href="{{ route('peminjam.katalog.index', $baseParams) }}"
                style="--d: 0ms;"
                class="lp-card rounded-xl border px-3.5 py-3 flex items-center gap-3 {{ !$kategoriAktif ? 'bg-[#E53935] border-[#E53935] text-white' : 'bg-white border-[#E5E7EB] text-[#1F2937]' }}"
            >
                <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ !$kategoriAktif ? 'bg-white/20 text-white' : 'lp-icon-box bg-[#F7F7F8] text-[#1F2937]' }}">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-semibold truncate">Semua Alat</p>
                    <p class="text-[11px] {{ !$kategoriAktif ? 'text-white/80' : 'text-[#6B7280]' }}">{{ $totalSemuaAlat }} alat</p>
                </div>
            </a>

            @foreach ($kategoris as $kat)
                @php
                    $isAktif = $kategoriAktif && $kategoriAktif->id_kategori === $kat->id_kategori;
                    $katUrl = route('peminjam.katalog.index', array_merge($baseParams, ['kategori' => $kat->id_kategori]));
                @endphp
                <a
                    href="{{ $katUrl }}"
                    style="--d: {{ ($loop->index + 1) * 45 }}ms;"
                    class="lp-card rounded-xl border px-3.5 py-3 flex items-center gap-3 {{ $isAktif ? 'bg-[#E53935] border-[#E53935] text-white' : 'bg-white border-[#E5E7EB] text-[#1F2937]' }}"
                >
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 overflow-hidden transition-transform duration-300 {{ $isAktif ? 'bg-white' : 'bg-[#F7F7F8]' }}">
                        <x-alat-illustration :kategori-id="$kat->id_kategori" :kategori="$kat->nama_kategori" class="lp-illu-svg w-6 h-6" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold truncate">{{ $kat->nama_kategori }}</p>
                        <p class="text-[11px] {{ $isAktif ? 'text-white/80' : 'text-[#6B7280]' }}">{{ $kat->alat_count }} alat</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Tata Letak 2 Kolom: Sidebar Filter Kiri + Grid Katalog Kanan --}}
    <div class="flex flex-col lg:flex-row gap-6 items-start">
        {{-- Sidebar Filter Kiri --}}
        <aside class="lp-panel-lg w-full lg:w-64 shrink-0 bg-white border border-[#E5E7EB] rounded-xl p-5 space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-[#E5E7EB]">
                <h2 class="text-sm font-bold text-[#1F2937]">Filter Katalog</h2>
                @if ($kategoriAktif || $ketersediaan !== 'semua' || $q !== '' || $sort !== 'nama_asc')
                    <a
                        href="{{ route('peminjam.katalog.index') }}"
                        class="text-xs font-semibold text-[#E53935] hover:text-[#C62828] transition"
                    >
                        Reset
                    </a>
                @endif
            </div>

            {{-- Filter Kategori --}}
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-[#6B7280] mb-2.5">Kategori Lab</p>
                <ul class="space-y-1">
                    <li>
                        <a
                            href="{{ route('peminjam.katalog.index', $baseParams) }}"
                            class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition {{ !$kategoriAktif ? 'bg-[#FEF2F2] text-[#E53935] font-semibold' : 'text-[#1F2937] hover:bg-[#F7F7F8]' }}"
                        >
                            <span>Semua Kategori</span>
                            <span class="text-[11px] {{ !$kategoriAktif ? 'text-[#E53935]' : 'text-[#6B7280]' }}">{{ $totalSemuaAlat }}</span>
                        </a>
                    </li>
                    @foreach ($kategoris as $kat)
                        @php
                            $isAktif = $kategoriAktif && $kategoriAktif->id_kategori === $kat->id_kategori;
                        @endphp
                        <li>
                            <a
                                href="{{ route('peminjam.katalog.index', array_merge($baseParams, ['kategori' => $kat->id_kategori])) }}"
                                class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition {{ $isAktif ? 'bg-[#FEF2F2] text-[#E53935] font-semibold' : 'text-[#1F2937] hover:bg-[#F7F7F8]' }}"
                            >
                                <span class="truncate pr-2">{{ $kat->nama_kategori }}</span>
                                <span class="text-[11px] shrink-0 {{ $isAktif ? 'text-[#E53935]' : 'text-[#6B7280]' }}">{{ $kat->alat_count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Filter Ketersediaan --}}
            <div class="pt-4 border-t border-[#E5E7EB]">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-[#6B7280] mb-2.5">Ketersediaan Stok</p>
                <form method="GET" action="{{ route('peminjam.katalog.index') }}" class="space-y-2">
                    @if ($kategoriAktif)
                        <input type="hidden" name="kategori" value="{{ $kategoriAktif->id_kategori }}">
                    @endif
                    @if ($q !== '')
                        <input type="hidden" name="q" value="{{ $q }}">
                    @endif
                    @if ($sort !== 'nama_asc')
                        <input type="hidden" name="sort" value="{{ $sort }}">
                    @endif

                    <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-[#F7F7F8] cursor-pointer text-xs text-[#1F2937]">
                        <input
                            type="radio"
                            name="ketersediaan"
                            value="semua"
                            onchange="this.form.submit()"
                            {{ $ketersediaan === 'semua' ? 'checked' : '' }}
                            class="accent-[#E53935]"
                        >
                        <span>Semua Alat</span>
                    </label>

                    <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-[#F7F7F8] cursor-pointer text-xs text-[#1F2937]">
                        <input
                            type="radio"
                            name="ketersediaan"
                            value="tersedia"
                            onchange="this.form.submit()"
                            {{ $ketersediaan === 'tersedia' ? 'checked' : '' }}
                            class="accent-[#E53935]"
                        >
                        <span>Tersedia saja</span>
                    </label>
                </form>
            </div>

            <div class="pt-4 border-t border-[#E5E7EB]">
                <a
                    href="{{ route('peminjam.katalog.index') }}"
                    class="w-full h-9 rounded-lg border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] flex items-center justify-center transition"
                >
                    Reset Filter
                </a>
            </div>
        </aside>

        {{-- Area Kanan: Toolbar Pencarian + Grid Kartu Alat --}}
        <div class="flex-1 min-w-0 w-full">
            <div class="lp-panel-lg bg-white border border-[#E5E7EB] rounded-xl p-4 mb-5">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-[#1F2937]">
                            {{ $kategoriAktif ? $kategoriAktif->nama_kategori : 'Semua Peralatan Lab' }}
                        </h2>
                        <p class="text-xs text-[#6B7280] mt-0.5">
                            Menampilkan {{ $alats->total() }} alat
                            @if ($q !== '')
                                untuk pencarian "<span class="font-semibold text-[#1F2937]">{{ $q }}</span>"
                            @endif
                        </p>
                    </div>

                    <form method="GET" action="{{ route('peminjam.katalog.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                        @if ($kategoriAktif)
                            <input type="hidden" name="kategori" value="{{ $kategoriAktif->id_kategori }}">
                        @endif
                        @if ($ketersediaan !== 'semua')
                            <input type="hidden" name="ketersediaan" value="{{ $ketersediaan }}">
                        @endif

                        <div class="relative">
                            <svg class="w-4 h-4 text-[#6B7280] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="7"/>
                                <line x1="20" y1="20" x2="16.2" y2="16.2"/>
                            </svg>
                            <input
                                type="search"
                                name="q"
                                value="{{ $q }}"
                                placeholder="Cari nama alat lab..."
                                class="w-full sm:w-56 h-9 pl-9 pr-3 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] text-xs text-[#1F2937] placeholder-[#6B7280] focus:bg-white focus:border-[#E53935] focus:outline-none transition"
                            >
                        </div>

                        <select
                            name="sort"
                            onchange="this.form.submit()"
                            aria-label="Urutkan alat"
                            class="w-full sm:w-40 shrink-0 h-9 px-3 rounded-lg bg-white border border-[#E5E7EB] text-xs font-medium text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
                        >
                            <option value="nama_asc" {{ $sort === 'nama_asc' ? 'selected' : '' }}>Nama A - Z</option>
                            <option value="nama_desc" {{ $sort === 'nama_desc' ? 'selected' : '' }}>Nama Z - A</option>
                            <option value="stok_desc" {{ $sort === 'stok_desc' ? 'selected' : '' }}>Stok Terbanyak</option>
                        </select>

                        <button
                            type="submit"
                            class="h-9 px-4 rounded-lg bg-[#1F2937] hover:bg-[#111827] text-white text-xs font-semibold transition"
                        >
                            Cari
                        </button>
                    </form>
                </div>
            </div>

            {{-- Grid Kartu Alat 3-4 Kolom --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @forelse ($alats as $alat)
                    @php
                        $diKeranjang = (int) ($daftarPinjam[$alat->id_alat] ?? 0);
                        $habis = $alat->jumlah_tersedia <= 0;
                        $maksTercapai = !$habis && $diKeranjang >= $alat->jumlah_tersedia;
                    @endphp
                    <article style="--d: {{ min($loop->index, 11) * 40 }}ms;" class="lp-card bg-white border border-[#E5E7EB] rounded-xl p-4 flex flex-col justify-between">
                        <div>
                            <a
                                href="{{ route('peminjam.katalog.show', $alat) }}"
                                class="lp-illu-stage block bg-[#F7F7F8] rounded-lg h-36 flex items-center justify-center mb-3.5 relative group"
                            >
                                <x-alat-illustration
                                    :alat-id="$alat->id_alat"
                                    :kategori="$alat->kategori?->nama_kategori ?? ''"
                                    :nama-alat="$alat->nama_alat"
                                    class="lp-illu-svg w-20 h-20"
                                />
                                @if ($diKeranjang > 0)
                                    <span class="absolute top-2 right-2 z-10 px-2 py-0.5 rounded-full bg-[#1F2937] text-white text-[10px] font-semibold">
                                        {{ $diKeranjang }} di daftar
                                    </span>
                                @endif
                            </a>

                            <p class="text-xs text-[#6B7280] truncate">
                                {{ $alat->kategori?->nama_kategori ?? 'Umum' }}
                            </p>

                            <h3 class="mt-1">
                                <a
                                    href="{{ route('peminjam.katalog.show', $alat) }}"
                                    class="text-sm font-semibold text-[#1F2937] hover:text-[#E53935] transition line-clamp-2"
                                >
                                    {{ $alat->nama_alat }}
                                </a>
                            </h3>
                        </div>

                        <div class="pt-3 mt-3.5 border-t border-[#E5E7EB] flex items-center justify-between gap-2">
                            @if ($habis)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FEF2F2] text-[#DC2626]">
                                    Stok Habis
                                </span>
                            @else
                                <span class="text-xs font-medium text-[#16A34A]">
                                    Stok: {{ $alat->jumlah_tersedia }} unit
                                </span>
                            @endif

                            <form method="POST" action="{{ route('peminjam.daftar-pinjam.tambah') }}">
                                @csrf
                                <input type="hidden" name="id_alat" value="{{ $alat->id_alat }}">
                                <input type="hidden" name="jumlah" value="1">
                                <button
                                    type="submit"
                                    @disabled($habis || $maksTercapai)
                                    title="{{ $habis ? 'Stok habis' : ($maksTercapai ? 'Maksimal stok sudah di Daftar Pinjam' : 'Tambah 1 unit ke Daftar Pinjam') }}"
                                    aria-label="Tambah {{ $alat->nama_alat }} ke Daftar Pinjam"
                                    class="lp-plus-btn w-9 h-9 rounded-full flex items-center justify-center {{ ($habis || $maksTercapai) ? 'bg-[#E5E7EB] text-[#6B7280] cursor-not-allowed' : 'bg-[#E53935] hover:bg-[#C62828] text-white' }}"
                                >
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"/>
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full bg-white border border-[#E5E7EB] rounded-xl p-4">
                        <x-empty-state
                            icon="search"
                            title="Tidak ada alat laboratorium yang sesuai filter."
                            description="Coba ubah kata kunci pencarian atau reset filter kategori."
                            primary-label="Tampilkan Semua Alat"
                            :primary-href="route('peminjam.katalog.index')"
                        />
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($alats->hasPages())
                <div class="mt-6">
                    {{ $alats->links() }}
                </div>
            @endif
        </div>
    </div>

    @include('peminjam.partials.added-modal')
@endsection
