<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class AlatIllustrationService
{
    /** @var array<string, array{key: string, group: string, label: string, keywords: list<string>, svg: string}>|null */
    private static ?array $iconsCache = null;

    /** @var array<string, array{data: array<string, array{mode: string, type?: string, file?: string}>, path: string, mtime: ?int}> */
    private static array $manifestCache = [];

    /**
     * Daftar lengkap ikon SVG offline beserta grup & kata kunci pencocokan otomatis.
     *
     * @return array<string, array{key: string, group: string, label: string, keywords: list<string>, svg: string}>
     */
    public static function icons(): array
    {
        if (self::$iconsCache !== null) {
            return self::$iconsCache;
        }

        return self::$iconsCache = [
            // --- Lab Kimia ---
            'erlenmeyer' => [
                'key' => 'erlenmeyer',
                'group' => 'kimia',
                'label' => 'Erlenmeyer / Gelas Ukur',
                'keywords' => ['gelas ukur', 'erlenmeyer', 'labu', 'beaker', 'gelas kimia'],
                'svg' => '<path d="M27 14H43" stroke="#1F2937" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M30 14V31L17.5 56.5C16.2 59.1 18.1 62 21 62H49C51.9 62 53.8 59.1 52.5 56.5L40 31V14" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.5" stroke-linejoin="round"/>
                    <path d="M23.5 47H46.5L51 56.5C51.7 58 50.6 59.5 49 59.5H21C19.4 59.5 18.3 58 19 56.5L23.5 47Z" fill="#E53935" fill-opacity="0.16"/>
                    <path d="M23.5 47H46.5" stroke="#E53935" stroke-width="2" stroke-linecap="round"/>
                    <path d="M32 37H37M31 42H36" stroke="#6B7280" stroke-width="1.8" stroke-linecap="round"/>
                    <circle cx="32" cy="53" r="2" fill="#E53935"/>
                    <circle cx="39" cy="55" r="1.5" fill="#E53935"/>
                    <rect x="57" y="23" width="9" height="36" rx="4.5" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.3"/>
                    <path d="M55 23H68" stroke="#1F2937" stroke-width="2.3" stroke-linecap="round"/>
                    <path d="M58.5 42H64.5V54.5C64.5 56.2 63.2 57.5 61.5 57.5C59.8 57.5 58.5 56.2 58.5 54.5V42Z" fill="#E53935" fill-opacity="0.22"/>',
            ],
            'tabung' => [
                'key' => 'tabung',
                'group' => 'kimia',
                'label' => 'Tabung Reaksi',
                'keywords' => ['tabung reaksi', 'rak tabung', 'tabung'],
                'svg' => '<path d="M14 58H66" stroke="#1F2937" stroke-width="2.8" stroke-linecap="round"/>
                    <path d="M18 32H62" stroke="#1F2937" stroke-width="2.4" stroke-linecap="round"/>
                    <path d="M18 32V58M62 32V58" stroke="#1F2937" stroke-width="2.4" stroke-linecap="round"/>
                    <rect x="24" y="18" width="8" height="34" rx="4" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.2"/>
                    <rect x="36" y="18" width="8" height="34" rx="4" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.2"/>
                    <rect x="48" y="18" width="8" height="34" rx="4" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.2"/>
                    <path d="M25.5 38H30.5V48C30.5 49.4 29.4 50.5 28 50.5C26.6 50.5 25.5 49.4 25.5 48V38Z" fill="#E53935" fill-opacity="0.24"/>
                    <path d="M37.5 34H42.5V48C42.5 49.4 41.4 50.5 40 50.5C38.6 50.5 37.5 49.4 37.5 48V34Z" fill="#E53935" fill-opacity="0.7"/>
                    <path d="M49.5 41H54.5V48C54.5 49.4 53.4 50.5 52 50.5C50.6 50.5 49.5 49.4 49.5 48V41Z" fill="#6B7280" fill-opacity="0.35"/>',
            ],
            'pipet' => [
                'key' => 'pipet',
                'group' => 'kimia',
                'label' => 'Pipet / Buret',
                'keywords' => ['pipet', 'mikropipet', 'buret'],
                'svg' => '<g transform="rotate(35 40 40)">
                        <path d="M35 12C35 8.7 45 8.7 45 12V22H35V12Z" fill="#E53935" stroke="#1F2937" stroke-width="2.2"/>
                        <path d="M37 22H43V52L40 65L37 52V22Z" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.2" stroke-linejoin="round"/>
                        <path d="M38.5 40H41.5V51L40 58L38.5 51V40Z" fill="#E53935" fill-opacity="0.35"/>
                    </g>
                    <circle cx="24" cy="61" r="2.8" fill="#E53935"/>',
            ],
            'bunsen' => [
                'key' => 'bunsen',
                'group' => 'kimia',
                'label' => 'Pembakar Bunsen',
                'keywords' => ['bunsen', 'pembakar', 'spiritus', 'lampu spiritus'],
                'svg' => '<path d="M40 14C45 20 46 26 40 31C34 26 35 20 40 14Z" fill="#E53935" fill-opacity="0.22" stroke="#E53935" stroke-width="2" stroke-linejoin="round"/>
                    <rect x="35" y="34" width="10" height="22" rx="2" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.4"/>
                    <path d="M24 62H56L52 56H28L24 62Z" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.4" stroke-linejoin="round"/>
                    <path d="M48 45H56" stroke="#6B7280" stroke-width="2.2" stroke-linecap="round"/>',
            ],
            'mortir' => [
                'key' => 'mortir',
                'group' => 'kimia',
                'label' => 'Mortir & Alu',
                'keywords' => ['mortir', 'lumpang', 'alu', 'gerus'],
                'svg' => '<path d="M48 16L37 36" stroke="#E53935" stroke-width="4" stroke-linecap="round"/>
                    <path d="M18 36H62C62 50 53 58 40 58C27 58 18 50 18 36Z" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.5" stroke-linejoin="round"/>
                    <path d="M28 58L26 63H54L52 58" stroke="#1F2937" stroke-width="2.3" stroke-linejoin="round"/>',
            ],
            'botol_reagen' => [
                'key' => 'botol_reagen',
                'group' => 'kimia',
                'label' => 'Botol Reagen',
                'keywords' => ['botol reagen', 'reagen', 'botol kimia', 'ph meter'],
                'svg' => '<rect x="31" y="14" width="18" height="7" rx="2" fill="#E53935" stroke="#1F2937" stroke-width="2.2"/>
                    <path d="M33 21V28L23 36V61C23 62.7 24.3 64 26 64H54C55.7 64 57 62.7 57 61V36L47 28V21" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.5" stroke-linejoin="round"/>
                    <rect x="29" y="42" width="22" height="12" rx="2" fill="#F7F7F8" stroke="#E53935" stroke-width="1.8"/>',
            ],

            // --- Lab Fisika & Pengukuran ---
            'penggaris' => [
                'key' => 'penggaris',
                'group' => 'fisika',
                'label' => 'Penggaris / Mistar',
                'keywords' => ['penggaris', 'mistar', 'meteran', 'busur', 'rolmeter'],
                'svg' => '<g transform="rotate(-24 40 40)">
                        <rect x="10" y="30" width="60" height="20" rx="3" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.5"/>
                        <path d="M18 30V37M25 30V34M32 30V38M39 30V34M46 30V38M53 30V34M60 30V37" stroke="#1F2937" stroke-width="2" stroke-linecap="round"/>
                        <path d="M18 44H62" stroke="#E53935" stroke-width="2" stroke-linecap="round" stroke-dasharray="4 3"/>
                    </g>',
            ],
            'jangka_sorong' => [
                'key' => 'jangka_sorong',
                'group' => 'fisika',
                'label' => 'Jangka Sorong',
                'keywords' => ['jangka sorong', 'mikrometer', 'sigmat'],
                'svg' => '<path d="M16 33H66V41H16V33Z" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.4" stroke-linejoin="round"/>
                    <path d="M16 20V58L23 51V41H16V20Z" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.4" stroke-linejoin="round"/>
                    <path d="M32 23V56L38 50V41H32V23Z" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.4" stroke-linejoin="round"/>
                    <rect x="31" y="30" width="18" height="13" rx="2.5" fill="#1F2937"/>
                    <rect x="34" y="33" width="12" height="7" rx="1" fill="#F7F7F8"/>
                    <path d="M36 36.5H44" stroke="#E53935" stroke-width="2" stroke-linecap="round"/>
                    <path d="M53 37H62M56 33V36M60 33V36" stroke="#6B7280" stroke-width="1.6" stroke-linecap="round"/>',
            ],
            'multimeter' => [
                'key' => 'multimeter',
                'group' => 'fisika',
                'label' => 'Multimeter',
                'keywords' => ['multimeter', 'avometer', 'voltmeter', 'amperemeter', 'ohmmeter'],
                'svg' => '<rect x="20" y="12" width="40" height="56" rx="6" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.5"/>
                    <rect x="26" y="19" width="28" height="15" rx="3" fill="#F7F7F8" stroke="#1F2937" stroke-width="2"/>
                    <path d="M31 27H36M40 24V29M45 27H49" stroke="#E53935" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="40" cy="48" r="8.5" fill="#F7F7F8" stroke="#1F2937" stroke-width="2.2"/>
                    <path d="M40 48L45 43" stroke="#E53935" stroke-width="2.5" stroke-linecap="round"/>
                    <circle cx="29" cy="61" r="2.5" fill="#1F2937"/>
                    <circle cx="40" cy="61" r="2.5" fill="#E53935"/>
                    <circle cx="51" cy="61" r="2.5" fill="#6B7280"/>',
            ],
            'osiloskop' => [
                'key' => 'osiloskop',
                'group' => 'fisika',
                'label' => 'Osiloskop',
                'keywords' => ['osiloskop', 'oscilloscope', 'signal generator'],
                'svg' => '<rect x="12" y="18" width="56" height="42" rx="6" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.5"/>
                    <rect x="18" y="24" width="32" height="24" rx="3" fill="#F7F7F8" stroke="#1F2937" stroke-width="2"/>
                    <path d="M21 36H25L28 29L32 42L36 31L39 36H47" stroke="#E53935" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="58" cy="29" r="3" fill="#FFFFFF" stroke="#1F2937" stroke-width="2"/>
                    <circle cx="58" cy="39" r="3" fill="#FFFFFF" stroke="#E53935" stroke-width="2"/>
                    <rect x="18" y="52" width="12" height="3" rx="1.5" fill="#6B7280"/>
                    <rect x="34" y="52" width="16" height="3" rx="1.5" fill="#1F2937"/>
                    <path d="M22 60V64M58 60V64" stroke="#1F2937" stroke-width="2.5" stroke-linecap="round"/>',
            ],
            'neraca' => [
                'key' => 'neraca',
                'group' => 'fisika',
                'label' => 'Neraca / Timbangan',
                'keywords' => ['neraca', 'timbangan', 'stopwatch'],
                'svg' => '<rect x="20" y="22" width="40" height="6" rx="3" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.4"/>
                    <path d="M40 28V38" stroke="#1F2937" stroke-width="2.6"/>
                    <path d="M16 38H64L60 60H20L16 38Z" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.5" stroke-linejoin="round"/>
                    <rect x="28" y="44" width="24" height="10" rx="2" fill="#F7F7F8" stroke="#1F2937" stroke-width="1.8"/>
                    <path d="M34 49H46" stroke="#E53935" stroke-width="2.2" stroke-linecap="round"/>',
            ],
            'termometer' => [
                'key' => 'termometer',
                'group' => 'fisika',
                'label' => 'Termometer',
                'keywords' => ['termometer', 'thermometer', 'pengukur suhu'],
                'svg' => '<path d="M35 16A5 5 0 0 1 45 16V46.3A10 10 0 1 1 35 46.3V16Z" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.5"/>
                    <circle cx="40" cy="55" r="5.5" fill="#E53935"/>
                    <path d="M40 25V55" stroke="#E53935" stroke-width="3" stroke-linecap="round"/>
                    <path d="M49 22H54M49 29H53M49 36H54M49 43H53" stroke="#6B7280" stroke-width="1.8" stroke-linecap="round"/>',
            ],
            'magnet' => [
                'key' => 'magnet',
                'group' => 'fisika',
                'label' => 'Magnet',
                'keywords' => ['magnet', 'kompas', 'kumparan'],
                'svg' => '<path d="M22 22V44A18 18 0 0 0 58 44V22H47V44A7 7 0 0 1 33 44V22H22Z" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.5" stroke-linejoin="round"/>
                    <path d="M22 22H33V31H22ZM47 22H58V31H47Z" fill="#E53935" fill-opacity="0.25" stroke="#E53935" stroke-width="2"/>',
            ],

            // --- Lab Biologi ---
            'mikroskop' => [
                'key' => 'mikroskop',
                'group' => 'biologi',
                'label' => 'Mikroskop',
                'keywords' => ['mikroskop'],
                'svg' => '<path d="M20 64H60" stroke="#1F2937" stroke-width="3" stroke-linecap="round"/>
                    <path d="M26 64V58H54V64" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.5" stroke-linejoin="round"/>
                    <path d="M26 49H47" stroke="#1F2937" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M35 49V45" stroke="#E53935" stroke-width="2.5" stroke-linecap="round"/>
                    <rect x="33" y="16" width="11" height="24" rx="2" transform="rotate(14 33 16)" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.5"/>
                    <path d="M35 15L46 18" stroke="#E53935" stroke-width="3" stroke-linecap="round"/>
                    <path d="M44 29C53 31 56 41 53 50C51.5 54 48.5 56.5 45 58" stroke="#1F2937" stroke-width="2.5" stroke-linecap="round"/>
                    <circle cx="48" cy="39" r="3.5" fill="#FFFFFF" stroke="#E53935" stroke-width="2"/>',
            ],
            'lup' => [
                'key' => 'lup',
                'group' => 'biologi',
                'label' => 'Lup & Preparat',
                'keywords' => ['lup', 'kaca pembesar', 'preparat'],
                'svg' => '<rect x="14" y="48" width="38" height="12" rx="2" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.2"/>
                    <rect x="27" y="51" width="12" height="6" rx="1" fill="#E53935" fill-opacity="0.2" stroke="#E53935" stroke-width="1.6"/>
                    <circle cx="42" cy="31" r="14" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.6"/>
                    <circle cx="42" cy="31" r="9" fill="#F7F7F8" stroke="#E53935" stroke-width="1.6" stroke-dasharray="3 2"/>
                    <path d="M52 41L64 53" stroke="#1F2937" stroke-width="4" stroke-linecap="round"/>
                    <path d="M55 44L61 50" stroke="#E53935" stroke-width="2" stroke-linecap="round"/>',
            ],
            'cawan_petri' => [
                'key' => 'cawan_petri',
                'group' => 'biologi',
                'label' => 'Cawan Petri',
                'keywords' => ['cawan petri', 'petri', 'cawan'],
                'svg' => '<ellipse cx="40" cy="46" rx="24" ry="11" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.5"/>
                    <ellipse cx="40" cy="38" rx="24" ry="11" fill="#F7F7F8" stroke="#1F2937" stroke-width="2.5"/>
                    <circle cx="34" cy="38" r="3" fill="#E53935"/>
                    <circle cx="45" cy="36" r="2.2" fill="#E53935"/>
                    <circle cx="43" cy="42" r="2" fill="#6B7280"/>',
            ],
            'pinset' => [
                'key' => 'pinset',
                'group' => 'biologi',
                'label' => 'Pinset / Alat Bedah',
                'keywords' => ['pinset', 'bedah', 'scalpel', 'gunting bedah'],
                'svg' => '<path d="M56 18L22 58M62 24L22 58" stroke="#1F2937" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M56 18L62 24" stroke="#E53935" stroke-width="3.2" stroke-linecap="round"/>',
            ],

            // --- Lab Komputer & Jaringan ---
            'komputer' => [
                'key' => 'komputer',
                'group' => 'komputer',
                'label' => 'Komputer / Laptop',
                'keywords' => ['komputer', 'laptop', 'monitor', 'pc', 'chromebook'],
                'svg' => '<rect x="14" y="18" width="52" height="34" rx="4" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.5"/>
                    <rect x="19" y="23" width="42" height="24" rx="2" fill="#F7F7F8" stroke="#E5E7EB" stroke-width="1.5"/>
                    <path d="M27 35H43M27 40H37" stroke="#E53935" stroke-width="2" stroke-linecap="round"/>
                    <path d="M34 52L32 60H48L46 52" stroke="#1F2937" stroke-width="2.4" stroke-linejoin="round"/>
                    <path d="M26 60H54" stroke="#1F2937" stroke-width="2.5" stroke-linecap="round"/>',
            ],
            'jaringan' => [
                'key' => 'jaringan',
                'group' => 'komputer',
                'label' => 'Router / LAN',
                'keywords' => ['router', 'routerboard', 'mikrotik', 'switch', 'lan', 'access point', 'modem'],
                'svg' => '<path d="M24 38V19M56 38V19" stroke="#1F2937" stroke-width="2.5" stroke-linecap="round"/>
                    <circle cx="24" cy="17" r="2.5" fill="#E53935"/>
                    <circle cx="56" cy="17" r="2.5" fill="#E53935"/>
                    <path d="M33 25C36.8 21.8 43.2 21.8 47 25" stroke="#6B7280" stroke-width="2" stroke-linecap="round"/>
                    <path d="M36.5 29.5C38.5 27.8 41.5 27.8 43.5 29.5" stroke="#E53935" stroke-width="2" stroke-linecap="round"/>
                    <rect x="14" y="38" width="52" height="20" rx="4" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.5"/>
                    <circle cx="23" cy="48" r="2.5" fill="#E53935"/>
                    <circle cx="31" cy="48" r="2.5" fill="#1F2937"/>
                    <rect x="42" y="44" width="6" height="8" rx="1" fill="#F7F7F8" stroke="#6B7280" stroke-width="1.8"/>
                    <rect x="52" y="44" width="6" height="8" rx="1" fill="#F7F7F8" stroke="#6B7280" stroke-width="1.8"/>
                    <path d="M22 58V63M58 58V63" stroke="#1F2937" stroke-width="2.5" stroke-linecap="round"/>',
            ],
            'crimping' => [
                'key' => 'crimping',
                'group' => 'komputer',
                'label' => 'Tang Crimping',
                'keywords' => ['crimping', 'tang crimping', 'obeng', 'tang'],
                'svg' => '<path d="M28 18L40 34L52 18" stroke="#1F2937" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="33" y="29" width="14" height="12" rx="2" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.4"/>
                    <circle cx="40" cy="35" r="2.2" fill="#E53935"/>
                    <path d="M35 41L24 62" stroke="#E53935" stroke-width="4.5" stroke-linecap="round"/>
                    <path d="M45 41L56 62" stroke="#1F2937" stroke-width="4.5" stroke-linecap="round"/>
                    <path d="M33 49H47" stroke="#6B7280" stroke-width="2" stroke-linecap="round"/>',
            ],
            'kabel' => [
                'key' => 'kabel',
                'group' => 'komputer',
                'label' => 'Kabel / Tester',
                'keywords' => ['kabel', 'cable', 'rj45', 'hdmi', 'usb', 'tester'],
                'svg' => '<rect x="18" y="22" width="14" height="16" rx="2" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.4"/>
                    <rect x="48" y="42" width="14" height="16" rx="2" fill="#FFFFFF" stroke="#E53935" stroke-width="2.4"/>
                    <path d="M25 38V50C25 56 35 56 38 42C41 28 55 28 55 42" stroke="#1F2937" stroke-width="2.5" stroke-linecap="round"/>',
            ],

            // --- Multimedia & Umum ---
            'proyektor' => [
                'key' => 'proyektor',
                'group' => 'multimedia',
                'label' => 'Proyektor',
                'keywords' => ['proyektor', 'projector', 'infocus'],
                'svg' => '<rect x="13" y="27" width="54" height="28" rx="5" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.5"/>
                    <circle cx="48" cy="41" r="9.5" fill="#F7F7F8" stroke="#1F2937" stroke-width="2.5"/>
                    <circle cx="48" cy="41" r="4.5" fill="#E53935" fill-opacity="0.18" stroke="#E53935" stroke-width="2"/>
                    <path d="M21 36H31M21 41H31M21 46H28" stroke="#6B7280" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="22" cy="21" r="2" fill="#E53935"/>
                    <path d="M24 55L20 63M56 55L60 63" stroke="#1F2937" stroke-width="2.5" stroke-linecap="round"/>',
            ],
            'kamera' => [
                'key' => 'kamera',
                'group' => 'multimedia',
                'label' => 'Kamera DSLR',
                'keywords' => ['kamera', 'dslr', 'mirrorless', 'webcam'],
                'svg' => '<path d="M28 24L32 18H48L52 24" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.5" stroke-linejoin="round"/>
                    <rect x="14" y="24" width="52" height="36" rx="6" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.5"/>
                    <circle cx="40" cy="42" r="11" fill="#F7F7F8" stroke="#1F2937" stroke-width="2.5"/>
                    <circle cx="40" cy="42" r="5.5" fill="#E53935" fill-opacity="0.18" stroke="#E53935" stroke-width="2"/>
                    <circle cx="57" cy="31" r="2.2" fill="#E53935"/>
                    <rect x="20" y="29" width="7" height="3.5" rx="1" fill="#6B7280"/>',
            ],
            'tripod' => [
                'key' => 'tripod',
                'group' => 'multimedia',
                'label' => 'Tripod',
                'keywords' => ['tripod', 'monopod', 'stand kamera'],
                'svg' => '<rect x="30" y="16" width="20" height="8" rx="2" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.4"/>
                    <path d="M50 20H59" stroke="#E53935" stroke-width="2.4" stroke-linecap="round"/>
                    <path d="M40 24V34" stroke="#1F2937" stroke-width="3" stroke-linecap="round"/>
                    <circle cx="40" cy="34" r="3.5" fill="#E53935"/>
                    <path d="M38 37L20 64M42 37L60 64M40 37V62" stroke="#1F2937" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M29 50H51" stroke="#6B7280" stroke-width="2" stroke-linecap="round"/>',
            ],
            'audio' => [
                'key' => 'audio',
                'group' => 'multimedia',
                'label' => 'Speaker / Mic',
                'keywords' => ['speaker', 'mikrofon', 'microphone', 'mic', 'headset', 'audio'],
                'svg' => '<rect x="23" y="15" width="34" height="50" rx="5" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.5"/>
                    <circle cx="40" cy="27" r="4" fill="#E53935"/>
                    <circle cx="40" cy="47" r="10" fill="#F7F7F8" stroke="#1F2937" stroke-width="2.3"/>
                    <circle cx="40" cy="47" r="3.5" fill="#1F2937"/>',
            ],
            'kacamata_lab' => [
                'key' => 'kacamata_lab',
                'group' => 'multimedia',
                'label' => 'Kacamata Lab / APD',
                'keywords' => ['kacamata', 'goggles', 'sarung tangan', 'jas lab', 'apd'],
                'svg' => '<path d="M14 38C14 32 18 29 24 29H56C62 29 66 32 66 38V44C66 49 62 52 57 52H47L43 44H37L33 52H23C18 52 14 49 14 44V38Z" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.5" stroke-linejoin="round"/>
                    <path d="M20 38H32M48 38H60" stroke="#E53935" stroke-width="2.2" stroke-linecap="round"/>',
            ],

            // --- Fallback Netral ---
            'default' => [
                'key' => 'default',
                'group' => 'all',
                'label' => 'Kotak Netral',
                'keywords' => [],
                'svg' => '<rect x="16" y="22" width="48" height="38" rx="6" fill="#FFFFFF" stroke="#6B7280" stroke-width="2.2" stroke-dasharray="4 3"/>
                    <path d="M30 22V18C30 16.3 31.3 15 33 15H47C48.7 15 50 16.3 50 18V22" stroke="#6B7280" stroke-width="2.2"/>
                    <circle cx="40" cy="41" r="9" fill="#F7F7F8" stroke="#9CA3AF" stroke-width="2"/>
                    <path d="M40 37V42M40 45.5H40.01" stroke="#6B7280" stroke-width="2.2" stroke-linecap="round"/>',
            ],
        ];
    }

    /**
     * Daftar key ikon valid.
     *
     * @return list<string>
     */
    public static function validIconKeys(): array
    {
        return array_keys(self::icons());
    }

    /**
     * Mengembalikan isi tag <svg> berdasarkan tipe ikon.
     */
    public static function svgInner(string $type): string
    {
        $icons = self::icons();

        return $icons[$type]['svg'] ?? $icons['default']['svg'];
    }

    /**
     * Payload siap-pakai untuk komponen picker & live preview JS di frontend.
     *
     * @return array{icons: list<array{key: string, group: string, label: string, keywords: list<string>, svg: string}>}
     */
    public static function clientPayload(): array
    {
        return [
            'icons' => array_values(self::icons()),
        ];
    }

    /**
     * Cek kata kunci dengan batas kata agar potongan huruf pendek tidak salah cocok.
     */
    public static function containsKeyword(string $text, string $keyword): bool
    {
        $pattern = '/(^|[^a-z0-9])' . preg_quote($keyword, '/') . '([^a-z0-9]|$)/iu';

        return preg_match($pattern, $text) === 1;
    }

    /**
     * Aturan validasi bersama untuk opsi ilustrasi di form Alat maupun Kategori.
     *
     * @return array<string, list<mixed>>
     */
    public static function validationRules(bool $requireUploadFile): array
    {
        return [
            'illustration_mode' => ['nullable', 'in:auto,manual_icon,upload'],
            'illustration_icon' => ['nullable', 'string', Rule::in(self::validIconKeys())],
            'illustration_file' => [
                $requireUploadFile ? 'required' : 'nullable',
                'file',
                'mimes:png,jpg,jpeg,webp',
                'max:2048',
            ],
        ];
    }

    /**
     * Pesan validasi bersama untuk opsi ilustrasi.
     *
     * @return array<string, string>
     */
    public static function validationMessages(): array
    {
        return [
            'illustration_mode.in' => 'Mode ilustrasi tidak valid.',
            'illustration_icon.in' => 'Ikon ilustrasi yang dipilih tidak valid.',
            'illustration_file.required' => 'Pilih file gambar terlebih dahulu untuk mode Upload Gambar.',
            'illustration_file.mimes' => 'Format gambar harus PNG, JPG, JPEG, atau WebP.',
            'illustration_file.max' => 'Ukuran gambar maksimal 2 MB.',
        ];
    }

    /**
     * Tentukan ilustrasi akhir untuk alat atau kartu kategori.
     *
     * @return array{mode: string, type: string, url: ?string, status: string, label: string}
     */
    public static function resolve(
        string $namaAlat = '',
        string $kategori = '',
        ?int $alatId = null,
        ?int $kategoriId = null
    ): array {
        $icons = self::icons();

        if ($alatId !== null) {
            $resolvedOverride = self::resolveOverride($alatId, 'alat', $icons);
            if ($resolvedOverride !== null) {
                return $resolvedOverride;
            }
        }

        $nama = trim($namaAlat);
        $kat = trim($kategori);

        if ($nama !== '') {
            $lowerNama = mb_strtolower($nama);

            foreach ($icons as $key => $icon) {
                if ($key === 'default') {
                    continue;
                }
                foreach ($icon['keywords'] as $kw) {
                    if (self::containsKeyword($lowerNama, $kw)) {
                        return [
                            'mode' => 'auto',
                            'type' => $key,
                            'url' => null,
                            'status' => 'matched',
                            'label' => 'Cocok otomatis: ' . $icon['label'],
                        ];
                    }
                }
            }

            return [
                'mode' => 'auto',
                'type' => 'default',
                'url' => null,
                'status' => 'unmatched',
                'label' => 'Tidak cocok dengan instrumen lab — memakai fallback netral',
            ];
        }

        // Mode kategori (tanpa nama alat): cek override kategori terlebih dahulu, lalu fallback nama kategori.
        if ($kategoriId !== null) {
            $resolvedKatOverride = self::resolveOverride($kategoriId, 'kategori', $icons);
            if ($resolvedKatOverride !== null) {
                return $resolvedKatOverride;
            }
        }

        if ($kat !== '') {
            $lowerKat = mb_strtolower($kat);
            $katType = match (true) {
                str_contains($lowerKat, 'kimia') => 'erlenmeyer',
                str_contains($lowerKat, 'fisika') => 'multimeter',
                str_contains($lowerKat, 'biologi') => 'mikroskop',
                str_contains($lowerKat, 'komputer') || str_contains($lowerKat, 'jaringan') => 'jaringan',
                str_contains($lowerKat, 'multimedia') => 'proyektor',
                default => 'default',
            };

            return [
                'mode' => 'auto',
                'type' => $katType,
                'url' => null,
                'status' => 'category',
                'label' => 'Ikon kategori',
            ];
        }

        return [
            'mode' => 'auto',
            'type' => 'default',
            'url' => null,
            'status' => 'empty',
            'label' => 'Menunggu input nama',
        ];
    }

    /**
     * Ambil override tersimpan untuk satu id entitas ('alat' atau 'kategori').
     *
     * @return array{mode: string, type?: string, file?: string}|null
     */
    public static function getOverride(int $id, string $entity = 'alat'): ?array
    {
        $manifest = self::readManifest($entity);

        return $manifest[(string) $id] ?? null;
    }

    /**
     * Simpan pilihan mode ilustrasi dari form Admin tanpa menambah kolom tabel DB.
     */
    public static function saveFromRequest(
        int $id,
        string $mode,
        ?string $manualIcon = null,
        ?UploadedFile $file = null,
        string $entity = 'alat'
    ): void {
        if ($mode === 'manual_icon') {
            $iconKey = in_array($manualIcon, self::validIconKeys(), true) ? $manualIcon : 'default';
            self::deleteUploadedFileFor($id, $entity);
            $manifest = self::readManifest($entity);
            $manifest[(string) $id] = [
                'mode' => 'manual_icon',
                'type' => $iconKey,
            ];
            self::writeManifest($manifest, $entity);

            return;
        }

        if ($mode === 'upload') {
            if ($file !== null && $file->isValid()) {
                self::deleteUploadedFileFor($id, $entity);

                $dir = public_path(self::uploadDir($entity));
                File::ensureDirectoryExists($dir);

                $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'png');
                $filename = $entity . '-' . $id . '-' . uniqid() . '.' . $ext;
                $file->move($dir, $filename);

                $manifest = self::readManifest($entity);
                $manifest[(string) $id] = [
                    'mode' => 'upload',
                    'file' => $filename,
                ];
                self::writeManifest($manifest, $entity);

                return;
            }

            // Pertahankan file upload lama jika sedang edit dan admin tidak memilih file baru.
            $existing = self::getOverride($id, $entity);
            if ($existing !== null && ($existing['mode'] ?? '') === 'upload' && !empty($existing['file'])) {
                return;
            }
        }

        self::clearOverride($id, $entity);
    }

    /**
     * Hapus override dan file gambar kustom milik entitas tertentu.
     */
    public static function clearOverride(int $id, string $entity = 'alat'): void
    {
        self::deleteUploadedFileFor($id, $entity);
        $manifest = self::readManifest($entity);
        if (isset($manifest[(string) $id])) {
            unset($manifest[(string) $id]);
            self::writeManifest($manifest, $entity);
        }
    }

    /**
     * Bersihkan cache statis manifest (berguna saat file diubah di luar proses atau pengujian).
     */
    public static function resetCache(?string $entity = null): void
    {
        if ($entity === null) {
            self::$manifestCache = [];

            return;
        }

        unset(self::$manifestCache[$entity]);
    }

    /**
     * @param array<string, array{key: string, group: string, label: string, keywords: list<string>, svg: string}> $icons
     * @return array{mode: string, type: string, url: ?string, status: string, label: string}|null
     */
    private static function resolveOverride(int $id, string $entity, array $icons): ?array
    {
        $override = self::getOverride($id, $entity);
        if ($override === null) {
            return null;
        }

        if (($override['mode'] ?? '') === 'upload' && !empty($override['file'])) {
            $relative = self::uploadDir($entity) . '/' . $override['file'];
            if (File::exists(public_path($relative))) {
                return [
                    'mode' => 'upload',
                    'type' => 'upload',
                    'url' => asset($relative),
                    'status' => 'manual_upload',
                    'label' => 'Gambar upload manual',
                ];
            }
        }

        if (($override['mode'] ?? '') === 'manual_icon' && !empty($override['type']) && isset($icons[$override['type']])) {
            $icon = $icons[$override['type']];

            return [
                'mode' => 'manual_icon',
                'type' => $icon['key'],
                'url' => null,
                'status' => 'manual_icon',
                'label' => 'Ikon manual: ' . $icon['label'],
            ];
        }

        return null;
    }

    private static function deleteUploadedFileFor(int $id, string $entity = 'alat'): void
    {
        $existing = self::getOverride($id, $entity);
        if ($existing !== null && ($existing['mode'] ?? '') === 'upload' && !empty($existing['file'])) {
            $path = public_path(self::uploadDir($entity) . '/' . $existing['file']);
            if (File::exists($path)) {
                File::delete($path);
            }
        }
    }

    private static function manifestPath(string $entity = 'alat'): string
    {
        $filename = $entity === 'kategori' ? 'kategori-illustrations.json' : 'alat-illustrations.json';

        return app()->environment('testing')
            ? storage_path('framework/testing/' . $filename)
            : storage_path('app/' . $filename);
    }

    private static function uploadDir(string $entity = 'alat'): string
    {
        $base = $entity === 'kategori' ? 'kategori' : 'alat';

        return app()->environment('testing')
            ? 'uploads/' . $base . '-testing'
            : 'uploads/' . $base;
    }

    /**
     * @return array<string, array{mode: string, type?: string, file?: string}>
     */
    private static function readManifest(string $entity = 'alat'): array
    {
        $path = self::manifestPath($entity);
        if (!File::exists($path)) {
            self::$manifestCache[$entity] = [
                'data' => [],
                'path' => $path,
                'mtime' => null,
            ];

            return [];
        }

        $mtime = @filemtime($path) ?: null;
        $cached = self::$manifestCache[$entity] ?? null;
        if (
            $cached !== null &&
            $cached['path'] === $path &&
            $cached['mtime'] === $mtime
        ) {
            return $cached['data'];
        }

        $decoded = json_decode((string) File::get($path), true);
        $data = is_array($decoded) ? $decoded : [];
        self::$manifestCache[$entity] = [
            'data' => $data,
            'path' => $path,
            'mtime' => $mtime,
        ];

        return $data;
    }

    /**
     * @param array<string, array{mode: string, type?: string, file?: string}> $manifest
     */
    private static function writeManifest(array $manifest, string $entity = 'alat'): void
    {
        $path = self::manifestPath($entity);
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        self::resetCache($entity);
    }
}
