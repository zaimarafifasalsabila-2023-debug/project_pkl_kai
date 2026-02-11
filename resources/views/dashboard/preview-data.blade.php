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
<div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6 reveal">
    <form method="GET" action="{{ route('preview.data') }}" class="flex flex-col sm:flex-row sm:flex-wrap sm:items-end sm:justify-between gap-4">
        <h3 class="text-lg font-semibold text-gray-800">Filter Data</h3>

        <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nomor Sarana</label>
                <input name="nomor_sarana" value="{{ request('nomor_sarana') }}" type="text" placeholder="Cari nomor sarana..." class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Awal</label>
                <input name="tanggal_awal" value="{{ request('tanggal_awal') }}" type="date" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Akhir</label>
                <input name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" type="date" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nama Customer</label>
                <select name="nama_customer" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
                    <option value="">Semua Customer</option>
                    @foreach (($customers ?? collect()) as $customer)
                        <option value="{{ $customer }}" @selected(request('nama_customer') == $customer)>{{ $customer }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Stasiun Asal</label>
                <select name="stasiun_asal_sa" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
                    <option value="">Semua</option>
                    @foreach (($stasiunAsalList ?? collect()) as $st)
                        <option value="{{ $st }}" @selected(request('stasiun_asal_sa') == $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Stasiun Tujuan</label>
                <select name="stasiun_tujuan_sa" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
                    <option value="">Semua</option>
                    @foreach (($stasiunTujuanList ?? collect()) as $st)
                        <option value="{{ $st }}" @selected(request('stasiun_tujuan_sa') == $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Jenis Angkutan</label>
                <select name="jenis_angkutan" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
                    <option value="">Semua</option>
                    <option value="kedatangan" @selected(request('jenis_angkutan') == 'kedatangan')>Kedatangan</option>
                    <option value="muat" @selected(request('jenis_angkutan') == 'muat')>Muat</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status_sa" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent">
                    <option value="">Semua Status</option>
                    @foreach (($statusList ?? collect()) as $st)
                        <option value="{{ $st }}" @selected((string)request('status_sa') === (string)$st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="h-10 px-4 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200 whitespace-nowrap">
                <i class="fas fa-search mr-2"></i>
                Cari
            </button>

            <a href="{{ route('preview.data') }}" class="h-10 px-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200 flex items-center justify-center whitespace-nowrap">
                Reset
            </a>
        </div>
    </form>
</div>

    <!-- Data Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <h3 class="text-lg font-semibold text-gray-800">Data Angkutan</h3>
            <div class="flex flex-wrap gap-2 items-center justify-end">
                <button type="button" id="bulkDeleteBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:opacity-90 transition duration-200">
                    <i class="fas fa-trash mr-2"></i>
                    Hapus Terpilih
                </button>
                <div class="relative">
                    <button type="button" id="exportBtn" class="px-4 py-2 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200 flex items-center">
                        <i class="fas fa-download mr-2"></i>
                        Export Excel
                        <i class="fas fa-chevron-down ml-2 text-sm"></i>
                    </button>
                    <div id="exportMenu" class="absolute right-0 mt-2 w-40 bg-white border border-gray-300 rounded-lg shadow-lg hidden z-10">
                        <button type="button" onclick="exportData('xlsx')" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">
                            <i class="fas fa-file-excel mr-2 text-green-600"></i>
                            Export XLSX
                        </button>
                        <button type="button" onclick="exportData('csv')" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700 border-t border-gray-200">
                            <i class="fas fa-file-csv mr-2 text-blue-600"></i>
                            Export CSV
                        </button>
                    </div>
                </div>
                <button type="button" onclick="printTable()" class="px-4 py-2 kai-navy-gradient text-white rounded-lg hover:opacity-90 transition duration-200">
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input id="selectAll" type="checkbox" class="h-4 w-4" />
                    </th>
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
                <tr class="hover:bg-gray-50" data-row-id="{{ $item->id }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <input type="checkbox" class="row-select h-4 w-4" data-id="{{ $item->id }}" />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ($data->firstItem() ?? 0) + $index }}</td>
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
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ optional($item->tanggal_keberangkatan_asal_ka)->format('Y-m-d') ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->nomor_sarana ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->volume_berat_kai, 2) }} kg</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->banyaknya_pengajuan }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @php($statusSa = strtoupper((string)($item->status_sa ?? '')))
                            @if($statusSa === 'BAB') bg-green-100 text-green-800
                            @elseif($statusSa === 'BKD') bg-blue-100 text-blue-800
                            @elseif($statusSa === 'SA') bg-yellow-100 text-yellow-800
                            @elseif($statusSa === 'BATAL SA') bg-red-100 text-red-800
                            @elseif($statusSa === 'DRAF') bg-gray-100 text-gray-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $item->status_sa ?? '-' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button type="button" data-action="view" data-id="{{ $item->id }}" class="row-action text-kai-orange hover:text-kai-orange-dark mr-3">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" data-action="edit" data-id="{{ $item->id }}" class="row-action text-kai-navy hover:text-kai-navy-dark mr-3">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" data-action="delete" data-id="{{ $item->id }}" class="row-action text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" class="px-6 py-4 text-center text-gray-500">
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
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="text-sm text-gray-700">
                Menampilkan <span class="font-medium">{{ $data->firstItem() ?? 0 }}</span> hingga <span class="font-medium">{{ $data->lastItem() ?? 0 }}</span> dari <span class="font-medium">{{ $data->total() }}</span> data
            </div>
            <div>
                {{ $data->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</div>

<!-- CRUD Modal -->
<div id="crudModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center p-4 z-[9998]">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 id="crudModalTitle" class="text-lg font-semibold text-gray-800">Detail</h3>
            <button type="button" id="crudModalClose" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="p-6">
            <div id="crudModalBody"></div>
            <div id="crudModalError" class="hidden mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
        </div>

        <div class="px-6 py-4 border-t flex justify-end gap-2">
            <button type="button" id="crudModalCancel" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Tutup</button>
            <button type="button" id="crudModalSave" class="hidden px-4 py-2 kai-orange-gradient text-white rounded-lg hover:opacity-90">Simpan</button>
        </div>
    </div>
</div>

<script>
function exportData(format) {
    // Get current query parameters
    const params = new URLSearchParams(window.location.search);
    
    // Build download URL with current filters
    const url = new URL("/preview-data/export/excel", window.location.origin);
    
    // Add all current filter parameters to the export URL
    for (const [key, value] of params) {
        url.searchParams.append(key, value);
    }
    
    // Add format parameter
    url.searchParams.append('format', format);
    
    // Download the file
    window.location.href = url.toString();
}

function printTable() {
    // Get current filter parameters
    const params = new URLSearchParams(window.location.search);
    
    // Build URL to fetch all data with current filters
    const url = new URL("/preview-data", window.location.origin);
    
    // Add all current filter parameters
    for (const [key, value] of params) {
        url.searchParams.append(key, value);
    }
    
    // Remove pagination parameter to get all data
    url.searchParams.delete('page');

    url.searchParams.set('print', 'true');
    
    // Fetch all data
    fetch(url.toString(), {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(responseData => {
        // Create a new window for printing
        const printWindow = window.open('', '', 'width=1200,height=800');
        
        // Prepare table HTML
        let tableHtml = `
            <table style="width:100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="background-color: #f3f4f6;">
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border: 1px solid #d1d5db; font-size: 12px;">Jenis</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border: 1px solid #d1d5db; font-size: 12px;">Customer</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border: 1px solid #d1d5db; font-size: 12px;">Stasiun Asal</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border: 1px solid #d1d5db; font-size: 12px;">Stasiun Tujuan</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border: 1px solid #d1d5db; font-size: 12px;">Nama KA</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border: 1px solid #d1d5db; font-size: 12px;">Tanggal</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border: 1px solid #d1d5db; font-size: 12px;">No. Sarana</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border: 1px solid #d1d5db; font-size: 12px;">Volume (kg)</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border: 1px solid #d1d5db; font-size: 12px;">Koli</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border: 1px solid #d1d5db; font-size: 12px;">Status</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        // Add data rows
        if (responseData.data && responseData.data.length > 0) {
            responseData.data.forEach((item, index) => {
                const rowColor = index % 2 === 0 ? '#ffffff' : '#f9fafb';
                tableHtml += `
                    <tr style="background-color: ${rowColor};">
                        <td style="padding: 12px; border: 1px solid #d1d5db; font-size: 12px;">${item.jenis_angkutan || '-'}</td>
                        <td style="padding: 12px; border: 1px solid #d1d5db; font-size: 12px;">${item.nama_customer || '-'}</td>
                        <td style="padding: 12px; border: 1px solid #d1d5db; font-size: 12px;">${item.stasiun_asal_sa || '-'}</td>
                        <td style="padding: 12px; border: 1px solid #d1d5db; font-size: 12px;">${item.stasiun_tujuan_sa || '-'}</td>
                        <td style="padding: 12px; border: 1px solid #d1d5db; font-size: 12px;">${item.nama_ka_stasiun_asal || '-'}</td>
                        <td style="padding: 12px; border: 1px solid #d1d5db; font-size: 12px;">${item.tanggal_keberangkatan_asal_ka || '-'}</td>
                        <td style="padding: 12px; border: 1px solid #d1d5db; font-size: 12px;">${item.nomor_sarana || '-'}</td>
                        <td style="padding: 12px; border: 1px solid #d1d5db; font-size: 12px; text-align: right;">${parseFloat(item.volume_berat_kai).toLocaleString('id-ID') || '0'}</td>
                        <td style="padding: 12px; border: 1px solid #d1d5db; font-size: 12px; text-align: center;">${item.banyaknya_pengajuan || '-'}</td>
                        <td style="padding: 12px; border: 1px solid #d1d5db; font-size: 12px;">${item.status_sa || '-'}</td>
                    </tr>
                `;
            });
        } else {
            tableHtml += '<tr><td colspan="10" style="padding: 20px; text-align: center; border: 1px solid #d1d5db;">Tidak ada data</td></tr>';
        }
        
        tableHtml += '</tbody></table>';
        
        // Create HTML content for printing
        const html = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Data Angkutan</title>
                <style>
                    body {
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        margin: 20px;
                        background-color: white;
                    }
                    h1 {
                        text-align: center;
                        color: #333;
                        margin-bottom: 20px;
                    }
                    .print-info {
                        text-align: center;
                        color: #666;
                        margin-bottom: 20px;
                        font-size: 12px;
                    }
                    @media print {
                        body { margin: 0; }
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                <h1>Data Angkutan</h1>
                <p class="print-info">Dicetak pada: ${new Date().toLocaleString('id-ID')} | Total Data: ${responseData.data ? responseData.data.length : 0}</p>
                ${tableHtml}
            </body>
            </html>
        `;
        
        // Write content to print window
        printWindow.document.write(html);
        printWindow.document.close();
        
        // Trigger print dialog after a short delay to ensure content is loaded
        setTimeout(() => {
            printWindow.print();
        }, 250);
    })
    .catch(error => {
        console.error('Error fetching data for print:', error);
        alert('Gagal mengambil data untuk cetak. Silakan filter data dan coba lagi.');
    });
}

// Toggle export menu visibility
document.getElementById('exportBtn').addEventListener('click', function(e) {
    e.stopPropagation();
    const menu = document.getElementById('exportMenu');
    menu.classList.toggle('hidden');
});

// Close export menu when clicking outside
document.addEventListener('click', function(e) {
    const exportBtn = document.getElementById('exportBtn');
    const exportMenu = document.getElementById('exportMenu');
    if (!exportBtn.contains(e.target) && !exportMenu.contains(e.target)) {
        exportMenu.classList.add('hidden');
    }
});

// Close export menu after selecting an option
document.getElementById('exportMenu').addEventListener('click', function() {
    setTimeout(() => {
        this.classList.add('hidden');
    }, 100);
});

(function () {
    const csrfToken = "{{ csrf_token() }}";
    const modal = document.getElementById('crudModal');
    const modalTitle = document.getElementById('crudModalTitle');
    const modalBody = document.getElementById('crudModalBody');
    const modalError = document.getElementById('crudModalError');
    const btnClose = document.getElementById('crudModalClose');
    const btnCancel = document.getElementById('crudModalCancel');
    const btnSave = document.getElementById('crudModalSave');

    if (!modal || !modalTitle || !modalBody || !modalError || !btnClose || !btnCancel || !btnSave) {
        return;
    }

    let currentId = null;

    const selectAll = document.getElementById('selectAll');
    const bulkBtn = document.getElementById('bulkDeleteBtn');

    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.row-select:checked'))
            .map((el) => parseInt(el.getAttribute('data-id') || '0', 10))
            .filter((n) => Number.isFinite(n) && n > 0);
    }

    function updateSelectAllState() {
        if (!selectAll) return;
        const boxes = Array.from(document.querySelectorAll('.row-select'));
        const checked = boxes.filter((b) => b.checked);
        if (boxes.length === 0) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
            return;
        }
        selectAll.checked = checked.length === boxes.length;
        selectAll.indeterminate = checked.length > 0 && checked.length < boxes.length;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            const boxes = document.querySelectorAll('.row-select');
            boxes.forEach((b) => { b.checked = selectAll.checked; });
            updateSelectAllState();
        });
    }
    document.querySelectorAll('.row-select').forEach((b) => {
        b.addEventListener('change', updateSelectAllState);
    });

    function showModal() {
        const main = document.querySelector('main');
        if (main && modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function hideModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        currentId = null;
        modalBody.innerHTML = '';
        modalError.classList.add('hidden');
        modalError.textContent = '';
        btnSave.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function setError(msg) {
        modalError.classList.remove('hidden');
        modalError.textContent = String(msg || 'Terjadi kesalahan');
    }

    btnClose.addEventListener('click', hideModal);
    btnCancel.addEventListener('click', hideModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) hideModal();
    });

    async function fetchRow(id) {
        const url = new URL("/preview-data/" + String(id), window.location.origin);
        const res = await fetch(url.toString(), {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        });
        if (!res.ok) {
            throw new Error('Gagal mengambil data');
        }
        const json = await res.json();
        if (!json || !json.data) {
            throw new Error('Data tidak ditemukan');
        }
        return json.data;
    }

    function buildDetailHtml(data) {
        const rows = [
            ['Jenis', data.jenis_angkutan],
            ['Customer', data.nama_customer],
            ['Stasiun Asal', data.stasiun_asal_sa],
            ['Stasiun Tujuan', data.stasiun_tujuan_sa],
            ['Nama KA', data.nama_ka_stasiun_asal],
            ['Tanggal', data.tanggal_keberangkatan_asal_ka],
            ['No. Sarana', data.nomor_sarana],
            ['Volume (kg)', data.volume_berat_kai],
            ['Pengajuan', data.banyaknya_pengajuan],
            ['Status', data.status_sa]
        ];

        return `
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                ${rows.map(([k, v]) => `
                    <div>
                        <div class="text-xs text-gray-500">${k}</div>
                        <div class="text-sm text-gray-900 font-medium">${(v === null || v === undefined || v === '') ? '-' : String(v)}</div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    function buildEditHtml(data) {
        return `
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Customer</label>
                    <input id="f_nama_customer" value="${data.nama_customer || ''}" class="w-full h-10 px-3 border border-gray-300 rounded-lg" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nama KA</label>
                    <input id="f_nama_ka" value="${data.nama_ka_stasiun_asal || ''}" class="w-full h-10 px-3 border border-gray-300 rounded-lg" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Stasiun Asal</label>
                    <input id="f_stasiun_asal" value="${data.stasiun_asal_sa || ''}" class="w-full h-10 px-3 border border-gray-300 rounded-lg" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Stasiun Tujuan</label>
                    <input id="f_stasiun_tujuan" value="${data.stasiun_tujuan_sa || ''}" class="w-full h-10 px-3 border border-gray-300 rounded-lg" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal</label>
                    <input id="f_tanggal" type="date" value="${data.tanggal_keberangkatan_asal_ka || ''}" class="w-full h-10 px-3 border border-gray-300 rounded-lg" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">No. Sarana</label>
                    <input id="f_nomor_sarana" value="${data.nomor_sarana || ''}" class="w-full h-10 px-3 border border-gray-300 rounded-lg" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Volume (kg)</label>
                    <input id="f_volume" type="number" step="0.01" value="${(data.volume_berat_kai === null || data.volume_berat_kai === undefined) ? '' : String(data.volume_berat_kai)}" class="w-full h-10 px-3 border border-gray-300 rounded-lg" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Pengajuan</label>
                    <input id="f_pengajuan" type="number" value="${(data.banyaknya_pengajuan === null || data.banyaknya_pengajuan === undefined) ? '' : String(data.banyaknya_pengajuan)}" class="w-full h-10 px-3 border border-gray-300 rounded-lg" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                    <input id="f_status" value="${data.status_sa || ''}" class="w-full h-10 px-3 border border-gray-300 rounded-lg" />
                </div>
            </div>
        `;
    }

    async function updateRow(id) {
        const url = new URL("/preview-data/" + String(id), window.location.origin);
        const payload = {
            nama_customer: (document.getElementById('f_nama_customer') || {}).value || null,
            nama_ka_stasiun_asal: (document.getElementById('f_nama_ka') || {}).value || null,
            stasiun_asal_sa: (document.getElementById('f_stasiun_asal') || {}).value || null,
            stasiun_tujuan_sa: (document.getElementById('f_stasiun_tujuan') || {}).value || null,
            tanggal_keberangkatan_asal_ka: (document.getElementById('f_tanggal') || {}).value || null,
            nomor_sarana: (document.getElementById('f_nomor_sarana') || {}).value || null,
            volume_berat_kai: (document.getElementById('f_volume') || {}).value || null,
            banyaknya_pengajuan: (document.getElementById('f_pengajuan') || {}).value || null,
            status_sa: (document.getElementById('f_status') || {}).value || null,
        };

        const res = await fetch(url.toString(), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        });

        const json = await res.json();
        if (!res.ok || !json || json.success !== true) {
            throw new Error('Gagal menyimpan perubahan');
        }
        
    }

    async function deleteRow(id) {
        const url = new URL("/preview-data/" + String(id), window.location.origin);
        const res = await fetch(url.toString(), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'same-origin'
        });
        const json = await res.json();
        if (!res.ok || !json || json.success !== true) {
            throw new Error('Gagal menghapus data');
        }
    }

    async function bulkDelete(ids) {
        const url = new URL("/preview-data/bulk-delete", window.location.origin);
        const res = await fetch(url.toString(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'same-origin',
            body: JSON.stringify({ ids })
        });
        const json = await res.json();
        if (!res.ok || !json || json.success !== true) {
            throw new Error((json && json.message) ? json.message : 'Gagal menghapus data');
        }
    }

    document.querySelectorAll('.row-action').forEach((btn) => {
        btn.addEventListener('click', async function () {
            const id = btn.getAttribute('data-id');
            const action = btn.getAttribute('data-action');
            if (!id || !action) return;

            modalError.classList.add('hidden');
            modalError.textContent = '';
            currentId = id;

            if (action === 'delete') {
                const ok = confirm('Yakin ingin menghapus data ini?');
                if (!ok) return;
                try {
                    await deleteRow(id);
                    const tr = document.querySelector('tr[data-row-id="' + String(id) + '"]');
                    if (tr) tr.remove();
                    updateSelectAllState();
                    if (typeof window.showToast === 'function') {
                        window.showToast('success', 'Data berhasil dihapus.');
                    }
                } catch (e) {
                    if (typeof window.showToast === 'function') {
                        window.showToast('error', e.message || String(e));
                    } else {
                        alert(e.message || String(e));
                    }
                }
                return;
            }

            try {
                const data = await fetchRow(id);
                if (action === 'view') {
                    modalTitle.textContent = 'Detail Data';
                    modalBody.innerHTML = buildDetailHtml(data);
                    btnSave.classList.add('hidden');
                    showModal();
                    return;
                }

                if (action === 'edit') {
                    modalTitle.textContent = 'Edit Data';
                    modalBody.innerHTML = buildEditHtml(data);
                    btnSave.classList.remove('hidden');
                    showModal();
                    return;
                }
            } catch (e) {
                modalTitle.textContent = 'Error';
                modalBody.innerHTML = '';
                setError(e.message || String(e));
                showModal();
            }
        });
    });

    btnSave.addEventListener('click', async function () {
        if (!currentId) return;
        modalError.classList.add('hidden');
        modalError.textContent = '';
        try {
            await updateRow(currentId);
            if (typeof window.showToast === 'function') {
                window.showToast('success', 'Data berhasil diperbarui.');
            }
            // Refresh the row in place without full page reload
            const updated = await fetchRow(currentId);
            const tr = document.querySelector('tr[data-row-id="' + String(currentId) + '"]');
            if (tr && updated) {
                // Rebuild row cells using the same structure as the server-rendered table
                const jenisBadge = updated.jenis_angkutan === 'kedatangan' 
                    ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">' + (updated.jenis_angkutan.charAt(0).toUpperCase() + updated.jenis_angkutan.slice(1)) + '</span>'
                    : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">' + (updated.jenis_angkutan.charAt(0).toUpperCase() + updated.jenis_angkutan.slice(1)) + '</span>';
                const statusBadge = (() => {
                    const s = (updated.status_sa || '').toUpperCase();
                    if (s === 'BAB') return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">' + updated.status_sa + '</span>';
                    if (s === 'BKD') return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">' + updated.status_sa + '</span>';
                    if (s === 'SA') return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">' + updated.status_sa + '</span>';
                    if (s === 'BATAL SA') return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">' + updated.status_sa + '</span>';
                    if (s === 'DRAF') return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">' + updated.status_sa + '</span>';
                    return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">' + (updated.status_sa ?? '-') + '</span>';
                })();
                tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <input type="checkbox" class="row-select h-4 w-4" data-id="${updated.id}" />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tr.querySelector('td:nth-child(2)').textContent}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${jenisBadge}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${updated.nama_customer ?? '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${updated.stasiun_asal_sa ?? '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${updated.stasiun_tujuan_sa ?? '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${updated.nama_ka_stasiun_asal ?? '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${updated.tanggal_keberangkatan_asal_ka ?? '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${updated.nomor_sarana ?? '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${parseFloat(updated.volume_berat_kai).toLocaleString('id-ID')} kg</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${updated.banyaknya_pengajuan ?? '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${statusBadge}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button type="button" data-action="view" data-id="${updated.id}" class="row-action text-kai-orange hover:text-kai-orange-dark mr-3">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" data-action="edit" data-id="${updated.id}" class="row-action text-kai-navy hover:text-kai-navy-dark mr-3">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" data-action="delete" data-id="${updated.id}" class="row-action text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                // Re-attach event listeners to new buttons
                tr.querySelectorAll('.row-action').forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        const id = btn.getAttribute('data-id');
                        const action = btn.getAttribute('data-action');
                        if (!id || !action) return;

                        modalError.classList.add('hidden');
                        modalError.textContent = '';
                        currentId = id;

                        if (action === 'delete') {
                            const ok = confirm('Yakin ingin menghapus data ini?');
                            if (!ok) return;
                            // reuse existing delete logic
                            (async () => {
                                try {
                                    await deleteRow(id);
                                    const trDel = document.querySelector('tr[data-row-id="' + String(id) + '"]');
                                    if (trDel) trDel.remove();
                                    updateSelectAllState();
                                    if (typeof window.showToast === 'function') {
                                        window.showToast('success', 'Data berhasil dihapus.');
                                    }
                                } catch (e) {
                                    if (typeof window.showToast === 'function') {
                                        window.showToast('error', e.message || String(e));
                                    }
                                }
                            })();
                            return;
                        }

                        (async () => {
                            try {
                                const data = await fetchRow(id);
                                if (action === 'view') {
                                    modalTitle.textContent = 'Detail Data';
                                    modalBody.innerHTML = buildDetailHtml(data);
                                    btnSave.classList.add('hidden');
                                    showModal();
                                    return;
                                }

                                if (action === 'edit') {
                                    modalTitle.textContent = 'Edit Data';
                                    modalBody.innerHTML = buildEditHtml(data);
                                    btnSave.classList.remove('hidden');
                                    showModal();
                                    return;
                                }
                            } catch (e) {
                                modalTitle.textContent = 'Error';
                                modalBody.innerHTML = '';
                                setError(e.message || String(e));
                                showModal();
                            }
                        })();
                    });
                });
                tr.querySelector('.row-select').addEventListener('change', updateSelectAllState);
            }
            hideModal();
        } catch (e) {
            setError(e.message || String(e));
        }
    });

    if (bulkBtn) {
        bulkBtn.addEventListener('click', async function () {
            const ids = getSelectedIds();
            if (!ids.length) {
                if (typeof window.showToast === 'function') {
                    window.showToast('warning', 'Pilih minimal 1 data untuk dihapus.');
                }
                return;
            }
            const ok = confirm('Yakin ingin menghapus ' + String(ids.length) + ' data terpilih?');
            if (!ok) return;

            try {
                await bulkDelete(ids);
                ids.forEach((id) => {
                    const tr = document.querySelector('tr[data-row-id="' + String(id) + '"]');
                    if (tr) tr.remove();
                });
                if (selectAll) {
                    selectAll.checked = false;
                    selectAll.indeterminate = false;
                }
                if (typeof window.showToast === 'function') {
                    window.showToast('success', 'Data terpilih berhasil dihapus.');
                }
            } catch (e) {
                if (typeof window.showToast === 'function') {
                    window.showToast('error', e.message || String(e));
                } else {
                    alert(e.message || String(e));
                }
            }
        });
    }
})();
</script>
@endsection
