@extends('layouts.app')

@section('title', $title ?? 'Modul')

@section('content')
    <div class="bg-white border border-[#E5E7EB] rounded-xl p-8 max-w-2xl">
        <div class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold uppercase tracking-wider bg-[#F7F7F8] text-[#6B7280] border border-[#E5E7EB] mb-3">
            Dijadwalkan pada {{ $moduleStage ?? 'Tahap Berikutnya' }}
        </div>
        <h1 class="text-xl font-bold text-[#1F2937]">{{ $title ?? 'Halaman Modul' }}</h1>
        <p class="text-sm text-[#6B7280] mt-2">
            {{ $description ?? 'Route dan proteksi akses role sudah aktif. Implementasi fitur penuh akan dikerjakan pada tahap selanjutnya.' }}
        </p>

        @if (request('q'))
            <p class="text-xs text-[#6B7280] mt-3">
                Kata kunci pencarian: <span class="font-semibold text-[#1F2937]">{{ request('q') }}</span>
            </p>
        @endif

        <div class="mt-6">
            <a
                href="{{ route('dashboard') }}"
                class="inline-flex items-center h-9 px-5 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold transition"
            >
                Kembali ke Dashboard
            </a>
        </div>
    </div>
@endsection
