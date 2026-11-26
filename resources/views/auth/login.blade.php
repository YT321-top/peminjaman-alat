@php
    $hasError = $errors->any() || session()->has('error');
    $errorMessage = $errors->first() ?: session('error');
    $statusMessage = session('status');
    $activeRoleTab = in_array(old('role_tab'), ['peminjam', 'petugas', 'admin'], true)
        ? old('role_tab')
        : 'peminjam';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - LabPinjam | Inventaris Lab Sekolah</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    </noscript>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Animasi Getar saat Validasi Gagal */
        @keyframes cardShake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }

        /* Animasi Melayang Lembut untuk Karakter & Partikel Lab */
        @keyframes characterFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
        }

        @keyframes badgeOrbitA {
            0%, 100% { transform: translate(0px, 0px) rotate(0deg); }
            50% { transform: translate(4px, -7px) rotate(3deg); }
        }

        @keyframes badgeOrbitB {
            0%, 100% { transform: translate(0px, 0px) rotate(0deg); }
            50% { transform: translate(-5px, -6px) rotate(-3deg); }
        }

        @keyframes bubbleRise {
            0% { transform: translateY(0) scale(0.85); opacity: 0; }
            30% { opacity: 0.9; }
            100% { transform: translateY(-22px) scale(1.1); opacity: 0; }
        }

        /* Animasi Garis Lengkungan Bawah Judul */
        @keyframes drawUnderline {
            from { stroke-dashoffset: 220; }
            to { stroke-dashoffset: 0; }
        }

        .animate-shake {
            animation: cardShake 0.4s ease-in-out;
        }

        .animate-character-float {
            animation: characterFloat 4.6s ease-in-out infinite;
        }

        .animate-orbit-a {
            animation: badgeOrbitA 4.2s ease-in-out infinite;
        }

        .animate-orbit-b {
            animation: badgeOrbitB 4.8s ease-in-out infinite;
        }

        .animate-bubble-1 {
            animation: bubbleRise 2.8s ease-in infinite;
        }

        .animate-bubble-2 {
            animation: bubbleRise 3.4s ease-in 1.1s infinite;
        }

        .underline-path {
            stroke-dasharray: 220;
            stroke-dashoffset: 0;
        }

        .underline-path.replay-draw {
            animation: drawUnderline 0.65s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        /* Tekstur Bintik Halus pada Panel Gradien Kiri */
        .gradient-speckle {
            background-image: radial-gradient(rgba(255, 255, 255, 0.16) 1px, transparent 0);
            background-size: 22px 22px;
        }

        /* Transisi Mulus Stage Ilustrasi & Teks */
        .role-stage-item {
            transition:
                opacity 0.52s cubic-bezier(0.22, 1, 0.36, 1),
                transform 0.58s cubic-bezier(0.22, 1, 0.36, 1);
            will-change: opacity, transform;
        }

        .role-text-transition {
            transition:
                opacity 0.32s cubic-bezier(0.22, 1, 0.36, 1),
                transform 0.38s cubic-bezier(0.22, 1, 0.36, 1);
            will-change: opacity, transform;
        }

        /* Penyesuaian Tinggi Otomatis agar Card Selalu Muat Penuh Tanpa Scroll di Layar Desktop/Laptop */
        #visual-showcase {
            height: 350px;
        }
        @media (min-width: 640px) {
            #visual-showcase {
                height: 400px;
            }
        }
        @media (min-width: 768px) {
            #visual-showcase {
                height: min(525px, calc(100vh - 64px));
                min-height: 468px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .animate-character-float,
            .animate-orbit-a,
            .animate-orbit-b,
            .animate-bubble-1,
            .animate-bubble-2,
            .animate-shake,
            .underline-path.replay-draw {
                animation: none !important;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-[#edf1f7] text-slate-800 font-sans antialiased selection:bg-brand-600 selection:text-white flex items-center justify-center p-2.5 sm:p-4 lg:px-6 lg:py-3">

    <!-- Toast Notification Container (Pengganti alert) -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <!-- Modal Info Bantuan Akun & Lupa Kata Sandi (Pengganti alert/confirm) -->
    <div id="help-modal" onclick="handleModalBackdrop(event)" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/45 backdrop-blur-[2px] p-4" role="dialog" aria-modal="true" aria-labelledby="help-modal-title">
        <div class="bg-white rounded-[28px] border border-slate-200/80 shadow-2xl max-w-md w-full p-6 sm:p-7 transform transition-all">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center">
                        <svg id="help-modal-icon-key" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M2.586 17.414A2 2 0 0 0 2 18.828V21a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h1a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h.172a2 2 0 0 0 1.414-.586l.814-.814a6.5 6.5 0 1 0-4-4z"/>
                            <circle cx="16.5" cy="7.5" r=".5" fill="currentColor"/>
                        </svg>
                        <svg id="help-modal-icon-user" class="w-4 h-4 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <line x1="19" x2="19" y1="8" y2="14"/>
                            <line x1="22" x2="16" y1="11" y2="11"/>
                        </svg>
                    </span>
                    <h3 id="help-modal-title" class="text-base font-extrabold text-slate-900">Lupa Kata Sandi?</h3>
                </div>
                <button type="button" onclick="closeHelpModal()" aria-label="Tutup modal" class="w-8 h-8 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18"/>
                        <path d="m6 6 12 12"/>
                    </svg>
                </button>
            </div>
            <div id="help-modal-body" class="py-4 space-y-2.5 text-sm text-slate-600 leading-relaxed">
                <p>
                    Pengaturan ulang kata sandi dilakukan langsung oleh <strong class="text-slate-900">Admin Laboratorium</strong> untuk menjaga keamanan inventaris sekolah.
                </p>
                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 text-xs text-slate-600">
                    Silakan temui petugas laboran di ruang <strong class="text-slate-800">Lab Utama</strong> dengan menyertakan NIS/NIP dan username Anda.
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 flex justify-end">
                <button type="button" onclick="closeHelpModal()" class="px-6 py-2.5 rounded-full bg-brand-600 hover:bg-brand-700 text-xs font-bold text-white shadow-sm transition-colors">
                    Mengerti
                </button>
            </div>
        </div>
    </div>

    <!-- ==================================================================
         KARTU KONTAINER UTAMA (SPLIT CARD UI KIT STYLE)
         ================================================================== -->
    <main id="login-shell" class="w-full max-w-[1160px] bg-white rounded-[26px] sm:rounded-[34px] shadow-[0_24px_64px_-16px_rgba(15,23,42,0.12)] border border-white p-3 sm:p-4 grid grid-cols-1 md:grid-cols-12 gap-4 items-stretch {{ $hasError ? 'animate-shake' : '' }}">

        <!-- ==============================================================
             PANEL KIRI: STAGE DINAMIS PER ROLE (FIT FULL TANPA SCROLL)
             ============================================================== -->
        <section id="visual-showcase" class="md:col-span-6 relative rounded-[20px] sm:rounded-[26px] text-white overflow-hidden flex flex-col justify-between select-none">

            <!-- Lapisan Gradien Latar Belakang per Role (Crossfade Opacity Mulus 700ms) -->
            <div id="bg-peminjam" class="absolute inset-0 bg-gradient-to-br from-brand-600 via-brand-500 to-rose-400 transition-opacity duration-700 ease-out opacity-100"></div>
            <div id="bg-petugas" class="absolute inset-0 bg-gradient-to-br from-amber-600 via-orange-500 to-brand-500 transition-opacity duration-700 ease-out opacity-0"></div>
            <div id="bg-admin" class="absolute inset-0 bg-gradient-to-br from-slate-900 via-brand-700 to-rose-500 transition-opacity duration-700 ease-out opacity-0"></div>

            <!-- Tekstur Bintik & Pendaran Cahaya Hangat di Bagian Bawah -->
            <div class="absolute inset-0 gradient-speckle pointer-events-none"></div>
            <div class="absolute -bottom-16 left-1/2 -translate-x-1/2 w-[125%] h-56 bg-gradient-to-t from-amber-100/50 via-white/15 to-transparent blur-xl pointer-events-none"></div>

            <!-- Indikator Pill Peran di Pojok Kiri Atas Panel -->
            <div class="relative z-20 px-6 sm:px-8 pt-5 sm:pt-6 flex items-center justify-between gap-2">
                <span id="showcase-pill" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/[0.18] backdrop-blur-[2px] border border-white/25 text-[11px] font-bold tracking-wide uppercase shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                    <span id="showcase-pill-text">Akses Peminjam • Siswa</span>
                </span>

                <!-- Navigasi Cepat Dot Indicator di Panel Kiri -->
                <div class="flex items-center gap-1.5">
                    <button type="button" onclick="switchRole('peminjam')" aria-label="Tampilan Peminjam" id="dot-peminjam" class="h-1.5 w-6 rounded-full bg-white transition-all duration-300"></button>
                    <button type="button" onclick="switchRole('petugas')" aria-label="Tampilan Petugas" id="dot-petugas" class="h-1.5 w-2 rounded-full bg-white/45 hover:bg-white/75 transition-all duration-300"></button>
                    <button type="button" onclick="switchRole('admin')" aria-label="Tampilan Admin" id="dot-admin" class="h-1.5 w-2 rounded-full bg-white/45 hover:bg-white/75 transition-all duration-300"></button>
                </div>
            </div>

            <!-- Teks Headline & Subjudul Dinamis dengan Transisi Halus -->
            <div id="showcase-copy" class="relative z-20 px-6 sm:px-7 lg:px-8 pt-2 sm:pt-3 role-text-transition">
                <h1 class="text-[24px] sm:text-[27px] lg:text-[34px] font-extrabold leading-[1.13] tracking-tight">
                    <span id="headline-line1" class="block">Kelola &amp; pinjam</span>
                    <span id="headline-line2" class="block">alat sekolah di</span>
                    <span class="relative inline-block">
                        <span id="headline-accent">dashboard.</span>
                        <!-- Garis Lengkungan Putih Animasi di Bawah Kata Terakhir -->
                        <svg class="w-full h-3.5 -mt-0.5 text-white/95 overflow-visible" viewBox="0 0 210 14" fill="none" preserveAspectRatio="none" aria-hidden="true">
                            <path id="headline-underline" class="underline-path replay-draw" d="M3 10.5C68 3.2 142 2.8 207 9.5" stroke="currentColor" stroke-width="3.2" stroke-linecap="round"/>
                        </svg>
                    </span>
                </h1>

                <p id="showcase-subtitle" class="mt-2 text-xs sm:text-[13px] text-white/90 font-medium leading-relaxed max-w-[390px]">
                    Sederhanakan peminjaman peralatan laboratorium sekolah dengan inventaris yang rapi, cepat, dan terpantau.
                </p>
            </div>

            <!-- ==========================================================
                 STAGE ILUSTRASI 3 PERAN (TUMPUK ABSOLUT AGAR PERPINDAHAN PAS)
                 ========================================================== -->
            <div id="parallax-stage" class="relative z-10 flex-1 w-full min-h-[170px] sm:min-h-[210px] mt-0.5">

                <!-- 1. ILUSTRASI PEMINJAM: DUA SISWA PRAKTIKAN DENGAN TABUNG REAKSI & BUKU LAB -->
                <div id="illus-peminjam" class="role-stage-item absolute inset-x-0 bottom-0 flex justify-center items-end px-4 opacity-100 translate-x-0 scale-100">
                    <svg viewBox="0 0 380 255" class="w-[240px] sm:w-[270px] lg:w-[325px] h-auto drop-shadow-[0_14px_24px_rgba(127,29,29,0.25)] animate-character-float" fill="none" aria-hidden="true">
                        <defs>
                            <linearGradient id="pSkin" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#FCD5B5"/>
                                <stop offset="100%" stop-color="#EAB38F"/>
                            </linearGradient>
                            <linearGradient id="pVest" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0%" stop-color="#E2D9CE"/>
                                <stop offset="100%" stop-color="#BFB3A4"/>
                            </linearGradient>
                            <linearGradient id="pShirt" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#F8FAFC"/>
                                <stop offset="100%" stop-color="#CBD5E1"/>
                            </linearGradient>
                            <linearGradient id="pPants" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#9A8463"/>
                                <stop offset="100%" stop-color="#766246"/>
                            </linearGradient>
                            <linearGradient id="pYellow" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0%" stop-color="#FEF08A"/>
                                <stop offset="100%" stop-color="#EAB308"/>
                            </linearGradient>
                            <linearGradient id="pWhitePants" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#F8FAFC"/>
                                <stop offset="100%" stop-color="#CBD5E1"/>
                            </linearGradient>
                        </defs>

                        <!-- Floating Badge Kiri: Katalog Alat -->
                        <g class="animate-orbit-a">
                            <rect x="24" y="54" width="54" height="34" rx="11" fill="#FFFFFF" fill-opacity="0.95"/>
                            <circle cx="40" cy="71" r="8" fill="#FEE2E2"/>
                            <path d="M38 67h4v3l2.5 4.5a1 1 0 0 1-.9 1.5h-7.2a1 1 0 0 1-.9-1.5L38 70v-3z" fill="#DC2626"/>
                            <rect x="53" y="66" width="16" height="3.5" rx="1.75" fill="#1E293B"/>
                            <rect x="53" y="73" width="11" height="3" rx="1.5" fill="#94A3B8"/>
                        </g>

                        <!-- Floating Badge Kanan: Molekul Sains -->
                        <g class="animate-orbit-b">
                            <circle cx="332" cy="68" r="19" fill="#FFFFFF" fill-opacity="0.22" stroke="#FFFFFF" stroke-opacity="0.45" stroke-width="1.5"/>
                            <circle cx="332" cy="68" r="4" fill="#FFFFFF"/>
                            <ellipse cx="332" cy="68" rx="13" ry="5" transform="rotate(30 332 68)" stroke="#FFFFFF" stroke-width="1.6"/>
                            <ellipse cx="332" cy="68" rx="13" ry="5" transform="rotate(-30 332 68)" stroke="#FFFFFF" stroke-width="1.6"/>
                        </g>

                        <!-- Karakter Siswa Pria (Kiri) -->
                        <g>
                            <path d="M92 178h46l5 77H87l5-77z" fill="url(#pPants)"/>
                            <line x1="115" y1="194" x2="115" y2="255" stroke="#5C4B34" stroke-width="2.2" stroke-linecap="round"/>
                            <path d="M84 106c-12 12-17 30-14 44 2 9 11 13 21 10l12-4" stroke="url(#pShirt)" stroke-width="21" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M144 106c11 14 16 31 28 38" stroke="url(#pShirt)" stroke-width="20" stroke-linecap="round"/>
                            <circle cx="175" cy="145" r="7.5" fill="url(#pSkin)"/>

                            <rect x="84" y="96" width="62" height="86" rx="22" fill="url(#pShirt)"/>
                            <path d="M89 96h52l4 78c0 5-4 8-9 8H94c-5 0-9-3-9-8l4-78z" fill="url(#pVest)"/>
                            <path d="M103 96l12 18 12-18" fill="#F8FAFC"/>
                            <path d="M103 96l12 18 12-18" stroke="#94A3B8" stroke-width="1.8" stroke-linejoin="round"/>

                            <!-- Labu Erlenmeyer & Gelembung Reaksi -->
                            <g transform="translate(94, 124)">
                                <circle cx="11" cy="2" r="2.2" fill="#FEF08A" class="animate-bubble-1"/>
                                <circle cx="16" cy="-2" r="1.8" fill="#FFFFFF" class="animate-bubble-2"/>
                                <path d="M9 6h8v6l7 16a3 3 0 0 1-2.7 4.3H4.7A3 3 0 0 1 2 28l7-16V6z" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="1.3"/>
                                <path d="M4.5 22h17l2.5 6a3 3 0 0 1-2.7 4.3H4.7A3 3 0 0 1 2 28l2.5-6z" fill="#DC2626"/>
                            </g>

                            <rect x="108" y="80" width="14" height="18" rx="6" fill="url(#pSkin)"/>
                            <rect x="94" y="35" width="42" height="50" rx="20" fill="url(#pSkin)"/>
                            <circle cx="93" cy="61" r="5" fill="#EAB38F"/>
                            <circle cx="137" cy="61" r="5" fill="#EAB38F"/>
                            <path d="M93 53c0-17 10-25 23-25 14 0 22 8 22 24-4-8-11-11-22-11-10 0-17 4-23 12z" fill="#5C3A21"/>
                            <path d="M97 34c6-7 25-9 35 1" stroke="#472B17" stroke-width="4" stroke-linecap="round"/>

                            <circle cx="106" cy="59" r="7.5" fill="#FFFFFF" fill-opacity="0.25" stroke="#1E293B" stroke-width="2.2"/>
                            <circle cx="124" cy="59" r="7.5" fill="#FFFFFF" fill-opacity="0.25" stroke="#1E293B" stroke-width="2.2"/>
                            <line x1="113.5" y1="59" x2="116.5" y2="59" stroke="#1E293B" stroke-width="2.2"/>
                            <circle cx="106" cy="59" r="2.2" fill="#1E293B"/>
                            <circle cx="124" cy="59" r="2.2" fill="#1E293B"/>
                            <path d="M110 72c3 3.5 8 3.5 11 0" stroke="#9A3412" stroke-width="2.2" stroke-linecap="round"/>
                        </g>

                        <!-- Karakter Siswi Praktikan (Kanan) -->
                        <g>
                            <path d="M228 54c0-20 14-31 32-31s32 11 32 31l5 42c1 6-4 11-10 11h-54c-6 0-11-5-10-11l5-42z" fill="#1E293B"/>
                            <path d="M234 176h52l7 79h-66l7-79z" fill="url(#pWhitePants)"/>
                            <line x1="260" y1="194" x2="260" y2="255" stroke="#94A3B8" stroke-width="2" stroke-linecap="round"/>

                            <path d="M231 108c-9 18-11 39-7 56" stroke="url(#pSkin)" stroke-width="13" stroke-linecap="round"/>
                            <path d="M289 108c9 18 11 39 7 56" stroke="url(#pSkin)" stroke-width="13" stroke-linecap="round"/>

                            <path d="M232 98c4-6 13-9 28-9s24 3 28 9l10 26-13 6-4-12-2 60h-38l-2-60-4 12-13-6 10-26z" fill="url(#pYellow)"/>

                            <rect x="254" y="80" width="12" height="16" rx="5" fill="url(#pSkin)"/>
                            <rect x="241" y="38" width="38" height="46" rx="19" fill="url(#pSkin)"/>
                            <path d="M240 53c3-14 14-20 24-20 11 0 18 7 17 20-6-6-13-8-21-7-8 1-14 3-20 7z" fill="#0F172A"/>

                            <rect x="245" y="53" width="13" height="10" rx="4" fill="#FFFFFF" fill-opacity="0.2" stroke="#1E293B" stroke-width="2"/>
                            <rect x="262" y="53" width="13" height="10" rx="4" fill="#FFFFFF" fill-opacity="0.2" stroke="#1E293B" stroke-width="2"/>
                            <line x1="258" y1="58" x2="262" y2="58" stroke="#1E293B" stroke-width="2"/>
                            <circle cx="251.5" cy="58" r="2" fill="#1E293B"/>
                            <circle cx="268.5" cy="58" r="2" fill="#1E293B"/>
                            <path d="M255 70c2.5 3 7.5 3 10 0" stroke="#9A3412" stroke-width="2" stroke-linecap="round"/>
                        </g>
                    </svg>
                </div>

                <!-- 2. ILUSTRASI PETUGAS: LABORAN DENGAN JAS LAB, CLIPBOARD VERIFIKASI & MIKROSKOP -->
                <div id="illus-petugas" class="role-stage-item absolute inset-x-0 bottom-0 flex justify-center items-end px-4 opacity-0 translate-x-10 scale-95 pointer-events-none">
                    <svg viewBox="0 0 380 255" class="w-[240px] sm:w-[270px] lg:w-[325px] h-auto drop-shadow-[0_14px_24px_rgba(124,45,18,0.28)] animate-character-float" fill="none" aria-hidden="true">
                        <defs>
                            <linearGradient id="lSkin" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#FCD5B5"/>
                                <stop offset="100%" stop-color="#E7AE88"/>
                            </linearGradient>
                            <linearGradient id="labCoat" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0%" stop-color="#FFFFFF"/>
                                <stop offset="100%" stop-color="#E2E8F0"/>
                            </linearGradient>
                            <linearGradient id="innerTeal" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#0F766E"/>
                                <stop offset="100%" stop-color="#115E59"/>
                            </linearGradient>
                            <linearGradient id="lTrousers" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#334155"/>
                                <stop offset="100%" stop-color="#1E293B"/>
                            </linearGradient>
                        </defs>

                        <!-- Floating Badge Kiri: Verifikasi Disetujui -->
                        <g class="animate-orbit-a">
                            <rect x="24" y="48" width="76" height="38" rx="12" fill="#FFFFFF" fill-opacity="0.96"/>
                            <circle cx="42" cy="67" r="9" fill="#DCFCE7"/>
                            <path d="M38 67.5l2.8 2.8 5.2-5.3" stroke="#16A34A" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                            <rect x="56" y="61" width="32" height="4" rx="2" fill="#0F172A"/>
                            <rect x="56" y="69" width="22" height="3.5" rx="1.75" fill="#16A34A"/>
                        </g>

                        <!-- Floating Badge Kanan: Scan Stok -->
                        <g class="animate-orbit-b">
                            <rect x="284" y="44" width="66" height="36" rx="12" fill="#FFFFFF" fill-opacity="0.22" stroke="#FFFFFF" stroke-opacity="0.45" stroke-width="1.5"/>
                            <path d="M297 56v12M302 56v12M306 56v12M312 56v12" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="331" cy="62" r="8" fill="#FEF3C7"/>
                            <path d="M328 62h6M331 59v6" stroke="#D97706" stroke-width="2" stroke-linecap="round"/>
                        </g>

                        <!-- Meja Kalibrasi & Mikroskop Lab (Kanan) -->
                        <g>
                            <rect x="234" y="176" width="106" height="79" rx="14" fill="#1E293B"/>
                            <rect x="228" y="170" width="118" height="12" rx="6" fill="#F8FAFC"/>
                            <path d="M276 112h12v20h-12z" fill="#DC2626"/>
                            <path d="M282 104v8" stroke="#F8FAFC" stroke-width="3" stroke-linecap="round"/>
                            <path d="M288 122c14 0 22 10 22 24v16" stroke="#CBD5E1" stroke-width="7" stroke-linecap="round"/>
                            <rect x="266" y="144" width="32" height="5" rx="2.5" fill="#94A3B8"/>
                            <path d="M262 170l8-14h34l8 14H262z" fill="#E2E8F0"/>
                        </g>

                        <!-- Karakter Utama: Petugas Laboran (Kiri-Tengah) -->
                        <g>
                            <path d="M116 184h54l6 71h-66l6-71z" fill="url(#lTrousers)"/>
                            <line x1="143" y1="196" x2="143" y2="255" stroke="#0F172A" stroke-width="2.4" stroke-linecap="round"/>
                            <path d="M106 106c-14 16-16 38-10 56" stroke="url(#labCoat)" stroke-width="22" stroke-linecap="round"/>
                            <path d="M178 106c14 14 22 28 22 42l-18 8" stroke="url(#labCoat)" stroke-width="21" stroke-linecap="round" stroke-linejoin="round"/>
                            <rect x="108" y="96" width="70" height="94" rx="22" fill="url(#innerTeal)"/>
                            <path d="M106 96h28l6 96h-36c-5 0-8-4-8-9l10-87z" fill="url(#labCoat)"/>
                            <path d="M152 96h28l10 87c0 5-3 9-8 9h-36l6-96z" fill="url(#labCoat)"/>
                            <path d="M126 96l10 24-14 6" stroke="#CBD5E1" stroke-width="2" stroke-linejoin="round"/>
                            <path d="M160 96l-10 24 14 6" stroke="#CBD5E1" stroke-width="2" stroke-linejoin="round"/>
                            <rect x="160" y="132" width="16" height="22" rx="4" fill="#FEF3C7" stroke="#F59E0B" stroke-width="1.5"/>
                            <circle cx="168" cy="139" r="3" fill="#D97706"/>

                            <g transform="translate(154, 136)">
                                <rect x="0" y="4" width="38" height="48" rx="7" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="1.5"/>
                                <rect x="11" y="0" width="16" height="7" rx="3" fill="#EA580C"/>
                                <path d="M9 20l3 3 5-5" stroke="#16A34A" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                <rect x="21" y="19" width="11" height="3" rx="1.5" fill="#64748B"/>
                                <path d="M9 32l3 3 5-5" stroke="#16A34A" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                <rect x="21" y="31" width="11" height="3" rx="1.5" fill="#64748B"/>
                            </g>

                            <rect x="136" y="79" width="14" height="19" rx="6" fill="url(#lSkin)"/>
                            <rect x="121" y="33" width="44" height="51" rx="21" fill="url(#lSkin)"/>
                            <path d="M120 52c0-18 11-26 24-26 14 0 22 9 22 25-5-8-12-11-23-11-10 0-17 4-23 12z" fill="#1E293B"/>
                            <rect x="126" y="53" width="15" height="11" rx="4" fill="#FFFFFF" fill-opacity="0.3" stroke="#0F172A" stroke-width="2"/>
                            <rect x="145" y="53" width="15" height="11" rx="4" fill="#FFFFFF" fill-opacity="0.3" stroke="#0F172A" stroke-width="2"/>
                            <line x1="141" y1="58" x2="145" y2="58" stroke="#0F172A" stroke-width="2"/>
                            <circle cx="133.5" cy="58.5" r="2" fill="#0F172A"/>
                            <circle cx="152.5" cy="58.5" r="2" fill="#0F172A"/>
                            <path d="M138 72c3 3.5 8 3.5 11 0" stroke="#9A3412" stroke-width="2.2" stroke-linecap="round"/>
                        </g>
                    </svg>
                </div>

                <!-- 3. ILUSTRASI ADMIN: ADMINISTRATOR SISTEM DENGAN DASHBOARD ANALITIK & KEAMANAN -->
                <div id="illus-admin" class="role-stage-item absolute inset-x-0 bottom-0 flex justify-center items-end px-4 opacity-0 translate-x-10 scale-95 pointer-events-none">
                    <svg viewBox="0 0 380 255" class="w-[240px] sm:w-[270px] lg:w-[325px] h-auto drop-shadow-[0_14px_24px_rgba(15,23,42,0.35)] animate-character-float" fill="none" aria-hidden="true">
                        <defs>
                            <linearGradient id="aSkin" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#FCD5B5"/>
                                <stop offset="100%" stop-color="#E7AE88"/>
                            </linearGradient>
                            <linearGradient id="aBlazer" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0%" stop-color="#334155"/>
                                <stop offset="100%" stop-color="#0F172A"/>
                            </linearGradient>
                            <linearGradient id="aTrousers" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#1E293B"/>
                                <stop offset="100%" stop-color="#020617"/>
                            </linearGradient>
                        </defs>

                        <!-- Panel Hologram Dashboard Sistem di Kanan -->
                        <g class="animate-orbit-b">
                            <rect x="218" y="68" width="132" height="94" rx="16" fill="#FFFFFF" fill-opacity="0.16" stroke="#FFFFFF" stroke-opacity="0.38" stroke-width="1.5"/>
                            <circle cx="232" cy="81" r="3" fill="#FCA5A5"/>
                            <circle cx="242" cy="81" r="3" fill="#FDE047"/>
                            <circle cx="252" cy="81" r="3" fill="#86EFAC"/>
                            <rect x="234" y="124" width="12" height="24" rx="4" fill="#FCA5A5"/>
                            <rect x="253" y="112" width="12" height="36" rx="4" fill="#FFFFFF"/>
                            <rect x="272" y="102" width="12" height="46" rx="4" fill="#FDE047"/>
                            <rect x="291" y="116" width="12" height="32" rx="4" fill="#FFFFFF"/>
                            <rect x="310" y="95" width="12" height="53" rx="4" fill="#34D399"/>
                        </g>

                        <!-- Floating Badge Perisai Keamanan & Role -->
                        <g class="animate-orbit-a">
                            <rect x="30" y="54" width="68" height="38" rx="12" fill="#FFFFFF" fill-opacity="0.96"/>
                            <path d="M47 64l8 3v6c0 5-3.5 9-8 11-4.5-2-8-6-8-11v-6l8-3z" fill="#FEE2E2" stroke="#DC2626" stroke-width="1.8" stroke-linejoin="round"/>
                            <rect x="61" y="67" width="26" height="4" rx="2" fill="#0F172A"/>
                            <rect x="61" y="75" width="18" height="3.5" rx="1.75" fill="#DC2626"/>
                        </g>

                        <!-- Karakter Administrator Sistem (Kiri-Tengah) -->
                        <g>
                            <path d="M114 182h56l6 73h-68l6-73z" fill="url(#aTrousers)"/>
                            <line x1="142" y1="196" x2="142" y2="255" stroke="#334155" stroke-width="2" stroke-linecap="round"/>
                            <path d="M104 106c-12 16-14 38-9 56" stroke="url(#aBlazer)" stroke-width="22" stroke-linecap="round"/>
                            <path d="M178 106c16 10 30 14 44 10" stroke="url(#aBlazer)" stroke-width="21" stroke-linecap="round"/>
                            <circle cx="225" cy="115" r="7.5" fill="url(#aSkin)"/>
                            <rect x="106" y="96" width="72" height="92" rx="22" fill="url(#aBlazer)"/>
                            <path d="M130 96h24l-12 34-12-34z" fill="#F8FAFC"/>
                            <path d="M140 104h4l2 20-4 6-4-6 2-20z" fill="#DC2626"/>

                            <g transform="translate(96, 138)">
                                <rect x="0" y="0" width="44" height="28" rx="5" fill="#F8FAFC" stroke="#94A3B8" stroke-width="1.5"/>
                                <circle cx="22" cy="14" r="4" fill="#DC2626"/>
                                <rect x="-4" y="27" width="52" height="4" rx="2" fill="#CBD5E1"/>
                            </g>

                            <rect x="135" y="79" width="14" height="19" rx="6" fill="url(#aSkin)"/>
                            <rect x="120" y="33" width="44" height="51" rx="21" fill="url(#aSkin)"/>
                            <path d="M119 51c0-18 11-26 24-26 14 0 22 9 22 25-5-8-12-11-23-11-10 0-17 4-23 12z" fill="#0F172A"/>
                            <rect x="125" y="53" width="15" height="10" rx="3.5" fill="#FFFFFF" fill-opacity="0.22" stroke="#0F172A" stroke-width="2.2"/>
                            <rect x="144" y="53" width="15" height="10" rx="3.5" fill="#FFFFFF" fill-opacity="0.22" stroke="#0F172A" stroke-width="2.2"/>
                            <line x1="140" y1="58" x2="144" y2="58" stroke="#0F172A" stroke-width="2.2"/>
                            <circle cx="132.5" cy="58" r="2" fill="#0F172A"/>
                            <circle cx="151.5" cy="58" r="2" fill="#0F172A"/>
                            <path d="M137 72c3 3 8 3 11 0" stroke="#9A3412" stroke-width="2.2" stroke-linecap="round"/>
                        </g>
                    </svg>
                </div>

            </div>
        </section>

        <!-- ==============================================================
             PANEL KANAN: FORM LOGIN & ROLE SWITCHER INTERAKTIF
             ============================================================== -->
        <section class="md:col-span-6 flex flex-col justify-center px-3 sm:px-6 lg:px-8 py-2 sm:py-3">
            <div class="w-full max-w-[420px] mx-auto">

                <!-- Logo Brand Bulat di Tengah Atas -->
                <div class="flex items-center justify-center gap-2.5">
                    <div class="w-9 h-9 rounded-full bg-brand-600 text-white flex items-center justify-center shadow-sm shadow-brand-600/30">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M10 2v7.527a2 2 0 0 1-.211.896L4.72 20.55a1 1 0 0 0 .9 1.45h12.76a1 1 0 0 0 .9-1.45l-5.069-10.127A2 2 0 0 1 14 9.527V2"/>
                            <path d="M8.5 2h7"/>
                            <path d="M7 16h10"/>
                        </svg>
                    </div>
                    <span class="text-xl font-extrabold tracking-tight text-slate-900">
                        Lab<span class="text-brand-600">Pinjam</span>
                    </span>
                </div>

                <!-- Judul Selamat Datang -->
                <div class="text-center mt-3">
                    <h2 class="text-xl sm:text-[26px] font-extrabold text-slate-900 tracking-tight leading-tight">
                        Selamat Datang
                    </h2>
                    <p class="text-xs sm:text-[13px] text-slate-400 font-medium mt-0.5">
                        Pilih peran akses &amp; masuk ke akun Anda
                    </p>
                </div>

                <!-- Segmented Role Switcher dengan Indikator Pill Bergeser Halus -->
                <div class="mt-3.5 bg-slate-100/95 p-1 rounded-2xl relative grid grid-cols-3 border border-slate-200/60">
                    <!-- Pill Putih Aktif yang Bergeser Mulus -->
                    <div
                        id="role-pill-slider"
                        class="absolute top-1 bottom-1 left-1 w-[calc(33.333%-2.67px)] bg-white rounded-xl shadow-sm border border-slate-200/50 transition-transform duration-300 ease-out pointer-events-none"
                    ></div>

                    <button
                        type="button"
                        onclick="switchRole('peminjam')"
                        id="tab-peminjam"
                        class="relative z-10 py-1.5 text-xs font-extrabold text-brand-600 transition-colors flex items-center justify-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <span>Peminjam</span>
                    </button>

                    <button
                        type="button"
                        onclick="switchRole('petugas')"
                        id="tab-petugas"
                        class="relative z-10 py-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors flex items-center justify-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/>
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                            <path d="m9 14 2 2 4-4"/>
                        </svg>
                        <span>Petugas</span>
                    </button>

                    <button
                        type="button"
                        onclick="switchRole('admin')"
                        id="tab-admin"
                        class="relative z-10 py-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors flex items-center justify-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>
                        </svg>
                        <span>Admin</span>
                    </button>
                </div>

                <!-- Banner Alert Inline (Menampilkan error validasi/autentikasi atau status sesi Laravel) -->
                @if ($hasError)
                    <div id="login-alert" class="mt-2.5 rounded-2xl px-3.5 py-2 text-xs font-medium flex items-start gap-2 border bg-rose-50 border-rose-200 text-rose-700" role="alert">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" x2="12" y1="8" y2="12"/>
                            <line x1="12" x2="12.01" y1="16" y2="16"/>
                        </svg>
                        <span id="login-alert-text" class="leading-relaxed">{{ $errorMessage }}</span>
                    </div>
                @elseif ($statusMessage)
                    <div id="login-alert" class="mt-2.5 rounded-2xl px-3.5 py-2 text-xs font-medium flex items-start gap-2 border bg-emerald-50 border-emerald-200 text-emerald-700" role="status">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                        <span id="login-alert-text" class="leading-relaxed">{{ $statusMessage }}</span>
                    </div>
                @else
                    <div id="login-alert" class="hidden mt-2.5 rounded-2xl px-3.5 py-2 text-xs font-medium flex items-start gap-2 border">
                        <span id="login-alert-text" class="leading-relaxed"></span>
                    </div>
                @endif

                <!-- Form Login -->
                <form id="login-form" method="POST" action="{{ route('login.post') }}" class="mt-3 space-y-2.5">
                    @csrf
                    <input type="hidden" id="role_tab" name="role_tab" value="{{ $activeRoleTab }}">

                    <!-- Input Username -->
                    <div>
                        <label for="username" class="sr-only">Username</label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            value="{{ old('username') }}"
                            autocomplete="username"
                            oninput="detectRoleFromUsername(this.value)"
                            required
                            placeholder="Username peminjam (mis. peminjam)"
                            class="w-full rounded-2xl bg-slate-100/90 hover:bg-slate-100 focus:bg-white border border-transparent focus:border-brand-500/40 px-4 py-2.5 text-sm font-medium text-slate-800 placeholder-slate-400 focus:ring-4 focus:ring-brand-500/10 outline-none transition-all"
                        >
                    </div>

                    <!-- Input Kata Sandi + Tombol Mata di Kanan -->
                    <div>
                        <label for="password" class="sr-only">Kata Sandi</label>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                required
                                placeholder="Kata sandi"
                                class="w-full rounded-2xl bg-slate-100/90 hover:bg-slate-100 focus:bg-white border border-transparent focus:border-brand-500/40 pl-4 pr-11 py-2.5 text-sm font-medium text-slate-800 placeholder-slate-400 focus:ring-4 focus:ring-brand-500/10 outline-none transition-all"
                            >
                            <button
                                type="button"
                                onclick="togglePasswordVisibility()"
                                title="Tampilkan / Sembunyikan Kata Sandi"
                                aria-label="Tampilkan atau sembunyikan kata sandi"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1.5 rounded-xl text-slate-400 hover:text-slate-700 transition-colors"
                            >
                                <svg id="eye-icon-off" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/>
                                    <path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/>
                                    <path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/>
                                    <path d="m2 2 20 20"/>
                                </svg>
                                <svg id="eye-icon-on" class="w-4 h-4 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Link Lupa Kata Sandi Rata Kanan -->
                        <div class="mt-1.5 text-right">
                            <button type="button" onclick="openHelpModal('forgot')" class="text-xs font-medium text-slate-400 hover:text-brand-600 transition-colors">
                                Lupa kata sandi?
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Login Utama -->
                    <div class="pt-0.5">
                        <button
                            type="submit"
                            id="login-submit-btn"
                            class="w-full py-2.5 sm:py-3 px-5 rounded-2xl bg-brand-600 hover:bg-brand-700 active:scale-[0.99] text-white text-sm font-bold shadow-lg shadow-brand-600/25 transition-all"
                        >
                            Masuk sebagai Peminjam
                        </button>
                    </div>
                </form>

                <!-- Divider "Atau Isi Cepat Akun Demo" (Hanya Tampil di Lingkungan Local) -->
                @env('local')
                    <div class="mt-3.5">
                        <div class="relative flex items-center justify-center">
                            <div class="w-full border-t border-slate-200/80"></div>
                            <span class="relative bg-white px-3 text-[11px] font-medium text-slate-400 whitespace-nowrap">
                                Atau isi cepat akun demo
                            </span>
                        </div>

                        <!-- Tombol Outlined Role Cepat (Peminjam, Petugas, Admin) -->
                        <div class="mt-2.5 grid grid-cols-3 gap-2">
                            <button
                                type="button"
                                onclick="quickDemoFill('peminjam')"
                                class="py-2 px-2 rounded-xl border border-slate-200/90 bg-white hover:bg-rose-50/60 hover:border-brand-200 text-xs font-bold text-slate-700 flex items-center justify-center gap-1.5 transition-all"
                            >
                                <span class="w-4 h-4 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                                    <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                </span>
                                <span>Peminjam</span>
                            </button>

                            <button
                                type="button"
                                onclick="quickDemoFill('petugas')"
                                class="py-2 px-2 rounded-xl border border-slate-200/90 bg-white hover:bg-amber-50/60 hover:border-amber-200 text-xs font-bold text-slate-700 flex items-center justify-center gap-1.5 transition-all"
                            >
                                <span class="w-4 h-4 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                                    <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/>
                                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                                        <path d="m9 14 2 2 4-4"/>
                                    </svg>
                                </span>
                                <span>Petugas</span>
                            </button>

                            <button
                                type="button"
                                onclick="quickDemoFill('admin')"
                                class="py-2 px-2 rounded-xl border border-slate-200/90 bg-white hover:bg-slate-100/80 hover:border-slate-300 text-xs font-bold text-slate-700 flex items-center justify-center gap-1.5 transition-all"
                            >
                                <span class="w-4 h-4 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                                    <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>
                                    </svg>
                                </span>
                                <span>Admin</span>
                            </button>
                        </div>
                    </div>
                @endenv

                <!-- Teks Bawah Pendaftaran Akun -->
                <p class="mt-3 text-center text-xs text-slate-400 font-medium">
                    Belum punya akun laboratorium?
                    <button type="button" onclick="openHelpModal('register')" class="text-brand-600 hover:text-brand-700 font-bold hover:underline transition-colors">
                        Hubungi Admin
                    </button>
                </p>

            </div>
        </section>

    </main>

    <!-- ==================================================================
         JAVASCRIPT ENGINE: PERPINDAHAN ROLE MULUS & INTERAKTIVITAS
         ================================================================== -->
    <script>
        const roleOrder = ['peminjam', 'petugas', 'admin'];
        let currentRole = @json($activeRoleTab);

        const roleConfigs = {
            peminjam: {
                index: 0,
                pillText: 'Akses Peminjam • Siswa',
                line1: 'Kelola & pinjam',
                line2: 'alat sekolah di',
                accent: 'dashboard.',
                subtitle: 'Sederhanakan peminjaman peralatan laboratorium sekolah dengan inventaris yang rapi, cepat, dan terpantau.',
                placeholder: 'Username peminjam (mis. peminjam)',
                submitLabel: 'Masuk sebagai Peminjam',
                tabActiveColor: 'text-brand-600'
            },
            petugas: {
                index: 1,
                pillText: 'Akses Petugas • Laboran',
                line1: 'Verifikasi & cek',
                line2: 'stok alat lab di',
                accent: 'dashboard.',
                subtitle: 'Setujui pengajuan peminjaman siswa, pantau kalibrasi instrumen, dan verifikasi pengembalian secara real-time.',
                placeholder: 'Username petugas (mis. petugas)',
                submitLabel: 'Masuk sebagai Petugas',
                tabActiveColor: 'text-amber-600'
            },
            admin: {
                index: 2,
                pillText: 'Akses Admin • Kontrol Penuh',
                line1: 'Pantau seluruh',
                line2: 'inventaris di',
                accent: 'dashboard.',
                subtitle: 'Kelola akun pengguna, kategori peralatan, sinkronisasi stok otomatis, dan log aktivitas laboratorium sekolah.',
                placeholder: 'Username admin (mis. admin)',
                submitLabel: 'Masuk sebagai Admin',
                tabActiveColor: 'text-slate-900'
            }
        };

        let copyTransitionTimer = null;

        /**
         * Perpindahan Role dengan Transisi Mulus (Gradien, Teks, & Ilustrasi Arah)
         */
        function applyRole(targetRole, options = {}) {
            const { animate = true, force = false } = options;
            if (!roleConfigs[targetRole]) return;
            if (!force && targetRole === currentRole) return;

            const prevIndex = roleConfigs[currentRole] ? roleConfigs[currentRole].index : 0;
            const nextIndex = roleConfigs[targetRole].index;
            const direction = nextIndex >= prevIndex ? 1 : -1;
            currentRole = targetRole;

            const cfg = roleConfigs[targetRole];

            const roleTabInput = document.getElementById('role_tab');
            if (roleTabInput) {
                roleTabInput.value = targetRole;
            }

            // 1. Geser Pill Segmented Control di Panel Kanan
            const slider = document.getElementById('role-pill-slider');
            if (slider) {
                slider.style.transform = `translateX(${cfg.index * 100}%)`;
            }

            roleOrder.forEach((role) => {
                const tab = document.getElementById(`tab-${role}`);
                const dot = document.getElementById(`dot-${role}`);
                const bg = document.getElementById(`bg-${role}`);
                const illus = document.getElementById(`illus-${role}`);

                if (role === targetRole) {
                    tab.className = `relative z-10 py-1.5 text-xs font-extrabold ${cfg.tabActiveColor} transition-colors flex items-center justify-center gap-1.5`;
                    dot.className = 'h-1.5 w-6 rounded-full bg-white transition-all duration-300';
                    bg.classList.remove('opacity-0');
                    bg.classList.add('opacity-100');

                    illus.classList.remove('opacity-0', '-translate-x-10', 'translate-x-10', 'scale-95', 'pointer-events-none');
                    illus.classList.add('opacity-100', 'translate-x-0', 'scale-100');
                } else {
                    tab.className = 'relative z-10 py-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors flex items-center justify-center gap-1.5';
                    dot.className = 'h-1.5 w-2 rounded-full bg-white/45 hover:bg-white/75 transition-all duration-300';
                    bg.classList.remove('opacity-100');
                    bg.classList.add('opacity-0');

                    const exitTranslate = direction > 0 ? '-translate-x-10' : 'translate-x-10';
                    illus.classList.remove('opacity-100', 'translate-x-0', '-translate-x-10', 'translate-x-10', 'scale-100');
                    illus.classList.add('opacity-0', exitTranslate, 'scale-95', 'pointer-events-none');
                }
            });

            // 2. Transisi Teks Headline & Subjudul di Panel Kiri
            const copyBox = document.getElementById('showcase-copy');
            const updateCopy = () => {
                document.getElementById('showcase-pill-text').textContent = cfg.pillText;
                document.getElementById('headline-line1').textContent = cfg.line1;
                document.getElementById('headline-line2').textContent = cfg.line2;
                document.getElementById('headline-accent').textContent = cfg.accent;
                document.getElementById('showcase-subtitle').textContent = cfg.subtitle;

                const underline = document.getElementById('headline-underline');
                underline.classList.remove('replay-draw');
                void underline.offsetWidth;
                underline.classList.add('replay-draw');
            };

            if (copyTransitionTimer) {
                clearTimeout(copyTransitionTimer);
                copyTransitionTimer = null;
            }

            if (!animate) {
                updateCopy();
                copyBox.style.opacity = '1';
                copyBox.style.transform = 'translateY(0px)';
            } else {
                copyBox.style.opacity = '0';
                copyBox.style.transform = 'translateY(8px)';

                copyTransitionTimer = setTimeout(() => {
                    updateCopy();
                    copyBox.style.opacity = '1';
                    copyBox.style.transform = 'translateY(0px)';
                }, 170);
            }

            // 3. Perbarui Placeholder Input & Label Tombol Form
            document.getElementById('username').placeholder = cfg.placeholder;
            document.getElementById('login-submit-btn').textContent = cfg.submitLabel;
        }

        function switchRole(targetRole) {
            applyRole(targetRole, { animate: true, force: false });
        }

        // Deteksi otomatis role saat user mengetik username (peminjam / petugas / admin)
        function detectRoleFromUsername(val) {
            const clean = val.trim().toLowerCase();
            if (clean === 'admin' || clean === 'petugas' || clean === 'peminjam') {
                switchRole(clean);
            }
        }

        // Efek Parallax Halus pada Stage Ilustrasi saat Kursor Bergerak
        const showcaseEl = document.getElementById('visual-showcase');
        const parallaxStage = document.getElementById('parallax-stage');
        showcaseEl.addEventListener('mousemove', (e) => {
            if (window.innerWidth < 768) return;
            const rect = showcaseEl.getBoundingClientRect();
            const offsetX = ((e.clientX - rect.left) / rect.width - 0.5) * 10;
            const offsetY = ((e.clientY - rect.top) / rect.height - 0.5) * 6;
            parallaxStage.style.transform = `translate3d(${offsetX.toFixed(1)}px, ${offsetY.toFixed(1)}px, 0)`;
        });
        showcaseEl.addEventListener('mouseleave', () => {
            parallaxStage.style.transform = 'translate3d(0, 0, 0)';
        });

        // Toggle Tampilkan / Sembunyikan Kata Sandi
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeOff = document.getElementById('eye-icon-off');
            const eyeOn = document.getElementById('eye-icon-on');
            const isHidden = passwordInput.type === 'password';

            passwordInput.type = isHidden ? 'text' : 'password';
            eyeOff.classList.toggle('hidden', isHidden);
            eyeOn.classList.toggle('hidden', !isHidden);
        }

        @env('local')
            const demoCredentials = {
                peminjam: { username: 'peminjam', password: 'password', roleLabel: 'PEMINJAM' },
                petugas: { username: 'petugas', password: 'password', roleLabel: 'PETUGAS' },
                admin: { username: 'admin', password: 'password', roleLabel: 'ADMIN' }
            };

            function quickDemoFill(roleKey) {
                const account = demoCredentials[roleKey];
                if (!account) return;

                switchRole(roleKey);
                document.getElementById('username').value = account.username;
                document.getElementById('password').value = account.password;

                showToast(`Kredensial demo ${account.roleLabel} (${account.username}) diisikan`);
            }
        @endenv

        // Modal Bantuan (Lupa Password / Hubungi Admin)
        function openHelpModal(mode) {
            const modal = document.getElementById('help-modal');
            const iconKey = document.getElementById('help-modal-icon-key');
            const iconUser = document.getElementById('help-modal-icon-user');
            const title = document.getElementById('help-modal-title');
            const body = document.getElementById('help-modal-body');

            if (mode === 'register') {
                iconKey.classList.add('hidden');
                iconUser.classList.remove('hidden');
                title.textContent = 'Pendaftaran Akun Baru';
                body.innerHTML = `
                    <p>
                        Pembuatan akun siswa maupun petugas dikelola terpusat oleh <strong class="text-slate-900">Admin Laboratorium</strong> agar sesuai dengan data kelas aktif.
                    </p>
                    <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 text-xs text-slate-600">
                        Kunjungi ruang <strong class="text-slate-800">Laboratorium Sekolah</strong> untuk aktivasi akun peminjaman baru.
                    </div>
                `;
            } else {
                iconUser.classList.add('hidden');
                iconKey.classList.remove('hidden');
                title.textContent = 'Lupa Kata Sandi?';
                body.innerHTML = `
                    <p>
                        Pengaturan ulang kata sandi dilakukan langsung oleh <strong class="text-slate-900">Admin Laboratorium</strong> untuk menjaga keamanan inventaris sekolah.
                    </p>
                    <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 text-xs text-slate-600">
                        Silakan temui petugas laboran di ruang <strong class="text-slate-800">Lab Utama</strong> dengan menyertakan NIS/NIP dan username Anda.
                    </div>
                `;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeHelpModal() {
            const modal = document.getElementById('help-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function handleModalBackdrop(event) {
            if (event.target.id === 'help-modal') {
                closeHelpModal();
            }
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeHelpModal();
            }
        });

        // Toast Notification
        function showToast(message) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto bg-slate-900 text-white text-xs sm:text-sm font-medium px-4 py-2.5 rounded-2xl shadow-lg border border-slate-700/80 flex items-center gap-2.5 transform transition-all duration-300 translate-y-2 opacity-0';

            const dot = document.createElement('span');
            dot.className = 'w-2 h-2 rounded-full bg-brand-500 shrink-0';

            const text = document.createElement('span');
            text.textContent = message;

            toast.appendChild(dot);
            toast.appendChild(text);
            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            });

            setTimeout(() => {
                toast.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 2800);
        }

        // Pulihkan tab role yang aktif sesuai input sebelumnya setelah redirect validasi
        document.addEventListener('DOMContentLoaded', () => {
            applyRole(currentRole, { animate: false, force: true });
        });
    </script>
</body>
</html>
