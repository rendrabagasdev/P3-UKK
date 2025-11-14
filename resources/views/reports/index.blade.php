@extends('layouts.admin')

@section('title', 'Laporan Peminjaman')

@section('content')
<div class="space-y-6">
  <!-- Page Header -->
  <div class="bg-gray-700 rounded-2xl p-8 shadow-lg mb-6">
    <div class="flex items-center gap-4">
      <div class="p-4 bg-white/20 text-white rounded-xl">
        <i class="fas fa-chart-bar text-2xl"></i>
      </div>
      <div>
        <h1 class="text-3xl font-bold text-white">Laporan Peminjaman</h1>
        <p class="text-gray-300 text-lg">Filter dan lihat laporan peminjaman ruangan</p>
      </div>
    </div>
  </div>

  <div class="bg-white p-8 rounded-xl shadow-md border border-gray-100">
    <h2 class="text-2xl font-bold mb-6 text-gray-900"><i class="fas fa-filter mr-2 text-gray-700"></i>Filter Laporan</h2>
    <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-5">
      <!-- Box: Dari Tanggal -->
      <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 h-full flex flex-col justify-between">
        <label class="block text-base font-semibold text-gray-700 mb-2">Dari Tanggal</label>
        <input type="date" name="from" value="{{ $filters['from'] }}" class="w-full rounded-lg px-3 py-2 text-base bg-white border border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200" />
      </div>
      <!-- Box: Sampai Tanggal -->
      <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 h-full flex flex-col justify-between">
        <label class="block text-base font-semibold text-gray-700 mb-2">Sampai Tanggal</label>
        <input type="date" name="to" value="{{ $filters['to'] }}" class="w-full rounded-lg px-3 py-2 text-base bg-white border border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200" />
      </div>
      <!-- Box: Ruangan -->
      <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 h-full flex flex-col justify-between">
        <label class="block text-base font-semibold text-gray-700 mb-2">Ruangan</label>
        <select name="room" class="w-full rounded-lg px-3 py-2 text-base bg-white border border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
          <option value="">Semua</option>
          @foreach($rooms as $r)
            <option value="{{ $r->id_room }}" {{ (string)$filters['room'] === (string)$r->id_room ? 'selected' : '' }}>{{ $r->nama_room }}</option>
          @endforeach
        </select>
      </div>
      <!-- Box: Status -->
      <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 h-full flex flex-col justify-between">
        <label class="block text-base font-semibold text-gray-700 mb-2">Status</label>
        <select name="status" class="w-full rounded-lg px-3 py-2 text-base bg-white border border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
          <option value="">Semua</option>
          <option value="proses" {{ $filters['status']==='proses' ? 'selected' : '' }}>Proses</option>
          <option value="diterima" {{ $filters['status']==='diterima' ? 'selected' : '' }}>Diterima</option>
          <option value="ditolak" {{ $filters['status']==='ditolak' ? 'selected' : '' }}>Ditolak</option>
          <option value="selesai" {{ $filters['status']==='selesai' ? 'selected' : '' }}>Selesai</option>
        </select>
      </div>
      <!-- Box: Actions -->
      <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 h-full flex items-end justify-between gap-3">
        <a href="{{ route('reports.export', request()->query()) }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 font-semibold shadow">Ekspor CSV</a>
        <button type="submit" class="px-5 py-2 gradient-bg text-white rounded-xl hover:shadow-xl font-bold shadow transition-all">
          <i class="fas fa-check mr-2"></i> Terapkan
        </button>
      </div>
    </form>
  </div>

  <div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="px-6 py-4 border-b">
      <h3 class="font-semibold text-gray-800">Hasil</h3>
    </div>
    <div class="p-0 overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">ID</th>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Tanggal</th>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Ruangan</th>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Peminjam</th>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Petugas</th>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Status</th>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Keterangan</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @forelse($bookings as $b)
          <tr>
            <td class="px-4 py-2">#{{ $b->id_booking }}</td>
            <td class="px-4 py-2">
            {{ \Carbon\Carbon::parse($b->tanggal_mulai)->translatedFormat('d M Y') }} -
            {{ \Carbon\Carbon::parse($b->tanggal_selesai)->translatedFormat('d M Y') }}
            </td>
            <td class="px-4 py-2">{{ $b->room->nama_room ?? '-' }}</td>
            <td class="px-6 py-4 text-base font-medium text-gray-900">{{ $b->user->username ?? ($b->user->nama ?? '-') }}</td>
            <td class="px-6 py-4 text-base text-gray-900">{{ $b->petugas->nama_petugas ?? '-' }}</td>
            <td class="px-6 py-4">
              @if($b->status=='proses')
                <span class="px-3 py-1.5 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">Proses</span>
                @elseif($b->status=='diterima')
                  <span class="px-3 py-1.5 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">Disetujui</span>
              @elseif($b->status=='ditolak')
                <span class="px-3 py-1.5 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">Ditolak</span>
              @elseif($b->status=='selesai')
                <span class="px-3 py-1.5 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">Selesai</span>
              @else
                <span class="px-3 py-1.5 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($b->status) }}</span>
              @endif
            </td>
            <td class="px-6 py-4 max-w-xs truncate text-base text-gray-700" title="{{ $b->keterangan }}">{{ $b->keterangan }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="px-6 py-8 text-center text-gray-500 text-base">Tidak ada data.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="px-6 py-4 border-t">
      {{ $bookings->links() }}
    </div>
  </div>
</div>
@endsection
