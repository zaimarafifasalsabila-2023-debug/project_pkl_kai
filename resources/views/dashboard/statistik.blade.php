@extends('dashboard.layout')

@section('title', 'Statistik')

@section('header', 'Dashboard Statistik')

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Pengiriman Bulan Ini</p>
                <p class="text-2xl font-bold text-gray-800">156</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-box text-blue-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Pendapatan Bulan Ini</p>
                <p class="text-2xl font-bold text-gray-800">Rp 45.2M</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-green-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Rata-rata Berat</p>
                <p class="text-2xl font-bold text-gray-800">4.2 kg</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-weight text-purple-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Customer Aktif</p>
                <p class="text-2xl font-bold text-gray-800">89</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                <i class="fas fa-users text-orange-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Monthly Trend Chart -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Trend Pengiriman Bulanan</h3>
        <canvas id="monthlyChart" width="400" height="200"></canvas>
    </div>
    
    <!-- Station Distribution Chart -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribusi per Stasiun</h3>
        <canvas id="stationChart" width="400" height="200"></canvas>
    </div>
</div>

<!-- Service Type Statistics -->
<div class="bg-white rounded-lg shadow-md p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Jenis Layanan</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="text-center">
            <div class="relative inline-flex items-center justify-center w-32 h-32 mb-4">
                <canvas id="regulerChart" width="128" height="128"></canvas>
            </div>
            <h4 class="font-semibold text-gray-800">Reguler</h4>
            <p class="text-2xl font-bold text-blue-600">68%</p>
            <p class="text-sm text-gray-600">106 pengiriman</p>
        </div>
        
        <div class="text-center">
            <div class="relative inline-flex items-center justify-center w-32 h-32 mb-4">
                <canvas id="ekspresChart" width="128" height="128"></canvas>
            </div>
            <h4 class="font-semibold text-gray-800">Ekspres</h4>
            <p class="text-2xl font-bold text-green-600">22%</p>
            <p class="text-sm text-gray-600">34 pengiriman</p>
        </div>
        
        <div class="text-center">
            <div class="relative inline-flex items-center justify-center w-32 h-32 mb-4">
                <canvas id="kargoChart" width="128" height="128"></canvas>
            </div>
            <h4 class="font-semibold text-gray-800">Kargo</h4>
            <p class="text-2xl font-bold text-purple-600">10%</p>
            <p class="text-sm text-gray-600">16 pengiriman</p>
        </div>
    </div>
</div>

<!-- Top Routes -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Rute Terpopuler</h3>
    <div class="space-y-4">
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-train text-blue-600"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Jakarta - Surabaya</p>
                    <p class="text-sm text-gray-600">45 pengiriman</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-lg font-semibold text-gray-800">Rp 15.2M</p>
                <p class="text-sm text-green-600">+12%</p>
            </div>
        </div>
        
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-train text-green-600"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Bandung - Yogyakarta</p>
                    <p class="text-sm text-gray-600">32 pengiriman</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-lg font-semibold text-gray-800">Rp 8.7M</p>
                <p class="text-sm text-green-600">+8%</p>
            </div>
        </div>
        
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-train text-purple-600"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Surabaya - Malang</p>
                    <p class="text-sm text-gray-600">28 pengiriman</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-lg font-semibold text-gray-800">Rp 6.3M</p>
                <p class="text-sm text-red-600">-3%</p>
            </div>
        </div>
    </div>
</div>

<script>
    // Monthly Trend Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            datasets: [{
                label: 'Pengiriman',
                data: [120, 135, 125, 145, 160, 156],
                borderColor: 'rgb(147, 51, 234)',
                backgroundColor: 'rgba(147, 51, 234, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Station Distribution Chart
    const stationCtx = document.getElementById('stationChart').getContext('2d');
    new Chart(stationCtx, {
        type: 'doughnut',
        data: {
            labels: ['Gambir', 'Bandung', 'Yogyakarta', 'Surabaya', 'Malang'],
            datasets: [{
                data: [35, 25, 20, 15, 5],
                backgroundColor: [
                    'rgb(59, 130, 246)',
                    'rgb(34, 197, 94)',
                    'rgb(147, 51, 234)',
                    'rgb(251, 146, 60)',
                    'rgb(239, 68, 68)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Service Charts
    const createDoughnutChart = (elementId, percentage, color) => {
        const ctx = document.getElementById(elementId).getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [percentage, 100 - percentage],
                    backgroundColor: [color, '#e5e7eb'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
    };

    createDoughnutChart('regulerChart', 68, 'rgb(59, 130, 246)');
    createDoughnutChart('ekspresChart', 22, 'rgb(34, 197, 94)');
    createDoughnutChart('kargoChart', 10, 'rgb(147, 51, 234)');
</script>
@endsection
