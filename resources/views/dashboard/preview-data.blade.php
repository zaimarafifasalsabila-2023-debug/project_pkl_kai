@extends('dashboard.layout')

@section('title', 'Preview Data')

@section('header', 'Preview Data Angkutan')

@section('content')
<!-- Success/Error Messages -->
@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
@endif

<!-- Filter Section -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h3 class="text-lg font-semibold text-gray-800">Filter Data</h3>
        <div class="flex flex-wrap gap-3">
            <input type="text" placeholder="Cari nomor resi..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
            <input type="date" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
            <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
                <option value="">Semua Stasiun</option>
                <option value="Gambir">Gambir</option>
                <option value="Bandung">Bandung</option>
                <option value="Yogyakarta">Yogyakarta</option>
                <option value="Surabaya">Surabaya</option>
            </select>
            <button class="px-4 py-2 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200">
                <i class="fas fa-search mr-2"></i>
                Cari
            </button>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Data Angkutan</h3>
            <div class="flex gap-2">
                <button class="px-4 py-2 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200">
                    <i class="fas fa-download mr-2"></i>
                    Export Excel
                </button>
                <button class="px-4 py-2 kai-navy-gradient text-white rounded-lg hover:opacity-90 transition duration-200">
                    <i class="fas fa-print mr-2"></i>
                    Cetak
                </button>
            </div>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Angkutan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stasiun Asal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stasiun Tujuan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama KA</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Sarana</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Volume (kg)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengajuan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($data as $index => $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($item->jenis_angkutan == 'kedatangan') bg-blue-100 text-blue-800 
                            @elseif($item->jenis_angkutan == 'muat') bg-green-100 text-green-800 
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($item->jenis_angkutan) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->nama_customer }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->stasiun_asal_sa }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->stasiun_tujuan_sa ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->nama_ka_stasiun_asal }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->tanggal_keberangkatan_asal_ka ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->nomor_sarana ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->volume_berat_kai, 2) }} kg</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->banyaknya_pengajuan }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($item->status_sa == 'approved') bg-green-100 text-green-800
                            @elseif($item->status_sa == 'pending') bg-yellow-100 text-yellow-800
                            @elseif($item->status_sa == 'rejected') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($item->status_sa ?? 'pending') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button class="text-kai-orange hover:text-kai-orange-dark mr-3">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="text-kai-navy hover:text-kai-navy-dark mr-3">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="px-6 py-4 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2"></i>
                        <p>Belum ada data angkutan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="px-6 py-4 border-t">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Menampilkan <span class="font-medium">1</span> hingga <span class="font-medium">{{ $data->count() }}</span> dari <span class="font-medium">{{ $data->count() }}</span> data
            </div>
            <div class="flex gap-2">
                <button class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="px-3 py-1 bg-kai-orange text-white rounded">1</button>
                <button class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">2</button>
                <button class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">3</button>
                <button class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
