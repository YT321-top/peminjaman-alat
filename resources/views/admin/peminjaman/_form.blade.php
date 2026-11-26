@php
    $isEdit = isset($peminjaman) && $peminjaman !== null;

    $initialItems = old('items');
    if (!is_array($initialItems) || empty($initialItems)) {
        if ($isEdit && $peminjaman->detailPeminjaman->isNotEmpty()) {
            $initialItems = $peminjaman->detailPeminjaman->map(fn ($d) => [
                'id_alat' => $d->id_alat,
                'jumlah_pinjam' => $d->jumlah_pinjam,
            ])->values()->all();
        } else {
            $initialItems = [
                ['id_alat' => '', 'jumlah_pinjam' => 1],
            ];
        }
    }

    $alatOptions = $alats->map(fn ($a) => [
        'id' => $a->id_alat,
        'label' => $a->nama_alat . ' — ' . ($a->kategori?->nama_kategori ?? 'Umum') . ' (Stok: ' . $a->jumlah_tersedia . ' unit)',
        'stok' => (int) $a->jumlah_tersedia,
    ])->values()->all();
@endphp

@if ($errors->any())
    <div class="mb-5 rounded-lg bg-white border border-[#E5E7EB] border-l-4 border-l-[#DC2626] px-4 py-3">
        <p class="text-xs font-semibold text-[#DC2626] mb-1">Periksa kembali isian form:</p>
        <ul class="list-disc list-inside text-xs text-[#DC2626] space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-5 rounded-xl bg-[#F7F7F8] border border-[#E5E7EB] px-4 py-3 text-xs text-[#6B7280]">
    <strong class="font-semibold text-[#1F2937]">Sinkronisasi Stok Otomatis:</strong>
    Hanya transaksi berstatus <strong class="font-semibold text-[#2563EB]">Disetujui</strong> yang mengurangi <code class="text-[#1F2937]">jumlah_tersedia</code>. Status Menunggu, Ditolak, dan Selesai tidak menahan stok alat.
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label for="id_user" class="block text-xs font-semibold text-[#1F2937] mb-1.5">Peminjam</label>
        <select
            id="id_user"
            name="id_user"
            required
            class="w-full h-10 px-3.5 rounded-lg bg-white border border-[#E5E7EB] text-sm text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
        >
            <option value="">Pilih peminjam</option>
            @foreach ($peminjams as $u)
                <option value="{{ $u->id_user }}" {{ (string) old('id_user', $isEdit ? $peminjaman->id_user : '') === (string) $u->id_user ? 'selected' : '' }}>
                    {{ $u->username }} ({{ $u->role }})
                </option>
            @endforeach
        </select>
        @error('id_user')
            <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="id_petugas" class="block text-xs font-semibold text-[#1F2937] mb-1.5">
            Petugas Approver <span class="font-normal text-[#6B7280]">(Opsional)</span>
        </label>
        <select
            id="id_petugas"
            name="id_petugas"
            class="w-full h-10 px-3.5 rounded-lg bg-white border border-[#E5E7EB] text-sm text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
        >
            <option value="">Belum ada</option>
            @foreach ($petugass as $ptg)
                <option value="{{ $ptg->id_user }}" {{ (string) old('id_petugas', $isEdit ? $peminjaman->id_petugas : '') === (string) $ptg->id_user ? 'selected' : '' }}>
                    {{ $ptg->username }} ({{ $ptg->role }})
                </option>
            @endforeach
        </select>
        @error('id_petugas')
            <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tanggal_pinjam" class="block text-xs font-semibold text-[#1F2937] mb-1.5">Tanggal Pinjam</label>
        <input
            id="tanggal_pinjam"
            type="date"
            name="tanggal_pinjam"
            value="{{ old('tanggal_pinjam', $isEdit ? $peminjaman->tanggal_pinjam?->toDateString() : today()->toDateString()) }}"
            required
            class="w-full h-10 px-3.5 rounded-lg bg-white border border-[#E5E7EB] text-sm text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
        >
        @error('tanggal_pinjam')
            <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="block text-xs font-semibold text-[#1F2937] mb-1.5">Status Peminjaman</label>
        <select
            id="status"
            name="status"
            required
            class="w-full h-10 px-3.5 rounded-lg bg-white border border-[#E5E7EB] text-sm text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
        >
            @foreach (['menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'selesai' => 'Selesai'] as $val => $lbl)
                <option value="{{ $val }}" {{ old('status', $isEdit ? $peminjaman->status : 'menunggu') === $val ? 'selected' : '' }}>
                    {{ $lbl }}
                </option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- Daftar Baris Alat Dinamis --}}
<div class="mt-6 pt-5 border-t border-[#E5E7EB]">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
        <div>
            <h2 class="text-sm font-bold text-[#1F2937]">Rincian Alat Dipinjam</h2>
            <p class="text-xs text-[#6B7280]">Tambahkan satu atau beberapa jenis alat laboratorium beserta jumlah unitnya.</p>
        </div>
        <button
            type="button"
            id="btn-tambah-baris-alat"
            class="inline-flex items-center justify-center h-8 px-4 rounded-full border border-[#E53935] bg-white hover:bg-[#E53935] text-[#E53935] hover:text-white text-xs font-semibold transition self-start sm:self-auto"
        >
            + Tambah Baris Alat
        </button>
    </div>

    <div id="items-container" class="space-y-3">
        @foreach ($initialItems as $idx => $row)
            <div class="item-row grid grid-cols-1 sm:grid-cols-12 gap-3 items-end p-3.5 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB]">
                <div class="sm:col-span-7">
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-[#6B7280] mb-1">Alat Laboratorium</label>
                    <select
                        name="items[{{ $idx }}][id_alat]"
                        required
                        class="w-full h-10 px-3 rounded-lg bg-white border border-[#E5E7EB] text-xs text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
                    >
                        <option value="">Pilih alat</option>
                        @foreach ($alatOptions as $opt)
                            <option value="{{ $opt['id'] }}" {{ (string) ($row['id_alat'] ?? '') === (string) $opt['id'] ? 'selected' : '' }}>
                                {{ $opt['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-[#6B7280] mb-1">Jumlah Unit</label>
                    <input
                        type="number"
                        name="items[{{ $idx }}][jumlah_pinjam]"
                        value="{{ $row['jumlah_pinjam'] ?? 1 }}"
                        min="1"
                        max="10000"
                        step="1"
                        required
                        class="w-full h-10 px-3 rounded-lg bg-white border border-[#E5E7EB] text-xs font-semibold text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
                    >
                </div>

                <div class="sm:col-span-2 flex justify-end">
                    <button
                        type="button"
                        class="btn-hapus-baris h-10 w-full sm:w-auto px-3.5 rounded-lg border border-[#E5E7EB] bg-white hover:border-[#DC2626] hover:text-[#DC2626] text-xs font-semibold text-[#6B7280] transition"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="mt-6 pt-4 border-t border-[#E5E7EB] flex items-center justify-end gap-2.5">
    <a
        href="{{ route('admin.peminjaman.index') }}"
        class="inline-flex items-center h-10 px-5 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition"
    >
        Batal
    </a>
    <button
        type="submit"
        class="h-10 px-6 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold transition"
    >
        {{ $submitLabel }}
    </button>
</div>

@push('scripts')
    <script>
        (function () {
            const alatOptions = @json($alatOptions);
            const container = document.getElementById('items-container');
            const addBtn = document.getElementById('btn-tambah-baris-alat');
            let rowIndex = container.querySelectorAll('.item-row').length;

            function syncDeleteButtons() {
                const rows = container.querySelectorAll('.item-row');
                rows.forEach(function (row) {
                    const btn = row.querySelector('.btn-hapus-baris');
                    if (!btn) return;
                    if (rows.length <= 1) {
                        btn.disabled = true;
                        btn.classList.add('opacity-40', 'cursor-not-allowed');
                    } else {
                        btn.disabled = false;
                        btn.classList.remove('opacity-40', 'cursor-not-allowed');
                    }
                });
            }

            function createRow(index) {
                const wrapper = document.createElement('div');
                wrapper.className = 'item-row grid grid-cols-1 sm:grid-cols-12 gap-3 items-end p-3.5 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB]';

                // Kolom Pilih Alat
                const colAlat = document.createElement('div');
                colAlat.className = 'sm:col-span-7';
                const lblAlat = document.createElement('label');
                lblAlat.className = 'block text-[11px] font-semibold uppercase tracking-wider text-[#6B7280] mb-1';
                lblAlat.textContent = 'Alat Laboratorium';
                const select = document.createElement('select');
                select.name = 'items[' + index + '][id_alat]';
                select.required = true;
                select.className = 'w-full h-10 px-3 rounded-lg bg-white border border-[#E5E7EB] text-xs text-[#1F2937] focus:border-[#E53935] focus:outline-none transition';

                const defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.textContent = 'Pilih alat';
                select.appendChild(defaultOpt);

                alatOptions.forEach(function (opt) {
                    const option = document.createElement('option');
                    option.value = String(opt.id);
                    option.textContent = opt.label;
                    select.appendChild(option);
                });

                colAlat.appendChild(lblAlat);
                colAlat.appendChild(select);

                // Kolom Jumlah Unit
                const colQty = document.createElement('div');
                colQty.className = 'sm:col-span-3';
                const lblQty = document.createElement('label');
                lblQty.className = 'block text-[11px] font-semibold uppercase tracking-wider text-[#6B7280] mb-1';
                lblQty.textContent = 'Jumlah Unit';
                const input = document.createElement('input');
                input.type = 'number';
                input.name = 'items[' + index + '][jumlah_pinjam]';
                input.value = '1';
                input.min = '1';
                input.max = '10000';
                input.step = '1';
                input.required = true;
                input.className = 'w-full h-10 px-3 rounded-lg bg-white border border-[#E5E7EB] text-xs font-semibold text-[#1F2937] focus:border-[#E53935] focus:outline-none transition';
                colQty.appendChild(lblQty);
                colQty.appendChild(input);

                // Kolom Hapus
                const colDel = document.createElement('div');
                colDel.className = 'sm:col-span-2 flex justify-end';
                const delBtn = document.createElement('button');
                delBtn.type = 'button';
                delBtn.className = 'btn-hapus-baris h-10 w-full sm:w-auto px-3.5 rounded-lg border border-[#E5E7EB] bg-white hover:border-[#DC2626] hover:text-[#DC2626] text-xs font-semibold text-[#6B7280] transition';
                delBtn.textContent = 'Hapus';
                colDel.appendChild(delBtn);

                wrapper.appendChild(colAlat);
                wrapper.appendChild(colQty);
                wrapper.appendChild(colDel);
                return wrapper;
            }

            addBtn.addEventListener('click', function () {
                container.appendChild(createRow(rowIndex++));
                syncDeleteButtons();
            });

            container.addEventListener('click', function (event) {
                const btn = event.target.closest('.btn-hapus-baris');
                if (!btn || btn.disabled) return;
                const rows = container.querySelectorAll('.item-row');
                if (rows.length <= 1) return;
                btn.closest('.item-row').remove();
                syncDeleteButtons();
            });

            syncDeleteButtons();
        })();
    </script>
@endpush
