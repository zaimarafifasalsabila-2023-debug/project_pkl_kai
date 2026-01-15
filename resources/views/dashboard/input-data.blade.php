@extends('dashboard.layout')

@section('title', 'Input Data')

@section('header', 'Input Data Angkutan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-6">Form Input Data Angkutan</h3>
    
    <form class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Data Customer -->
            <div class="space-y-4">
                <h4 class="font-medium text-gray-700 border-b pb-2">Data Customer</h4>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Customer</label>
                    <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Masukkan nama customer">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                    <input type="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Masukkan nomor telepon">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                    <textarea rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Masukkan alamat customer"></textarea>
                </div>
            </div>
            
            <!-- Data Angkutan -->
            <div class="space-y-4">
                <h4 class="font-medium text-gray-700 border-b pb-2">Data Angkutan</h4>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Resi</label>
                    <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Masukkan nomor resi">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Barang</label>
                    <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Masukkan nama barang">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Berat (kg)</label>
                    <input type="number" step="0.1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Masukkan berat barang">
                </div>
            </div>
        </div>
        
        <!-- Data Pengiriman -->
        <div class="space-y-4">
            <h4 class="font-medium text-gray-700 border-b pb-2">Data Pengiriman</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Stasiun Asal</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Pilih stasiun asal</option>
                        <option value="Gambir">Gambir</option>
                        <option value="Bandung">Bandung</option>
                        <option value="Yogyakarta">Yogyakarta</option>
                        <option value="Surabaya">Surabaya</option>
                        <option value="Malang">Malang</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Stasiun Tujuan</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Pilih stasiun tujuan</option>
                        <option value="Gambir">Gambir</option>
                        <option value="Bandung">Bandung</option>
                        <option value="Yogyakarta">Yogyakarta</option>
                        <option value="Surabaya">Surabaya</option>
                        <option value="Malang">Malang</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Keberangkatan</label>
                    <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Layanan</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Pilih layanan</option>
                        <option value="reguler">Reguler</option>
                        <option value="ekspres">Ekspres</option>
                        <option value="kargo">Kargo</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Biaya</label>
                    <input type="number" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Masukkan total biaya">
                </div>
            </div>
        </div>
        
        <!-- Catatan -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Tambahan</label>
            <textarea rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Masukkan catatan tambahan (opsional)"></textarea>
        </div>
        
        <!-- Buttons -->
        <div class="flex justify-end space-x-4">
            <button type="button" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                Reset
            </button>
            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition duration-200">
                <i class="fas fa-save mr-2"></i>
                Simpan Data
            </button>
        </div>
    </form>
</div>
@endsection
