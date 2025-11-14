@extends('layouts.admin')

@section('title', 'Peminjaman Ruangan')

@section('content')
<!-- Hapus Bootstrap untuk menghindari bentrok dengan header Tailwind; gunakan Tailwind/utilitas lokal saja. -->
<!-- Page-specific styles (kept minimal). No header overrides here to keep layout consistent. -->

<div class="max-w-7xl mx-auto px-4 py-4">
    <div class="bg-gradient rounded-3 p-4 mb-4 text-white" style="background: linear-gradient(135deg, #f97316, #fb923c);">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="mb-1"><i class="fas fa-calendar-check"></i> Peminjaman Ruangan</h1>
                <p class="mb-0">Pilih ruangan yang ingin Anda booking</p>
            </div>
            <a href="{{ route('user.bookings.history') }}" class="px-3 py-1.5 rounded-lg bg-white text-amber-700 hover:bg-amber-50 text-sm font-semibold shadow">
                <i class="fas fa-history"></i> Riwayat Saya
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 p-3 rounded-lg border border-green-200 bg-green-50 text-green-800 flex items-start justify-between gap-3">
        <span><i class="fas fa-check-circle"></i> {{ session('success') }}</span>
        <button type="button" class="text-green-700/70" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>
    </div>
    @endif

    @isset($counts)
    <div class="mb-4 flex flex-wrap gap-2">
        <span class="inline-flex items-center px-3 py-1.5 rounded-full border border-amber-300 bg-amber-50 text-amber-700 text-sm">
            <i class="fas fa-hourglass-half mr-2"></i> Menunggu: <strong class="ml-1">{{ $counts['proses'] }}</strong>
        </span>
        <span class="inline-flex items-center px-3 py-1.5 rounded-full border border-emerald-300 bg-emerald-50 text-emerald-700 text-sm">
            <i class="fas fa-check-circle mr-2"></i> Diterima: <strong class="ml-1">{{ $counts['diterima'] }}</strong>
        </span>
        <span class="inline-flex items-center px-3 py-1.5 rounded-full border border-rose-300 bg-rose-50 text-rose-700 text-sm">
            <i class="fas fa-times-circle mr-2"></i> Ditolak: <strong class="ml-1">{{ $counts['ditolak'] }}</strong>
        </span>
    </div>
    @endisset

    @if(session('error'))
    <div class="mb-4 p-3 rounded-lg border border-red-200 bg-red-50 text-red-800 flex items-start justify-between gap-3">
        <span><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</span>
        <button type="button" class="text-red-700/70" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-4 p-3 rounded-lg border border-red-200 bg-red-50 text-red-800">
        <div class="font-semibold mb-1"><i class="fas fa-exclamation-triangle"></i> Terjadi kesalahan:</div>
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($rooms as $room)
        <div class="bg-white rounded-xl shadow overflow-hidden flex flex-col">
            <div class="text-white text-center py-6" style="background: linear-gradient(135deg, #f97316, #fb923c);">
                <div class="mb-3"><i class="fas fa-door-open fa-3x"></i></div>
                <h5 class="m-0 text-lg font-semibold">{{ $room->nama_room }}</h5>
            </div>
            <div class="p-4 flex flex-col gap-3">
                <p class="text-gray-600">{{ $room->deskripsi ?? 'Ruangan nyaman' }}</p>

                <div class="rounded-lg p-3 bg-gray-50 border border-gray-200">
                    <small class="text-gray-500 block mb-2"><i class="fas fa-info-circle"></i> Detail Ruangan</small>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-600">Lokasi</span>
                        <strong class="text-gray-800">{{ $room->lokasi ?? '-' }}</strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Kapasitas</span>
                        <strong class="text-gray-800">{{ $room->kapasitas ?? '-' }} orang</strong>
                    </div>
                </div>

                <a class="w-full text-center inline-block text-white font-medium py-2 rounded-lg" style="background: linear-gradient(135deg, #f97316, #fb923c);"
                   href="{{ route('user.slot-booking.show', $room->id_room) }}">
                    <i class="fas fa-calendar-plus"></i> Pilih {{ $room->nama_room }}
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection
