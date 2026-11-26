@php
    $entity = $previewEntity ?? 'alat';
    $initialMode = old('illustration_mode', $currentOverride['mode'] ?? 'auto');
    if (!in_array($initialMode, ['auto', 'manual_icon', 'upload'], true)) {
        $initialMode = 'auto';
    }
    $initialIcon = old('illustration_icon', $currentOverride['type'] ?? 'erlenmeyer');
    $existingUploadUrl = ($currentResolved['mode'] ?? '') === 'upload' ? ($currentResolved['url'] ?? null) : null;
@endphp

<aside class="lg:col-span-4 lp-card-3d bg-white border border-[#E5E7EB] rounded-2xl p-5 space-y-4">
    <div class="flex items-center justify-between gap-2">
        <span class="text-[11px] font-bold uppercase tracking-wider text-[#6B7280]">
            {{ $entity === 'kategori' ? 'Preview Kategori' : 'Preview Kartu Katalog' }}
        </span>
        <div class="flex items-center gap-1.5">
            @if ($entity === 'kategori' && isset($kategori))
                <span class="px-2 py-0.5 rounded-md bg-[#F7F7F8] border border-[#E5E7EB] text-[11px] font-semibold text-[#6B7280]">
                    ID #{{ $kategori->id_kategori }}
                </span>
            @elseif ($entity === 'alat' && isset($alat))
                <span class="px-2 py-0.5 rounded-md bg-[#F7F7F8] border border-[#E5E7EB] text-[11px] font-semibold text-[#6B7280]">
                    ALT-{{ str_pad((string) $alat->id_alat, 3, '0', STR_PAD_LEFT) }}
                </span>
            @endif
            <span id="pv-alat-mode-badge" class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#F3F4F6] text-[#6B7280]">
                Otomatis
            </span>
        </div>
    </div>

    @if ($entity === 'kategori')
        @php
            $jumlahAlatKat = isset($kategori) ? $kategori->alat()->count() : 0;
        @endphp
        <div class="p-3.5 rounded-xl bg-[#F7F7F8] border border-[#E5E7EB] flex items-center gap-3">
            <div id="pv-alat-illu-slot" class="w-11 h-11 rounded-xl bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 overflow-hidden p-1">
                <x-alat-illustration
                    :kategori-id="$kategori->id_kategori ?? null"
                    :kategori="old('nama_kategori', $kategori->nama_kategori ?? '')"
                    class="w-8 h-8"
                />
            </div>
            <div class="min-w-0">
                <p id="pv-kat-nama" class="text-sm font-bold text-[#1F2937] truncate">
                    {{ old('nama_kategori', $kategori->nama_kategori ?? '') ?: 'Nama Kategori Baru' }}
                </p>
                <p class="text-xs text-[#6B7280]">{{ number_format($jumlahAlatKat) }} alat terdaftar</p>
            </div>
        </div>
    @else
        <div class="bg-[#F7F7F8] rounded-xl h-36 border border-[#E5E7EB] flex items-center justify-center p-3 overflow-hidden">
            <div id="pv-alat-illu-slot" class="w-full h-full flex items-center justify-center">
                <x-alat-illustration
                    :alat-id="$alat->id_alat ?? null"
                    :kategori="$alat->kategori?->nama_kategori ?? ''"
                    :nama-alat="old('nama_alat', $alat->nama_alat ?? '')"
                    class="w-20 h-20"
                />
            </div>
        </div>

        <div>
            <p id="pv-alat-kat" class="text-xs text-[#6B7280]">
                {{ isset($alat) ? ($alat->kategori?->nama_kategori ?? 'Umum') : 'Kategori belum dipilih' }}
            </p>
            <p id="pv-alat-nama" class="text-sm font-bold text-[#1F2937] mt-0.5 break-words">
                {{ old('nama_alat', $alat->nama_alat ?? '') ?: 'Nama Alat Lab' }}
            </p>
        </div>

        <div class="pt-3 border-t border-[#F3F4F6] flex items-center justify-between">
            <span class="text-xs text-[#6B7280]">Status Stok</span>
            <span id="pv-alat-stok" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#E53935] text-white">
                Stok Habis
            </span>
        </div>
    @endif

    <div id="pv-alat-status-banner" class="text-[11px] leading-relaxed rounded-xl p-3 border bg-[#F7F7F8] border-[#E5E7EB] text-[#6B7280] flex items-start gap-2">
        <span id="pv-alat-status-dot" class="w-2 h-2 rounded-full mt-1 shrink-0 bg-[#9CA3AF]"></span>
        <span id="pv-alat-status-text">
            {{ $entity === 'kategori' ? 'Menunggu input nama kategori.' : 'Menunggu input nama alat.' }}
        </span>
    </div>
</aside>

@push('scripts')
    <script>
        (function () {
            const previewEntity = @json($entity);
            const payload = @json($illustrationConfig);
            const icons = Array.isArray(payload?.icons) ? payload.icons : [];
            const existingUploadUrl = @json($existingUploadUrl);

            const inNama = document.getElementById('nama_alat');
            const inKat = document.getElementById('id_kategori');
            const inStok = document.getElementById('jumlah_tersedia');
            const inKatNama = document.getElementById('nama_kategori');

            const inMode = document.getElementById('illustration_mode');
            const inIcon = document.getElementById('illustration_icon');
            const inFile = document.getElementById('illustration_file');

            const pvNama = document.getElementById('pv-alat-nama');
            const pvKat = document.getElementById('pv-alat-kat');
            const pvStok = document.getElementById('pv-alat-stok');
            const pvKatNama = document.getElementById('pv-kat-nama');

            const pvSlot = document.getElementById('pv-alat-illu-slot');
            const pvBadge = document.getElementById('pv-alat-mode-badge');
            const pvBanner = document.getElementById('pv-alat-status-banner');
            const pvDot = document.getElementById('pv-alat-status-dot');
            const pvText = document.getElementById('pv-alat-status-text');

            const panelManual = document.getElementById('illu-panel-manual');
            const panelUpload = document.getElementById('illu-panel-upload');
            const iconGrid = document.getElementById('illu-icon-grid');
            const iconSearch = document.getElementById('illu-icon-search');
            const uploadLabel = document.getElementById('illu-upload-label');

            const svgSizeClass = previewEntity === 'kategori' ? 'w-8 h-8' : 'w-20 h-20';
            const uploadImgClass = previewEntity === 'kategori'
                ? 'w-9 h-9 object-contain rounded-lg'
                : 'max-h-28 max-w-full object-contain rounded-lg';

            let currentGroup = 'all';
            let previewUploadUrl = existingUploadUrl;
            let lastRenderedKey = null;

            function setSlotContent(renderKey, html) {
                if (!pvSlot || lastRenderedKey === renderKey) return;
                lastRenderedKey = renderKey;
                pvSlot.innerHTML = html;
            }

            function findIcon(key) {
                return icons.find((ic) => ic.key === key) || icons[icons.length - 1] || null;
            }

            function hasWord(text, word) {
                const escaped = String(word).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                return new RegExp(`(^|[^a-z0-9])${escaped}([^a-z0-9]|$)`, 'i').test(text);
            }

            function resolveAutoAlat(namaRaw) {
                const nama = (namaRaw || '').trim().toLowerCase();
                if (!nama) {
                    return { key: 'default', status: 'empty', text: 'Menunggu nama alat diisi. Ilustrasi akan dipetakan otomatis saat mengetik.' };
                }

                for (const ic of icons) {
                    if (ic.key === 'default' || !Array.isArray(ic.keywords)) continue;
                    if (ic.keywords.some((kw) => hasWord(nama, kw))) {
                        return { key: ic.key, status: 'matched', text: `Cocok otomatis dengan ikon: ${ic.label}.` };
                    }
                }

                return {
                    key: 'default',
                    status: 'unmatched',
                    text: `"${namaRaw.trim()}" tidak cocok dengan instrumen lab mana pun — memakai ikon fallback netral (data tetap bisa disimpan atau pilih tab Pilih Ikon / Upload Gambar).`
                };
            }

            function resolveAutoKategori(katRaw) {
                const kat = (katRaw || '').trim().toLowerCase();
                if (!kat) {
                    return { key: 'default', status: 'empty', text: 'Menunggu nama kategori diisi. Ikon otomatis atau upload gambar dapat dipilih.' };
                }

                let matchedKey = 'default';
                if (kat.includes('kimia')) matchedKey = 'erlenmeyer';
                else if (kat.includes('fisika')) matchedKey = 'multimeter';
                else if (kat.includes('biologi')) matchedKey = 'mikroskop';
                else if (kat.includes('komputer') || kat.includes('jaringan')) matchedKey = 'jaringan';
                else if (kat.includes('multimedia')) matchedKey = 'proyektor';

                if (matchedKey !== 'default') {
                    const ic = findIcon(matchedKey);
                    return { key: matchedKey, status: 'matched', text: `Cocok otomatis dengan ikon kategori: ${ic ? ic.label : matchedKey}.` };
                }

                return {
                    key: 'default',
                    status: 'unmatched',
                    text: `"${katRaw.trim()}" memakai ikon netral — Anda bisa memilih ikon manual atau upload gambar kategori sendiri.`
                };
            }

            function renderSvg(inner) {
                return `<svg class="${svgSizeClass}" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">${inner}</svg>`;
            }

            function setStatusVisual(bgClass, dotClass, text, badgeLabel, badgeClass) {
                if (pvBanner) pvBanner.className = `text-[11px] leading-relaxed rounded-xl p-3 border flex items-start gap-2 ${bgClass}`;
                if (pvDot) pvDot.className = `w-2 h-2 rounded-full mt-1 shrink-0 ${dotClass}`;
                if (pvText) pvText.textContent = text;
                if (pvBadge) {
                    pvBadge.textContent = badgeLabel;
                    pvBadge.className = `px-2 py-0.5 rounded-full text-[10px] font-semibold ${badgeClass}`;
                }
            }

            function renderIconGrid() {
                if (!iconGrid) return;
                const q = (iconSearch?.value || '').trim().toLowerCase();
                const selectedKey = inIcon?.value || 'erlenmeyer';

                const filtered = icons.filter((ic) => {
                    const groupMatch = currentGroup === 'all' || ic.group === currentGroup;
                    const qMatch = !q || ic.label.toLowerCase().includes(q) || ic.key.toLowerCase().includes(q);
                    return groupMatch && qMatch;
                });

                if (filtered.length === 0) {
                    iconGrid.innerHTML = '<div class="col-span-full text-center text-xs text-[#6B7280] py-4">Ikon tidak ditemukan — coba kata lain atau gunakan Upload Gambar.</div>';
                    return;
                }

                iconGrid.innerHTML = '';
                filtered.forEach((ic) => {
                    const active = ic.key === selectedKey;
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = active
                        ? 'p-1.5 rounded-lg border border-[#E53935] bg-[#FEF2F2] flex flex-col items-center gap-1 cursor-pointer transition'
                        : 'p-1.5 rounded-lg border border-[#E5E7EB] bg-white hover:border-[#9CA3AF] flex flex-col items-center gap-1 cursor-pointer transition';

                    btn.innerHTML = `<div class="w-8 h-8 flex items-center justify-center"><svg class="w-8 h-8" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">${ic.svg}</svg></div><span class="text-[10px] font-semibold text-[#1F2937] truncate w-full text-center"></span>`;
                    btn.querySelector('span').textContent = ic.label;
                    btn.addEventListener('click', () => {
                        if (inIcon) inIcon.value = ic.key;
                        renderIconGrid();
                        syncPreview();
                    });
                    iconGrid.appendChild(btn);
                });
            }

            function updateGroupButtons() {
                document.querySelectorAll('.illu-grp-btn').forEach((btn) => {
                    const active = btn.getAttribute('data-illu-grp') === currentGroup;
                    btn.className = active
                        ? 'illu-grp-btn px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#1F2937] text-white cursor-pointer transition'
                        : 'illu-grp-btn px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-white text-[#6B7280] hover:text-[#1F2937] border border-[#E5E7EB] cursor-pointer transition';
                });
            }

            function updateModeUI(mode) {
                if (inMode) inMode.value = mode;
                document.querySelectorAll('.illu-mode-btn').forEach((btn) => {
                    const active = btn.getAttribute('data-illu-mode') === mode;
                    btn.className = active
                        ? 'illu-mode-btn py-1.5 px-2 rounded-lg text-xs font-bold bg-white text-[#E53935] shadow-sm transition cursor-pointer'
                        : 'illu-mode-btn py-1.5 px-2 rounded-lg text-xs font-semibold text-[#6B7280] hover:text-[#1F2937] transition cursor-pointer';
                });
                if (panelManual) panelManual.classList.toggle('hidden', mode !== 'manual_icon');
                if (panelUpload) panelUpload.classList.toggle('hidden', mode !== 'upload');
                if (mode === 'manual_icon') {
                    updateGroupButtons();
                    renderIconGrid();
                }
                syncPreview();
            }

            function syncPreview() {
                if (previewEntity === 'kategori') {
                    if (pvKatNama && inKatNama) {
                        pvKatNama.textContent = (inKatNama.value || '').trim() || 'Nama Kategori Baru';
                    }
                } else {
                    const namaRaw = inNama?.value || '';
                    if (pvNama) pvNama.textContent = namaRaw.trim() || 'Nama Alat Lab';

                    if (pvKat && inKat) {
                        const selected = inKat.options[inKat.selectedIndex];
                        pvKat.textContent = selected && selected.value !== '' ? selected.textContent.trim() : 'Kategori belum dipilih';
                    }

                    if (pvStok && inStok) {
                        const stok = Math.max(0, parseInt(inStok.value || '0', 10) || 0);
                        if (stok <= 0) {
                            pvStok.textContent = 'Stok Habis';
                            pvStok.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#E53935] text-white';
                        } else if (stok <= 3) {
                            pvStok.textContent = 'Menipis: ' + stok + ' unit';
                            pvStok.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#D97706] text-white';
                        } else {
                            pvStok.textContent = 'Tersedia: ' + stok + ' unit';
                            pvStok.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#16A34A] text-white';
                        }
                    }
                }

                const mode = inMode?.value || 'auto';
                if (mode === 'manual_icon') {
                    const ic = findIcon(inIcon?.value || 'erlenmeyer');
                    if (ic) setSlotContent('svg:' + ic.key, renderSvg(ic.svg));
                    setStatusVisual(
                        'bg-[#EFF6FF] border-[#BFDBFE] text-[#1E40AF]',
                        'bg-[#2563EB]',
                        `Ikon dipilih manual: ${ic ? ic.label : '-'}.`,
                        'Manual SVG',
                        'bg-[#DBEAFE] text-[#1E40AF]'
                    );
                    return;
                }

                if (mode === 'upload') {
                    if (previewUploadUrl) {
                        setSlotContent(
                            'upload:' + previewUploadUrl,
                            `<img src="${previewUploadUrl}" alt="Preview upload" class="${uploadImgClass}">`
                        );
                        setStatusVisual(
                            'bg-[#F7F7F8] border-[#E5E7EB] text-[#1F2937]',
                            'bg-[#1F2937]',
                            previewEntity === 'kategori'
                                ? 'Gambar upload kustom siap ditampilkan pada kartu kategori.'
                                : 'Gambar upload kustom siap ditampilkan di seluruh katalog.',
                            'Upload',
                            'bg-[#1F2937] text-white'
                        );
                    } else {
                        const fallback = findIcon('default');
                        if (fallback) setSlotContent('svg:default', renderSvg(fallback.svg));
                        setStatusVisual(
                            'bg-[#FFFBEB] border-[#FDE68A] text-[#92400E]',
                            'bg-[#D97706]',
                            'Pilih file PNG / JPG / WebP di formulir untuk mode Upload Gambar.',
                            'Upload',
                            'bg-[#FEF3C7] text-[#92400E]'
                        );
                    }
                    return;
                }

                const auto = previewEntity === 'kategori'
                    ? resolveAutoKategori(inKatNama?.value || '')
                    : resolveAutoAlat(inNama?.value || '');
                const ic = findIcon(auto.key);
                if (ic) setSlotContent('svg:' + ic.key, renderSvg(ic.svg));

                if (auto.status === 'matched') {
                    setStatusVisual(
                        'bg-[#F0FDF4] border-[#BBF7D0] text-[#166534]',
                        'bg-[#16A34A]',
                        auto.text,
                        'Terpetakan',
                        'bg-[#DCFCE7] text-[#166534]'
                    );
                } else if (auto.status === 'unmatched') {
                    setStatusVisual(
                        'bg-[#FFFBEB] border-[#FDE68A] text-[#92400E]',
                        'bg-[#D97706]',
                        auto.text,
                        'Fallback Netral',
                        'bg-[#FEF3C7] text-[#92400E]'
                    );
                } else {
                    setStatusVisual(
                        'bg-[#F7F7F8] border-[#E5E7EB] text-[#6B7280]',
                        'bg-[#9CA3AF]',
                        auto.text,
                        'Otomatis',
                        'bg-[#F3F4F6] text-[#6B7280]'
                    );
                }
            }

            document.querySelectorAll('.illu-mode-btn').forEach((btn) => {
                btn.addEventListener('click', () => updateModeUI(btn.getAttribute('data-illu-mode')));
            });

            document.querySelectorAll('.illu-grp-btn').forEach((btn) => {
                btn.addEventListener('click', () => {
                    currentGroup = btn.getAttribute('data-illu-grp') || 'all';
                    updateGroupButtons();
                    renderIconGrid();
                });
            });

            if (iconSearch) iconSearch.addEventListener('input', renderIconGrid);

            if (inFile) {
                inFile.addEventListener('change', (e) => {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;
                    if (uploadLabel) uploadLabel.textContent = file.name;
                    const reader = new FileReader();
                    reader.onload = (ev) => {
                        previewUploadUrl = ev.target?.result || null;
                        syncPreview();
                    };
                    reader.readAsDataURL(file);
                });
            }

            if (inNama) inNama.addEventListener('input', syncPreview);
            if (inKat) inKat.addEventListener('change', syncPreview);
            if (inStok) inStok.addEventListener('input', syncPreview);
            if (inKatNama) inKatNama.addEventListener('input', syncPreview);

            updateModeUI(inMode?.value || 'auto');
        })();
    </script>
@endpush
