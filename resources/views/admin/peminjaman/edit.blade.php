@extends('layouts.app')

@section('title', 'Edit Peminjaman')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-3">
        <div>
            <nav class="text-xs text-[#6B7280] mb-1">
                <a href="{{ route('admin.peminjaman.index') }}" class="hover:text-[#E53935] transition">Data Peminjaman</a>
                <span class="mx-1.5">/</span>
                <span class="text-[#1F2937] font-medium">Edit #PMJ-{{ $peminjaman->id_peminjaman }}</span>
            </nav>
            <h1 class="text-xl font-bold text-[#1F2937]">Edit Peminjaman #PMJ-{{ $peminjaman->id_peminjaman }}</h1>
        </div>
        <a
            href="{{ route('admin.peminjaman.index') }}"
            class="inline-flex items-center h-9 px-4 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition"
        >
            Kembali
        </a>
    </div>

    <div class="lp-panel-lg max-w-3xl bg-white border border-[#E5E7EB] rounded-xl p-6">
        <form method="POST" action="{{ route('admin.peminjaman.update', $peminjaman) }}">
            @csrf
            @method('PUT')
            @include('admin.peminjaman._form', [
                'peminjaman' => $peminjaman,
                'submitLabel' => 'Simpan Perubahan',
            ])
        </form>
    </div>
@endsection
