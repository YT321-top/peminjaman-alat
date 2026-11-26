@extends('layouts.app')

@section('title', 'Data Peminjaman')

@section('content')
    @php
        $tabs = [
            'semua' => 'Semua',
            'menunggu' => 'Menunggu',
            'disetujui' => 'Disetujui',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
        ];

        $alatCatalog = $alats->map(fn ($a) => [
            'id' => (int) $a->id_alat,
            'nama' => $a->nama_alat,
            'kategori' => $a->kategori?->nama_kategori ?? 'Umum',
            'stok' => (int) $a->jumlah_tersedia,
        ])->values()->all();

        $oldModalMode = old('_modal_mode');
        $oldEditId = old('_edit_id');
        $oldItems = old('items');
        $reopenModal = $oldModalMode && ($errors->any() || session('error'));
    @endphp

    {{-- Header Halaman --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[#1F2937] tracking-tight">Data Peminjaman</h1>
            <p class="text-sm text-[#6B7280] mt-1">
                Kelola seluruh transaksi peminjaman multi-alat dengan sinkronisasi stok otomatis.
            </p>
        </div>
        <div class="shrink-0">
            <button
                type="button"
                onclick="openCreateModal()"
                class="lp-btn-red inline-flex items-center gap-2 h-10 px-5 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs sm:text-sm font-semibold transition"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>Tambah Peminjaman</span>
            </button>
        </div>
    </div>

    {{-- Bar Filter Status & Pencarian --}}
    <section class="bg-white rounded-2xl border border-[#E5E7EB] px-5 py-3 flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4">
        <div class="flex items-center gap-5 overflow-x-auto border-b lg:border-b-0 border-[#E5E7EB] pb-2 lg:pb-0">
            @foreach ($tabs as $key => $label)
                @php
                    $isActive = $status === $key;
                    $params = array_filter([
                        'status' => $key !== 'semua' ? $key : null,
                        'q' => $q !== '' ? $q : null,
                    ], fn ($v) => $v !== null);
                @endphp
                <a
                    href="{{ route('admin.peminjaman.index', $params) }}"
                    class="flex items-center gap-2 py-2 text-sm whitespace-nowrap border-b-2 transition {{ $isActive ? 'border-[#E53935] text-[#E53935] font-bold' : 'border-transparent text-[#6B7280] hover:text-[#1F2937] font-medium' }}"
                >
                    <span>{{ $label }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $isActive ? 'bg-[#FEF2F2] text-[#E53935]' : 'bg-[#F7F7F8] text-[#6B7280]' }}">
                        {{ $counts[$key] ?? 0 }}
                    </span>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.peminjaman.index') }}" class="flex items-center gap-2.5 w-full lg:w-auto">
            @if ($status !== 'semua')
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <div class="relative flex-1 lg:w-60">
                <svg class="w-4 h-4 text-[#6B7280] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/>
                    <line x1="20" y1="20" x2="16.2" y2="16.2"/>
                </svg>
                <input
                    id="filter-search-input"
                    type="search"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Username atau #PMJ-1..."
                    class="w-full h-9 bg-[#F7F7F8] focus:bg-white text-xs sm:text-sm text-[#1F2937] placeholder-[#6B7280] pl-9 pr-3.5 rounded-xl border border-[#E5E7EB] focus:border-[#E53935] focus:outline-none transition"
                >
            </div>
            <button
                type="submit"
                class="h-9 px-5 rounded-xl bg-[#1F2937] hover:bg-[#111827] text-white text-xs font-semibold transition shrink-0"
            >
                Cari
            </button>
        </form>
    </section>

    {{-- Info Hasil Filter Aktif --}}
    <div class="flex items-center justify-between text-xs text-[#6B7280] px-1 mb-4">
        <div>
            Menampilkan <strong class="font-semibold text-[#1F2937]">{{ $peminjamans->count() }}</strong>
            @if ($peminjamans->total() > $peminjamans->count())
                dari <strong class="font-semibold text-[#1F2937]">{{ $peminjamans->total() }}</strong>
            @endif
            transaksi peminjaman
            <span class="hidden sm:inline">
                &bull; Status: <strong class="font-semibold text-[#1F2937]">{{ $tabs[$status] ?? 'Semua' }}</strong>
                @if ($q !== '')
                    &bull; Kata kunci: <strong class="font-semibold text-[#1F2937]">"{{ $q }}"</strong>
                @endif
            </span>
        </div>

        @if ($status !== 'semua' || $q !== '')
            <a
                href="{{ route('admin.peminjaman.index') }}"
                class="inline-flex items-center gap-1.5 text-[#E53935] hover:text-[#C62828] font-semibold transition"
            >
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="1 4 1 10 7 10"/>
                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                </svg>
                <span>Reset Filter</span>
            </a>
        @endif
    </div>

    {{-- Konten Utama: Card Grid 4 Kolom Responsif --}}
    @if ($peminjamans->isNotEmpty())
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach ($peminjamans as $pinjam)
                @php
                    $totalUnits = $pinjam->detailPeminjaman->sum('jumlah_pinjam');
                    $username = $pinjam->peminjam?->username ?? '-';
                    $initial = mb_strtoupper(mb_substr($username, 0, 1));

                    $statusConfig = match ($pinjam->status) {
                        'menunggu' => [
                            'label' => 'Menunggu',
                            'solid' => 'bg-[#D97706] text-white',
                            'bar' => 'bg-[#D97706]',
                        ],
                        'disetujui' => [
                            'label' => 'Disetujui',
                            'solid' => 'bg-[#2563EB] text-white',
                            'bar' => 'bg-[#2563EB]',
                        ],
                        'selesai' => [
                            'label' => 'Selesai',
                            'solid' => 'bg-[#16A34A] text-white',
                            'bar' => 'bg-[#16A34A]',
                        ],
                        default => [
                            'label' => 'Ditolak',
                            'solid' => 'bg-[#E53935] text-white',
                            'bar' => 'bg-[#E53935]',
                        ],
                    };

                    $trxPayload = [
                        'id' => (int) $pinjam->id_peminjaman,
                        'code' => '#PMJ-' . $pinjam->id_peminjaman,
                        'id_user' => (int) $pinjam->id_user,
                        'username' => $username,
                        'id_petugas' => $pinjam->id_petugas ? (int) $pinjam->id_petugas : '',
                        'tanggal_pinjam' => $pinjam->tanggal_pinjam?->toDateString() ?? today()->toDateString(),
                        'status' => $pinjam->status,
                        'update_url' => route('admin.peminjaman.update', $pinjam),
                        'destroy_url' => route('admin.peminjaman.destroy', $pinjam),
                        'items' => $pinjam->detailPeminjaman->map(fn ($d) => [
                            'id_alat' => (int) $d->id_alat,
                            'jumlah_pinjam' => (int) $d->jumlah_pinjam,
                        ])->values()->all(),
                    ];
                @endphp

                <article
                    style="--d: {{ min($loop->index * 45, 300) }}ms"
                    class="lp-card-3d bg-white rounded-2xl border border-[#E5E7EB] flex flex-col justify-between"
                >
                    <div class="h-1.5 w-full shrink-0 {{ $statusConfig['bar'] }}"></div>

                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            {{-- a. Card Header: Kode Transaksi & Badge Status Warna Langsung --}}
                            <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-[#F3F4F6]">
                                <span class="text-sm font-bold text-[#1F2937] tracking-tight tabular-nums">#PMJ-{{ $pinjam->id_peminjaman }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold shrink-0 {{ $statusConfig['solid'] }}">
                                    {{ $statusConfig['label'] }}
                                </span>
                            </div>

                            {{-- b. Info Peminjam, Approver & Tanggal --}}
                            <div class="mt-3 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-7 h-7 rounded-full text-[11px] font-bold flex items-center justify-center shrink-0 {{ $statusConfig['solid'] }}">
                                        {{ $initial }}
                                    </span>
                                    <div class="min-w-0 leading-tight">
                                        <p class="text-xs font-bold text-[#1F2937] truncate">{{ $username }}</p>
                                        <p class="text-[11px] text-[#6B7280] truncate">
                                            App: <strong class="font-semibold text-[#1F2937]">{{ $pinjam->petugas?->username ?? '-' }}</strong>
                                        </p>
                                    </div>
                                </div>
                                <span class="text-[11px] font-medium text-[#6B7280] shrink-0 tabular-nums">
                                    {{ $pinjam->tanggal_pinjam?->format('d/m/Y') }}
                                </span>
                            </div>

                            {{-- c. Box Rincian Alat --}}
                            <div class="mt-3 bg-[#F7F7F8] rounded-xl p-3 border border-[#E5E7EB]">
                                <div class="flex items-center justify-between text-[10px] font-bold tracking-wider text-[#6B7280] uppercase mb-1.5">
                                    <span>Rincian Alat</span>
                                    <span>{{ $totalUnits }} unit</span>
                                </div>

                                <ul class="space-y-1">
                                    @forelse ($pinjam->detailPeminjaman as $detail)
                                        <li class="flex items-center justify-between gap-2 text-xs">
                                            <span class="text-[#1F2937] font-medium truncate">
                                                {{ $detail->alat?->nama_alat ?? 'Alat tidak ditemukan' }}
                                            </span>
                                            <span class="shrink-0 px-1.5 py-0.5 rounded bg-white border border-[#E5E7EB] text-[11px] font-bold text-[#1F2937] tabular-nums">
                                                {{ $detail->jumlah_pinjam }}x
                                            </span>
                                        </li>
                                    @empty
                                        <li class="py-1 text-xs text-[#6B7280]">Belum ada rincian alat.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                        {{-- d. Card Footer: ID Transaksi & Tombol Aksi Pill Edit / Hapus --}}
                        <div class="mt-3.5 pt-2.5 border-t border-[#F3F4F6] flex items-center justify-between gap-2">
                            <span class="text-[11px] text-[#6B7280] font-medium tabular-nums">
                                ID #{{ $pinjam->id_peminjaman }}
                            </span>

                            <div class="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    data-trx='@json($trxPayload)'
                                    onclick="openEditModalFromButton(this)"
                                    class="h-7 px-3 rounded-full border border-[#E5E7EB] bg-white hover:bg-[#1F2937] hover:text-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition"
                                >
                                    Edit
                                </button>

                                <button
                                    type="button"
                                    data-trx='@json($trxPayload)'
                                    onclick="openDeleteModalFromButton(this)"
                                    class="h-7 px-3 rounded-full border border-[#E5E7EB] bg-white hover:bg-[#E53935] hover:text-white hover:border-[#E53935] text-xs font-semibold text-[#6B7280] transition"
                                >
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @else
        {{-- Empty State --}}
        <div class="bg-white rounded-[20px] border border-[#E5E7EB] p-12 text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#F7F7F8] border border-[#E5E7EB] text-[#6B7280] flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/>
                    <line x1="20" y1="20" x2="16.2" y2="16.2"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-[#1F2937]">Transaksi peminjaman tidak ditemukan</h3>
            <p class="text-sm text-[#6B7280] mt-1 max-w-md mx-auto">
                Tidak ada kode transaksi atau username peminjam yang cocok dengan kata kunci maupun filter status yang dipilih.
            </p>
            @if ($status !== 'semua' || $q !== '')
                <a
                    href="{{ route('admin.peminjaman.index') }}"
                    class="mt-4 inline-flex items-center h-9 px-5 rounded-full bg-[#1F2937] hover:bg-[#111827] text-white text-xs font-semibold transition"
                >
                    Tampilkan Semua Transaksi
                </a>
            @else
                <button
                    type="button"
                    onclick="openCreateModal()"
                    class="mt-4 inline-flex items-center h-9 px-5 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold transition"
                >
                    + Tambah Peminjaman Pertama
                </button>
            @endif
        </div>
    @endif

    {{-- Pagination --}}
    @if ($peminjamans->hasPages())
        <div class="mt-6">
            {{ $peminjamans->links() }}
        </div>
    @endif

    {{-- ==================================================================
         MODAL FORM EDIT & TAMBAH PEMINJAMAN (max-w-[820px] max-h-[92vh])
         ================================================================== --}}
    <div
        id="form-modal"
        class="no-print fixed inset-0 z-50 hidden items-center justify-center bg-[#1F2937]/50 p-3 sm:p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="form-modal-title"
    >
        <div class="relative bg-white rounded-[20px] border border-[#E5E7EB] shadow-xl max-w-[820px] w-full max-h-[92vh] flex flex-col overflow-hidden">
            {{-- Aksen Garis Merah Tipis di Atas Modal --}}
            <div class="h-1 w-full shrink-0 bg-[#E53935]"></div>

            {{-- Header Modal Sticky --}}
            <div class="px-5 py-3 border-b border-[#E5E7EB] flex items-center justify-between gap-3 shrink-0 bg-white">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h2 id="form-modal-title" class="text-base font-bold text-[#1F2937] tracking-tight">
                        Tambah Peminjaman
                    </h2>
                    <span id="form-modal-subtitle" class="px-2.5 py-0.5 rounded-full bg-[#FEF2F2] text-[#E53935] text-[10px] font-bold uppercase tracking-wider">
                        Transaksi Baru
                    </span>
                </div>
                <button
                    type="button"
                    onclick="closeFormModal()"
                    title="Tutup Form"
                    aria-label="Tutup Form"
                    class="w-7 h-7 rounded-full border border-[#E5E7EB] text-[#6B7280] hover:text-[#1F2937] hover:bg-[#F7F7F8] flex items-center justify-center transition shrink-0"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            {{-- Form Body Auto-Scroll & Sticky Footer --}}
            <form id="transaction-form" method="POST" action="{{ route('admin.peminjaman.store') }}" class="flex flex-col flex-1 min-h-0">
                @csrf
                <input type="hidden" name="_method" id="form-method-input" value="PUT" disabled>
                <input type="hidden" name="_modal_mode" id="form-modal-mode" value="create">
                <input type="hidden" name="_edit_id" id="form-edit-id" value="">

                <div id="form-modal-body" class="px-5 py-3.5 space-y-3 overflow-y-auto flex-1">
                    @if ($reopenModal)
                        <div class="rounded-xl bg-[#FEF2F2] border border-[#FECACA] px-3.5 py-2.5 text-xs text-[#DC2626]">
                            @if (session('error'))
                                <p class="font-semibold">{{ session('error') }}</p>
                            @endif
                            @if ($errors->any())
                                <p class="font-semibold mb-1">Periksa kembali isian form:</p>
                                <ul class="list-disc list-inside space-y-0.5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif

                    {{-- Info Sinkronisasi Stok Otomatis --}}
                    <div class="bg-[#F7F7F8] border border-[#E5E7EB] rounded-xl px-3.5 py-2 text-[11px] text-[#6B7280] flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-[#2563EB] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        <span>
                            <strong class="font-semibold text-[#1F2937]">Sinkronisasi Stok Otomatis:</strong>
                            Hanya status <span class="font-semibold text-[#2563EB]">Disetujui</span> yang mengurangi
                            <code class="font-mono text-[10px] bg-white border border-[#E5E7EB] text-[#1F2937] px-1 py-0.5 rounded">jumlah_tersedia</code>;
                            status lainnya tidak menahan stok.
                        </span>
                    </div>

                    {{-- Grid 4 Kolom Sejajar --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                        {{-- Peminjam --}}
                        <div>
                            <label for="form-peminjam" class="block text-[11px] font-semibold text-[#1F2937] mb-1">Peminjam</label>
                            <select
                                id="form-peminjam"
                                name="id_user"
                                required
                                class="w-full rounded-xl border border-[#E5E7EB] bg-white px-2.5 py-2 text-xs font-medium text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
                            >
                                <option value="">Pilih peminjam</option>
                                @foreach ($peminjams as $u)
                                    <option value="{{ $u->id_user }}">{{ $u->username }} ({{ $u->role }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Approver (Opsional) --}}
                        <div>
                            <label for="form-approver" class="block text-[11px] font-semibold text-[#1F2937] mb-1">
                                Approver <span class="font-normal text-[#6B7280]">(Opsional)</span>
                            </label>
                            <select
                                id="form-approver"
                                name="id_petugas"
                                class="w-full rounded-xl border border-[#E5E7EB] bg-white px-2.5 py-2 text-xs font-medium text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
                            >
                                <option value="">Belum ada</option>
                                @foreach ($petugass as $ptg)
                                    <option value="{{ $ptg->id_user }}">{{ $ptg->username }} ({{ $ptg->role }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tanggal Pinjam --}}
                        <div>
                            <label for="form-tanggal" class="block text-[11px] font-semibold text-[#1F2937] mb-1">Tanggal Pinjam</label>
                            <input
                                id="form-tanggal"
                                type="date"
                                name="tanggal_pinjam"
                                value="{{ today()->toDateString() }}"
                                required
                                class="w-full rounded-xl border border-[#E5E7EB] bg-white px-2.5 py-2 text-xs font-medium text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
                            >
                        </div>

                        {{-- Status Peminjaman --}}
                        <div>
                            <label for="form-status" class="block text-[11px] font-semibold text-[#1F2937] mb-1">Status Peminjaman</label>
                            <select
                                id="form-status"
                                name="status"
                                required
                                class="w-full rounded-xl border border-[#E5E7EB] bg-white px-2.5 py-2 text-xs font-medium text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
                            >
                                <option value="menunggu">Menunggu</option>
                                <option value="disetujui">Disetujui</option>
                                <option value="selesai">Selesai</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>
                    </div>

                    {{-- Section Rincian Alat Dipinjam (2 Kolom Dinamis) --}}
                    <div class="pt-2.5 border-t border-[#E5E7EB]">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2">
                                <h3 class="text-xs font-bold text-[#1F2937]">Rincian Alat Dipinjam</h3>
                                <span class="text-[11px] text-[#6B7280] hidden sm:inline">&bull; Pilih alat &amp; jumlah unit</span>
                            </div>
                            <button
                                type="button"
                                onclick="addToolRow()"
                                class="inline-flex items-center gap-1 px-3 py-1 rounded-full border border-[#E53935] bg-white hover:bg-[#FEF2F2] text-[#E53935] text-[11px] font-semibold transition shrink-0"
                            >
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <line x1="12" y1="5" x2="12" y2="19"/>
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                </svg>
                                <span>Tambah Baris</span>
                            </button>
                        </div>

                        <div id="tool-rows-container" class="grid grid-cols-1 md:grid-cols-2 gap-2"></div>
                    </div>
                </div>

                {{-- Footer Modal Sticky --}}
                <div class="px-5 py-2.5 bg-[#F7F7F8] border-t border-[#E5E7EB] flex items-center justify-end gap-2 shrink-0">
                    <button
                        type="button"
                        onclick="closeFormModal()"
                        class="h-8 px-4 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        id="form-submit-btn"
                        class="h-8 px-5 rounded-full bg-[#E53935] hover:bg-[#C62828] text-xs font-semibold text-white transition"
                    >
                        Simpan Peminjaman
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================================================================
         MODAL KONFIRMASI HAPUS TRANSAKSI (Tanpa browser confirm/alert)
         ================================================================== --}}
    <div
        id="delete-modal"
        class="no-print fixed inset-0 z-[100] hidden items-center justify-center bg-[#1F2937]/45 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="delete-modal-title"
    >
        <div class="lp-modal-pop relative bg-white rounded-3xl border border-[#E5E7EB] shadow-2xl max-w-[360px] w-full px-6 pt-6 pb-5 text-center">
            <button
                type="button"
                onclick="closeDeleteModal()"
                aria-label="Tutup Konfirmasi Hapus"
                class="absolute top-3.5 right-3.5 w-8 h-8 rounded-full text-[#6B7280] hover:text-[#1F2937] hover:bg-[#F7F7F8] flex items-center justify-center transition cursor-pointer"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>

            <div class="mx-auto w-28 h-24 flex items-center justify-center" aria-hidden="true">
                <svg viewBox="0 0 140 112" class="w-28 h-24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="70" cy="56" r="42" fill="#FEF2F2"/>
                    <circle cx="40" cy="40" r="14" fill="#FEE2E2" opacity="0.7"/>
                    <circle cx="100" cy="64" r="13" fill="#FEE2E2" opacity="0.7"/>
                    <path d="M24 68C24 63.6 27.6 60 32 60H34C35.8 56.4 39.6 54 44 54C49.5 54 54 58.5 54 64C54 68.4 50.4 72 46 72H30C26.7 72 24 69.3 24 68Z" fill="#FFFFFF" stroke="#1F2937" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M92 36C92 32.7 94.7 30 98 30H100C101.6 27.6 104.3 26 107.5 26C112.2 26 116 29.8 116 34.5C116 38.1 113.1 41 109.5 41H97C94.2 41 92 38.8 92 36Z" fill="#FFFFFF" stroke="#1F2937" stroke-width="2" stroke-linejoin="round"/>
                    <rect x="61" y="20" width="18" height="7" rx="3" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.4"/>
                    <rect x="44" y="27" width="52" height="10" rx="4" fill="#E53935" stroke="#1F2937" stroke-width="2.4"/>
                    <path d="M50 37H90L85.5 85C85.1 88.4 82.3 91 78.9 91H61.1C57.7 91 54.9 88.4 54.5 85L50 37Z" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.4" stroke-linejoin="round"/>
                    <path d="M53 48H87L84.5 85C84.2 87.2 82.3 89 80 89H60C57.7 89 55.8 87.2 55.5 85L53 48Z" fill="#FEF2F2"/>
                    <path d="M62 48V78M70 48V78M78 48V78" stroke="#E53935" stroke-width="2.4" stroke-linecap="round"/>
                    <path d="M35 30L37 32M37 30L35 32" stroke="#E53935" stroke-width="1.8" stroke-linecap="round"/>
                    <circle cx="105" cy="78" r="2.2" fill="#E53935"/>
                </svg>
            </div>

            <h3 id="delete-modal-title" class="mt-2 text-base font-bold text-[#1F2937] leading-snug">Hapus Transaksi?</h3>

            <p id="delete-modal-desc" class="mt-2 text-xs text-[#6B7280] leading-relaxed"></p>

            <p id="delete-stock-note" class="hidden mt-3 rounded-xl bg-[#FEF2F2] border border-[#FECACA] px-3 py-2 text-[11px] text-[#991B1B] leading-relaxed text-left">
                Transaksi ini berstatus <strong>Disetujui</strong>. Menghapus transaksi akan otomatis mengembalikan seluruh stok alat ke <code class="font-mono text-[10px]">jumlah_tersedia</code>.
            </p>

            <form id="delete-form" method="POST" action="" class="mt-5 flex flex-col items-center gap-2">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="min-w-[168px] h-10 px-6 rounded-full border-[1.5px] border-[#E53935] bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-bold shadow-sm transition cursor-pointer"
                >
                    Ya, Hapus Data
                </button>
                <button
                    type="button"
                    onclick="closeDeleteModal()"
                    class="px-4 py-1 text-xs font-semibold text-[#6B7280] hover:text-[#1F2937] transition cursor-pointer"
                >
                    Batal
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const alatCatalog = @json($alatCatalog);
            const storeUrl = @json(route('admin.peminjaman.store'));
            const updateBaseUrl = @json(url('/admin/peminjaman'));
            const todayStr = @json(today()->toDateString());
            const defaultPeminjamId = @json((string) ($peminjams->first()?->id_user ?? ''));

            let draftRows = [];

            const formModal = document.getElementById('form-modal');
            const formModalBody = document.getElementById('form-modal-body');
            const transactionForm = document.getElementById('transaction-form');
            const methodInput = document.getElementById('form-method-input');
            const modeInput = document.getElementById('form-modal-mode');
            const editIdInput = document.getElementById('form-edit-id');
            const modalTitle = document.getElementById('form-modal-title');
            const modalSubtitle = document.getElementById('form-modal-subtitle');
            const submitBtn = document.getElementById('form-submit-btn');

            const selectPeminjam = document.getElementById('form-peminjam');
            const selectApprover = document.getElementById('form-approver');
            const inputTanggal = document.getElementById('form-tanggal');
            const selectStatus = document.getElementById('form-status');
            const rowsContainer = document.getElementById('tool-rows-container');

            const deleteModal = document.getElementById('delete-modal');
            const deleteForm = document.getElementById('delete-form');
            const deleteTitle = document.getElementById('delete-modal-title');
            const deleteDesc = document.getElementById('delete-modal-desc');
            const deleteStockNote = document.getElementById('delete-stock-note');

            function ensureOptionExists(selectEl, value, label) {
                if (!value) return;
                const valStr = String(value);
                const exists = Array.from(selectEl.options).some(opt => opt.value === valStr);
                if (!exists) {
                    const opt = document.createElement('option');
                    opt.value = valStr;
                    opt.textContent = label;
                    selectEl.appendChild(opt);
                }
            }

            function renderToolRows() {
                rowsContainer.innerHTML = '';

                if (draftRows.length === 0) {
                    draftRows.push({
                        id_alat: alatCatalog.length > 0 ? String(alatCatalog[0].id) : '',
                        jumlah_pinjam: 1,
                    });
                }

                const disableDelete = draftRows.length <= 1;

                draftRows.forEach(function (row, idx) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'flex items-center gap-1.5 bg-[#F7F7F8] border border-[#E5E7EB] rounded-xl p-1.5';

                    const selectWrap = document.createElement('div');
                    selectWrap.className = 'flex-1 min-w-0';

                    const select = document.createElement('select');
                    select.name = 'items[' + idx + '][id_alat]';
                    select.required = true;
                    select.setAttribute('aria-label', 'Alat Laboratorium');
                    select.className = 'w-full rounded-lg border border-[#E5E7EB] bg-white px-2.5 py-1.5 text-xs font-medium text-[#1F2937] focus:border-[#E53935] focus:outline-none transition';

                    const placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.textContent = 'Pilih alat';
                    select.appendChild(placeholder);

                    alatCatalog.forEach(function (alat) {
                        const opt = document.createElement('option');
                        opt.value = String(alat.id);
                        opt.textContent = alat.nama + ' (Stok: ' + alat.stok + ')';
                        if (String(row.id_alat) === String(alat.id)) {
                            opt.selected = true;
                        }
                        select.appendChild(opt);
                    });

                    select.addEventListener('change', function () {
                        draftRows[idx].id_alat = this.value;
                    });

                    selectWrap.appendChild(select);

                    const qtyInput = document.createElement('input');
                    qtyInput.type = 'number';
                    qtyInput.name = 'items[' + idx + '][jumlah_pinjam]';
                    qtyInput.min = '1';
                    qtyInput.max = '10000';
                    qtyInput.step = '1';
                    qtyInput.required = true;
                    qtyInput.value = String(Math.max(1, parseInt(row.jumlah_pinjam, 10) || 1));
                    qtyInput.title = 'Jumlah Unit';
                    qtyInput.setAttribute('aria-label', 'Jumlah Unit');
                    qtyInput.className = 'w-16 shrink-0 text-center rounded-lg border border-[#E5E7EB] bg-white px-1.5 py-1.5 text-xs font-bold text-[#1F2937] focus:border-[#E53935] focus:outline-none transition';

                    qtyInput.addEventListener('input', function () {
                        const parsed = parseInt(this.value, 10);
                        draftRows[idx].jumlah_pinjam = !isNaN(parsed) && parsed > 0 ? parsed : 1;
                    });

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.title = disableDelete ? 'Minimal 1 baris alat' : 'Hapus Baris';
                    removeBtn.disabled = disableDelete;
                    removeBtn.textContent = 'Hapus';
                    removeBtn.className = disableDelete
                        ? 'shrink-0 px-2.5 py-1.5 rounded-lg border border-[#E5E7EB] bg-white text-[11px] font-semibold text-[#9CA3AF] opacity-60 cursor-not-allowed'
                        : 'shrink-0 px-2.5 py-1.5 rounded-lg border border-[#E5E7EB] bg-white hover:border-[#FECACA] hover:bg-[#FEF2F2] hover:text-[#DC2626] text-[11px] font-semibold text-[#6B7280] transition';

                    removeBtn.addEventListener('click', function () {
                        if (draftRows.length <= 1) return;
                        draftRows.splice(idx, 1);
                        renderToolRows();
                    });

                    wrapper.appendChild(selectWrap);
                    wrapper.appendChild(qtyInput);
                    wrapper.appendChild(removeBtn);
                    rowsContainer.appendChild(wrapper);
                });
            }

            window.addToolRow = function () {
                const usedIds = draftRows.map(r => String(r.id_alat));
                const nextAlat = alatCatalog.find(a => !usedIds.includes(String(a.id))) || alatCatalog[0];
                draftRows.push({
                    id_alat: nextAlat ? String(nextAlat.id) : '',
                    jumlah_pinjam: 1,
                });
                renderToolRows();

                if (formModalBody) {
                    formModalBody.scrollTo({ top: formModalBody.scrollHeight, behavior: 'smooth' });
                }
            };

            function showFormModal() {
                formModal.classList.remove('hidden');
                formModal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }

            window.closeFormModal = function () {
                if (window.LpDropdown) window.LpDropdown.close();
                formModal.classList.add('hidden');
                formModal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            };

            window.openCreateModal = function () {
                transactionForm.action = storeUrl;
                methodInput.disabled = true;
                modeInput.value = 'create';
                editIdInput.value = '';

                modalTitle.textContent = 'Tambah Peminjaman';
                modalSubtitle.textContent = 'Transaksi Baru';
                submitBtn.textContent = 'Simpan Peminjaman';

                selectPeminjam.value = defaultPeminjamId;
                selectApprover.value = '';
                inputTanggal.value = todayStr;
                selectStatus.value = 'menunggu';

                draftRows = [
                    {
                        id_alat: alatCatalog.length > 0 ? String(alatCatalog[0].id) : '',
                        jumlah_pinjam: 1,
                    },
                ];
                renderToolRows();
                showFormModal();
            };

            window.openEditModalFromButton = function (btn) {
                const trx = JSON.parse(btn.getAttribute('data-trx'));
                openEditModal(trx);
            };

            function openEditModal(trx) {
                transactionForm.action = trx.update_url;
                methodInput.disabled = false;
                methodInput.value = 'PUT';
                modeInput.value = 'edit';
                editIdInput.value = String(trx.id);

                modalTitle.textContent = 'Edit Peminjaman ' + trx.code;
                modalSubtitle.textContent = 'Mode Edit Transaksi';
                submitBtn.textContent = 'Simpan Perubahan';

                ensureOptionExists(selectPeminjam, trx.id_user, trx.username);
                selectPeminjam.value = String(trx.id_user || '');
                selectApprover.value = trx.id_petugas ? String(trx.id_petugas) : '';
                inputTanggal.value = trx.tanggal_pinjam || todayStr;
                selectStatus.value = trx.status || 'menunggu';

                draftRows = Array.isArray(trx.items) && trx.items.length > 0
                    ? trx.items.map(item => ({
                        id_alat: String(item.id_alat),
                        jumlah_pinjam: parseInt(item.jumlah_pinjam, 10) || 1,
                    }))
                    : [{ id_alat: alatCatalog.length > 0 ? String(alatCatalog[0].id) : '', jumlah_pinjam: 1 }];

                renderToolRows();
                showFormModal();
            }

            window.openDeleteModalFromButton = function (btn) {
                const trx = JSON.parse(btn.getAttribute('data-trx'));
                deleteForm.action = trx.destroy_url;
                deleteTitle.textContent = 'Hapus Transaksi ' + trx.code + '?';

                deleteDesc.textContent = '';
                deleteDesc.appendChild(document.createTextNode('Apakah Anda yakin ingin menghapus data peminjaman '));
                const strongCode = document.createElement('strong');
                strongCode.className = 'text-[#1F2937] font-semibold';
                strongCode.textContent = trx.code;
                deleteDesc.appendChild(strongCode);
                deleteDesc.appendChild(document.createTextNode(' milik '));
                const strongUser = document.createElement('strong');
                strongUser.className = 'text-[#1F2937] font-semibold';
                strongUser.textContent = trx.username;
                deleteDesc.appendChild(strongUser);
                deleteDesc.appendChild(document.createTextNode('? Data yang dihapus tidak dapat dipulihkan.'));

                if (trx.status === 'disetujui') {
                    deleteStockNote.classList.remove('hidden');
                } else {
                    deleteStockNote.classList.add('hidden');
                }

                deleteModal.classList.remove('hidden');
                deleteModal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            };

            window.closeDeleteModal = function () {
                deleteModal.classList.add('hidden');
                deleteModal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            };

            // Tutup modal saat klik backdrop atau tekan Escape
            formModal.addEventListener('click', function (e) {
                if (e.target === formModal) closeFormModal();
            });

            deleteModal.addEventListener('click', function (e) {
                if (e.target === deleteModal) closeDeleteModal();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    if (!formModal.classList.contains('hidden')) closeFormModal();
                    if (!deleteModal.classList.contains('hidden')) closeDeleteModal();
                }
            });

            // Reopen otomatis jika submit modal gagal validasi / stok tidak cukup
            @if ($reopenModal)
                (function () {
                    const prevMode = @json($oldModalMode);
                    const prevEditId = @json($oldEditId);
                    const prevUser = @json((string) old('id_user', ''));
                    const prevApprover = @json((string) old('id_petugas', ''));
                    const prevTanggal = @json((string) old('tanggal_pinjam', today()->toDateString()));
                    const prevStatus = @json((string) old('status', 'menunggu'));
                    const prevItems = @json(is_array($oldItems) ? array_values($oldItems) : []);

                    if (prevMode === 'edit' && prevEditId) {
                        transactionForm.action = updateBaseUrl + '/' + prevEditId;
                        methodInput.disabled = false;
                        methodInput.value = 'PUT';
                        modeInput.value = 'edit';
                        editIdInput.value = String(prevEditId);
                        modalTitle.textContent = 'Edit Peminjaman #PMJ-' + prevEditId;
                        modalSubtitle.textContent = 'Mode Edit Transaksi';
                        submitBtn.textContent = 'Simpan Perubahan';
                    } else {
                        transactionForm.action = storeUrl;
                        methodInput.disabled = true;
                        modeInput.value = 'create';
                        editIdInput.value = '';
                        modalTitle.textContent = 'Tambah Peminjaman';
                        modalSubtitle.textContent = 'Transaksi Baru';
                        submitBtn.textContent = 'Simpan Peminjaman';
                    }

                    selectPeminjam.value = prevUser;
                    selectApprover.value = prevApprover;
                    inputTanggal.value = prevTanggal;
                    selectStatus.value = prevStatus;

                    draftRows = prevItems.length > 0
                        ? prevItems.map(item => ({
                            id_alat: String(item.id_alat || ''),
                            jumlah_pinjam: parseInt(item.jumlah_pinjam, 10) || 1,
                        }))
                        : [{ id_alat: alatCatalog.length > 0 ? String(alatCatalog[0].id) : '', jumlah_pinjam: 1 }];

                    renderToolRows();
                    showFormModal();
                })();
            @endif
        })();
    </script>
@endpush
