@extends('dashboard.layout')

@section('title', 'Dashboard')

@section('header', 'Dashboard Utama')

@section('content')
<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col sm:flex-row sm:items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Filter Berdasarkan Tahun</label>
            <select name="tahun" id="tahunFilter" class="h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
                @foreach(($availableYears ?? []) as $y)
                    <option value="{{ $y }}" @selected((int)($tahun ?? now()->year) === (int)$y)>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="h-10 px-4 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200 whitespace-nowrap">
            <i class="fas fa-sort-amount-down mr-2"></i>
            Terapkan Filter
        </button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <!-- Total Volume Muat Seluruh Angkutan Card -->
    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Volume Muat Seluruh Angkutan</p>
                <p class="text-3xl font-bold text-gray-800">{{ number_format(((float) $totalVolumeAll) / 1000, 2) }}</p>
                <p class="text-xs text-gray-500">ton</p>
            </div>
            <div class="w-12 h-12 bg-kai-orange rounded-full flex items-center justify-center">
                <i class="fas fa-train text-white text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-green-600 text-sm">
                <i class="fas fa-arrow-up"></i> 12% dari bulan lalu
            </span>
        </div>
    </div>

    <!-- Total Customer Card -->
    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Customer</p>
                <p class="text-3xl font-bold text-gray-800">{{ $totalCustomer }}</p>
            </div>
            <div class="w-12 h-12 bg-kai-orange rounded-full flex items-center justify-center">
                <i class="fas fa-users text-white text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-green-600 text-sm">
                <i class="fas fa-arrow-up"></i> 8% dari bulan lalu
            </span>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Volume Muat Asal SBI</p>
                <p class="text-3xl font-bold text-gray-800">{{ number_format(((float) $muatVolumeSBI) / 1000, 2) }}</p>
                <p class="text-xs text-gray-500">ton</p>
            </div>
            <div class="w-12 h-12 bg-kai-navy rounded-full flex items-center justify-center">
                <i class="fas fa-train text-white text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-gray-600 text-sm">
                <i class="fas fa-minus"></i> Tidak ada perubahan
            </span>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Volume Muat Asal BBT</p>
                <p class="text-3xl font-bold text-gray-800">{{ number_format(((float) $muatVolumeBBT) / 1000, 2) }}</p>
                <p class="text-xs text-gray-500">ton</p>
            </div>
            <div class="w-12 h-12 bg-kai-navy rounded-full flex items-center justify-center">
                <i class="fas fa-train text-white text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-gray-600 text-sm">
                <i class="fas fa-minus"></i> Tidak ada perubahan
            </span>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Volume Muat Asal BJ</p>
                <p class="text-3xl font-bold text-gray-800">{{ number_format(((float) $muatVolumeBJ) / 1000, 2) }}</p>
                <p class="text-xs text-gray-500">ton</p>
            </div>
            <div class="w-12 h-12 bg-kai-navy rounded-full flex items-center justify-center">
                <i class="fas fa-train text-white text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-gray-600 text-sm">
                <i class="fas fa-minus"></i> Tidak ada perubahan
            </span>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Aksi Cepat</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('input.data') }}" class="flex items-center p-4 kai-orange-gradient rounded-lg hover:opacity-90 transition duration-200 text-white">
            <i class="fas fa-file-import text-white text-2xl mr-3"></i>
            <div>
                <p class="font-semibold text-white">Input Data Baru</p>
                <p class="text-sm text-white/90">Tambah data angkutan</p>
            </div>
        </a>
        
        <a href="{{ route('preview.data') }}" class="flex items-center p-4 kai-navy-gradient rounded-lg hover:opacity-90 transition duration-200 text-white">
            <i class="fas fa-table text-white text-2xl mr-3"></i>
            <div>
                <p class="font-semibold text-white">Lihat Data</p>
                <p class="text-sm text-white/90">Preview semua data</p>
            </div>
        </a>
        
        <a href="{{ route('statistik') }}" class="flex items-center p-4 bg-kai-navy-light rounded-lg hover:opacity-90 transition duration-200 text-white">
            <i class="fas fa-chart-column text-white text-2xl mr-3"></i>
            <div>
                <p class="font-semibold text-white">Lihat Statistik</p>
                <p class="text-sm text-white/90">Analisis data</p>
            </div>
        </a>
    </div>
</div>

<!-- Recent Activity -->
<div class="mt-6 bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Aktivitas Terkini</h3>
    <div class="space-y-3">
        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
            <div class="w-8 h-8 bg-kai-orange rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-train text-white text-sm"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-800">Data angkutan baru ditambahkan</p>
                <p class="text-xs text-gray-500">2 jam yang lalu</p>
            </div>
        </div>
        
        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
            <div class="w-8 h-8 bg-kai-navy rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-user text-white text-sm"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-800">Customer baru terdaftar</p>
                <p class="text-xs text-gray-500">5 jam yang lalu</p>
            </div>
        </div>
        
        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
            <div class="w-8 h-8 bg-kai-orange rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-edit text-white text-sm"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-800">Data angkutan diperbarui</p>
                <p class="text-xs text-gray-500">1 hari yang lalu</p>
            </div>
        </div>
    </div>
</div>
@endsection
