@extends('layouts.admin')

@section('title', 'Kelola Peminjaman - Admin (Baca Saja)')

@section('page-icon')
    <i class="fas fa-calendar-check text-gray-700 mr-3"></i>
@endsection

@section('page-title', 'Kelola Peminjaman')

@section('page-subtitle', 'Tampilan Admin (tanpa aksi persetujuan)')

@section('content')
<!-- Tabs -->
<div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
    <div class="p-6 border-b border-gray-200 bg-gray-50">
        <div class="flex flex-wrap gap-2" id="booking-tabs">
            <button onclick="showTab('pending')" id="tab-pending" class="tab-button active px-6 py-3 rounded-lg font-medium bg-white text-gray-900 shadow">
                <i class="fas fa-clock mr-2"></i>Menunggu <span class="ml-1 px-2 py-1 bg-gray-200 text-gray-700 rounded-full text-sm">{{ $pendingCount }}</span>
            </button>
            <button onclick="showTab('approved')" id="tab-approved" class="tab-button px-6 py-3 rounded-lg font-medium text-gray-600">
                <i class="fas fa-check-circle mr-2"></i>Disetujui <span class="ml-1 px-2 py-1 bg-gray-200 text-gray-700 rounded-full text-sm">{{ $approvedCount }}</span>
            </button>
            <button onclick="showTab('rejected')" id="tab-rejected" class="tab-button px-6 py-3 rounded-lg font-medium text-gray-600">
                <i class="fas fa-times-circle mr-2"></i>Ditolak <span class="ml-1 px-2 py-1 bg-gray-200 text-gray-700 rounded-full text-sm">{{ $rejectedCount }}</span>
            </button>
            <button onclick="showTab('all')" id="tab-all" class="tab-button px-6 py-3 rounded-lg font-medium text-gray-600">
                <i class="fas fa-list mr-2"></i>Semua <span class="ml-1 px-2 py-1 bg-gray-200 text-gray-700 rounded-full text-sm">{{ $allCount }}</span>
            </button>
        </div>
    </div>
    
</div>

<!-- Pending Tab (baca saja) -->
<div id="content-pending" class="tab-content">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">User</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Ruangan</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Tanggal Mulai</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Tanggal Selesai</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Keterangan</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($pendingBookings as $booking)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">#{{ $booking->id_booking }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-700 font-bold mr-3">
                                    {{ strtoupper(substr($booking->user->username ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $booking->user->username ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $booking->user->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $booking->room->nama_room ?? 'N/A' }}</div>
                            <div class="text-sm text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i>{{ $booking->room->lokasi ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($booking->tanggal_mulai)->translatedFormat('d M Y') }}</td>
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($booking->tanggal_selesai)->translatedFormat('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">{{ $booking->keterangan }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold"><i class="fas fa-clock mr-1"></i>Menunggu</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                            <p class="text-gray-500 text-lg">Tidak ada peminjaman yang menunggu konfirmasi</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Approved Tab -->
<div id="content-approved" class="tab-content hidden">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">User</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Ruangan</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Tanggal Mulai</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Tanggal Selesai</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Disetujui Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($approvedBookings as $booking)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">#{{ $booking->id_booking }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-700 font-bold mr-3">
                                    {{ strtoupper(substr($booking->user->username ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $booking->user->username ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $booking->user->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $booking->room->nama_room ?? 'N/A' }}</div>
                            <div class="text-sm text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i>{{ $booking->room->lokasi ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($booking->tanggal_mulai)->translatedFormat('d M Y') }}</td>
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($booking->tanggal_selesai)->translatedFormat('d M Y') }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold">
                                <i class="fas fa-user-check mr-1"></i>{{ $booking->petugas->user->username ?? $booking->petugas->nama_petugas ?? 'Admin' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <i class="fas fa-check-circle text-gray-300 text-6xl mb-4"></i>
                            <p class="text-gray-500 text-lg">Tidak ada peminjaman yang disetujui</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Rejected Tab -->
<div id="content-rejected" class="tab-content hidden">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">User</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Ruangan</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Tanggal Mulai</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Tanggal Selesai</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Alasan Ditolak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($rejectedBookings as $booking)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">#{{ $booking->id_booking }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-700 font-bold mr-3">
                                    {{ strtoupper(substr($booking->user->username ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $booking->user->username ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $booking->user->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $booking->room->nama_room ?? 'N/A' }}</div>
                            <div class="text-sm text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i>{{ $booking->room->lokasi ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($booking->tanggal_mulai)->translatedFormat('d M Y') }}</td>
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($booking->tanggal_selesai)->translatedFormat('d M Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="px-4 py-2 bg-gray-50 border-l-4 border-gray-400 rounded">
                                <p class="text-sm text-gray-700"><i class="fas fa-info-circle mr-2"></i>{{ $booking->alasan_tolak ?? 'Tidak ada alasan' }}</p>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <i class="fas fa-times-circle text-gray-300 text-6xl mb-4"></i>
                            <p class="text-gray-500 text-lg">Tidak ada peminjaman yang ditolak</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- All Tab -->
<div id="content-all" class="tab-content hidden">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">User</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Ruangan</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Tanggal Mulai</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Tanggal Selesai</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($allBookings as $booking)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">#{{ $booking->id_booking }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-700 font-bold mr-3">
                                    {{ strtoupper(substr($booking->user->username ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $booking->user->username ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $booking->user->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $booking->room->nama_room ?? 'N/A' }}</div>
                            <div class="text-sm text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i>{{ $booking->room->lokasi ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($booking->tanggal_mulai)->format('d M Y') }}</td>
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($booking->tanggal_selesai)->format('d M Y') }}</td>
                        <td class="px-6 py-4">
                            @if($booking->status == 'proses')
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold"><i class="fas fa-clock mr-1"></i>Menunggu</span>
                            @elseif($booking->status == 'diterima')
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold"><i class="fas fa-check mr-1"></i>Disetujui</span>
                            @else
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold"><i class="fas fa-times mr-1"></i>Ditolak</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <i class="fas fa-list text-gray-300 text-6xl mb-4"></i>
                            <p class="text-gray-500 text-lg">Tidak ada data peminjaman</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // hide contents
    document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
    // reset buttons to inactive
    document.querySelectorAll('#booking-tabs .tab-button').forEach(btn => {
        btn.classList.remove('bg-white','text-gray-900','shadow');
        btn.classList.add('text-gray-600');
    });
    // show active content
    const content = document.getElementById('content-' + tabName);
    if (content) content.classList.remove('hidden');
    // activate button
    const activeBtn = document.getElementById('tab-' + tabName);
    if (activeBtn) {
        activeBtn.classList.remove('text-gray-600');
        activeBtn.classList.add('bg-white','text-gray-900','shadow');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    showTab('pending');
});
</script>
@endsection
