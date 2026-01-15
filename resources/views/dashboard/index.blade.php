@extends('dashboard.layout')

@section('title', 'Dashboard')

@section('header', 'Dashboard Utama')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <!-- Total Angkutan Card -->
    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Angkutan</p>
                <p class="text-3xl font-bold text-gray-800">{{ $totalAngkutan }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-train text-blue-600 text-xl"></i>
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
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-users text-green-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-green-600 text-sm">
                <i class="fas fa-arrow-up"></i> 8% dari bulan lalu
            </span>
        </div>
    </div>

    <!-- Total Station Card -->
    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Stasiun</p>
                <p class="text-3xl font-bold text-gray-800">{{ $totalStation }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-map-marker-alt text-purple-600 text-xl"></i>
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
        <a href="{{ route('input.data') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-200">
            <i class="fas fa-plus-circle text-blue-600 text-2xl mr-3"></i>
            <div>
                <p class="font-semibold text-gray-800">Input Data Baru</p>
                <p class="text-sm text-gray-600">Tambah data angkutan</p>
            </div>
        </a>
        
        <a href="{{ route('preview.data') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition duration-200">
            <i class="fas fa-eye text-green-600 text-2xl mr-3"></i>
            <div>
                <p class="font-semibold text-gray-800">Lihat Data</p>
                <p class="text-sm text-gray-600">Preview semua data</p>
            </div>
        </a>
        
        <a href="{{ route('statistik') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition duration-200">
            <i class="fas fa-chart-bar text-purple-600 text-2xl mr-3"></i>
            <div>
                <p class="font-semibold text-gray-800">Lihat Statistik</p>
                <p class="text-sm text-gray-600">Analisis data</p>
            </div>
        </a>
    </div>
</div>

<!-- Recent Activity -->
<div class="mt-6 bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Aktivitas Terkini</h3>
    <div class="space-y-3">
        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-train text-blue-600 text-sm"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-800">Data angkutan baru ditambahkan</p>
                <p class="text-xs text-gray-500">2 jam yang lalu</p>
            </div>
        </div>
        
        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-user text-green-600 text-sm"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-800">Customer baru terdaftar</p>
                <p class="text-xs text-gray-500">5 jam yang lalu</p>
            </div>
        </div>
        
        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-edit text-purple-600 text-sm"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-800">Data angkutan diperbarui</p>
                <p class="text-xs text-gray-500">1 hari yang lalu</p>
            </div>
        </div>
    </div>
</div>
@endsection
