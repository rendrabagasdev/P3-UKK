@extends('layouts.admin')

@section('title', 'Dashboard Petugas')

@section('content')
<!-- Banner Selamat Datang -->
<div class="bg-gray-700 rounded-2xl p-8 mb-6 shadow-lg">
  <h2 class="text-3xl font-bold text-white">Halo, {{ Auth::user()->username }} 👋</h2>
  <p class="text-gray-300 mt-2">Kelola peminjaman ruangan dengan mudah</p>
</div>

<!-- Statistik Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
  <div class="bg-white rounded-xl p-6 shadow-md border-l-4 border-amber-500">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-gray-500 text-sm font-semibold">Menunggu Persetujuan</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $pendingBookings }}</p>
      </div>
      <div class="bg-amber-100 p-3 rounded-lg">
        <i class="fas fa-clock text-2xl text-amber-600"></i>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-xl p-6 shadow-md border-l-4 border-emerald-500">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-gray-500 text-sm font-semibold">Disetujui</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $approvedBookings }}</p>
      </div>
      <div class="bg-emerald-100 p-3 rounded-lg">
        <i class="fas fa-check-circle text-2xl text-emerald-600"></i>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-xl p-6 shadow-md border-l-4 border-rose-500">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-gray-500 text-sm font-semibold">Ditolak</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $rejectedBookings }}</p>
      </div>
      <div class="bg-rose-100 p-3 rounded-lg">
        <i class="fas fa-times-circle text-2xl text-rose-600"></i>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-xl p-6 shadow-md border-l-4 border-indigo-500">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-gray-500 text-sm font-semibold">Total Ruangan</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalRooms }}</p>
      </div>
      <div class="bg-indigo-100 p-3 rounded-lg">
        <i class="fas fa-door-open text-2xl text-indigo-600"></i>
      </div>
    </div>
  </div>
</div>

<!-- Menu Cepat -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
  <a href="{{ route('bookings.index') }}" class="bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition text-center group">
    <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-indigo-600 transition">
      <i class="fas fa-clipboard-check text-2xl text-indigo-600 group-hover:text-white"></i>
    </div>
    <p class="font-bold text-gray-900">Kelola Peminjaman</p>
    <p class="text-sm text-gray-500 mt-1">Setujui atau tolak permintaan</p>
  </a>

  <a href="{{ route('schedule.index') }}" class="bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition text-center group">
    <div class="bg-emerald-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-emerald-600 transition">
      <i class="fas fa-calendar text-2xl text-emerald-600 group-hover:text-white"></i>
    </div>
    <p class="font-bold text-gray-900">Jadwal Reguler</p>
    <p class="text-sm text-gray-500 mt-1">Lihat jadwal ruangan</p>
  </a>

  <a href="{{ route('reports.index') }}" class="bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition text-center group">
    <div class="bg-amber-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-amber-600 transition">
      <i class="fas fa-chart-bar text-2xl text-amber-600 group-hover:text-white"></i>
    </div>
    <p class="font-bold text-gray-900">Laporan</p>
    <p class="text-sm text-gray-500 mt-1">Lihat statistik peminjaman</p>
  </a>
</div>
@endsection
