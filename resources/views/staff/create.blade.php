@extends('layouts.admin')

@section('title','Tambah Petugas')
@section('page-title','Tambah Petugas')
@section('page-subtitle','Form menambahkan akun petugas baru')

@section('content')
<div class="max-w-3xl">
  <div class="bg-white shadow rounded-xl p-6">
    <h2 class="text-xl font-bold mb-4 flex items-center"><i class="fas fa-user-plus mr-2"></i>Data Petugas Baru</h2>
    @if($errors->any())
      <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded">
        <p class="text-red-700 font-medium">Periksa kembali input Anda:</p>
        <ul class="list-disc ml-5 text-sm text-red-600 mt-1">
          @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
      </div>
    @endif
    <form method="POST" action="{{ route('staff.store') }}" class="space-y-6">
      @include('staff.create_form_fields')
      <div class="flex justify-end gap-3 pt-4">
        <a href="{{ route('staff.index') }}" class="px-4 py-2 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">Batal</a>
        <button type="submit" class="px-5 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white font-semibold shadow">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection
