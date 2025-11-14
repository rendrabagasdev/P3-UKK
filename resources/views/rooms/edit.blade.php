@extends('layouts.admin')

@section('title', 'Ubah Ruangan')

@section('page-icon')
    <i class="fas fa-edit text-gray-700 mr-3"></i>
@endsection

@section('page-title', 'Ubah Ruangan')

@section('page-subtitle', 'Perbarui informasi ruangan')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-edit mr-3 text-gray-600"></i>
                Form Ubah Ruangan
            </h3>
        </div>

        <!-- Body -->
        <div class="p-8">
            <form action="{{ route('rooms.update', $room->id_room) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Nama Ruangan -->
                <div class="mb-6">
                    <label for="nama_room" class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-door-open text-gray-600 mr-2"></i>
                        Nama Ruangan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-gray-500 focus:outline-none transition-all @error('nama_room') border-red-500 @enderror" 
                           id="nama_room" 
                           name="nama_room" 
                           value="{{ old('nama_room', $room->nama_room) }}" 
                           placeholder="Contoh: Ruang Meeting A"
                           required>
                    @error('nama_room')
                        <p class="text-red-500 text-sm mt-1">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Lokasi -->
                <div class="mb-6">
                    <label for="lokasi" class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-map-marker-alt text-gray-600 mr-2"></i>
                        Lokasi <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-gray-500 focus:outline-none transition-all @error('lokasi') border-red-500 @enderror" 
                           id="lokasi" 
                           name="lokasi" 
                           value="{{ old('lokasi', $room->lokasi) }}" 
                           placeholder="Contoh: Lantai 2, Gedung A"
                           required>
                    @error('lokasi')
                        <p class="text-red-500 text-sm mt-1">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Kapasitas -->
                <div class="mb-6">
                    <label for="kapasitas" class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-users text-gray-600 mr-2"></i>
                        Kapasitas (Orang) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-gray-500 focus:outline-none transition-all @error('kapasitas') border-red-500 @enderror" 
                           id="kapasitas" 
                           name="kapasitas" 
                           value="{{ old('kapasitas', $room->kapasitas) }}" 
                           placeholder="Contoh: 20"
                           min="1"
                           required>
                    @error('kapasitas')
                        <p class="text-red-500 text-sm mt-1">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Info biaya dihapus per permintaan -->

                <!-- Deskripsi -->
                <div class="mb-8">
                    <label for="deskripsi" class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-info-circle text-gray-600 mr-2"></i>
                        Deskripsi/Fasilitas <span class="text-red-500">*</span>
                    </label>
                    <textarea class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-gray-500 focus:outline-none transition-all @error('deskripsi') border-red-500 @enderror" 
                              id="deskripsi" 
                              name="deskripsi" 
                              rows="5" 
                              placeholder="Contoh: Dilengkapi proyektor, AC, dan whiteboard"
                              required>{{ old('deskripsi', $room->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <p class="text-red-500 text-sm mt-1">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex gap-4">
                    <a href="{{ route('rooms.index') }}" class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-all duration-300 transform hover:scale-105 shadow-md">
                        <i class="fas fa-arrow-left mr-2"></i> Batal
                    </a>
                    <button type="submit" class="flex-1 px-6 py-3 gradient-bg hover:shadow-xl text-white rounded-xl transition-all duration-300 font-bold transform hover:scale-105">
                        <i class="fas fa-save mr-2"></i> Perbarui Ruangan
                    </button>
                </div>
            </form>
        </div>
    </div>
    
</div>
@endsection
