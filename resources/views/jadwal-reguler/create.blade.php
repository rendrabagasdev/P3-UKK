@extends('layouts.admin')

@section('title', 'Tambah Hari Nonaktif (Reguler)')
@section('subtitle', 'Blok tanggal penuh (full-day) untuk ruangan')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">
  <div class="bg-white rounded-2xl shadow-lg p-8">
  <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3"><i class="fas fa-calendar-check text-orange-600 text-xl"></i> Form Hari Nonaktif (Reguler)</h2>
    @if($errors->any())
      <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg mb-4">
        <ul class="list-disc ml-5 text-sm">
          @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    <form method="POST" action="{{ route('jadwal-reguler.store') }}" class="space-y-6">
      @csrf
      <div class="grid md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Blok/Hari Nonaktif</label>
          <input type="text" name="nama_reguler" value="{{ old('nama_reguler') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none" placeholder="Contoh: Kuliah Rutin" required>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Ruangan</label>
          <select name="id_room" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none" required>
            <option value="">-- Pilih Ruangan --</option>
            @foreach($rooms as $r)
              <option value="{{ $r->id_room }}" @selected(old('id_room')==$r->id_room)>{{ $r->nama_room }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Mulai</label>
          <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none" required>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Selesai</label>
          <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none" required>
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Keterangan (opsional)</label>
          <textarea name="keterangan" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none" placeholder="Detail tambahan">{{ old('keterangan') }}</textarea>
        </div>
      </div>
      <div class="flex gap-3">
        <button class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg shadow-md transition"><i class="fas fa-save mr-2"></i> Simpan</button>
        <a href="{{ route('jadwal-reguler.index') }}" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition"><i class="fas fa-arrow-left mr-2"></i> Batal</a>
      </div>
    </form>
  </div>
  <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-6 py-4 rounded-xl text-sm leading-relaxed">
    Jadwal reguler akan memblokir ruangan sepanjang hari untuk rentang tanggal yang dipilih. Pengajuan peminjaman yang bertabrakan akan otomatis ditolak.
  </div>
</div>
@endsection
