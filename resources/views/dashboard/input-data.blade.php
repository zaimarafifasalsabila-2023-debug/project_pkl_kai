@extends('dashboard.layout')

@section('title', 'Input Data')

@section('header', 'Input Data Angkutan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
            <button onclick="showTab('manual')" id="manual-tab" class="tab-btn py-2 px-1 border-b-2 border-kai-orange font-medium text-sm kai-orange">
                <i class="fas fa-keyboard mr-2"></i>Input Manual
            </button>
            <button onclick="showTab('kedatangan')" id="kedatangan-tab" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                <i class="fas fa-train mr-2"></i>Kedatangan
            </button>
            <button onclick="showTab('muat')" id="muat-tab" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                <i class="fas fa-box mr-2"></i>Muat
            </button>
        </nav>
    </div>

    <!-- Manual Input Tab -->
    <div id="manual-content" class="tab-content">
        <form class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h4 class="font-medium text-gray-700 border-b pb-2">Data Customer</h4>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Customer</label>
                        <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent" placeholder="Masukkan nama customer">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                        <input type="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent" placeholder="Masukkan nomor telepon">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                        <textarea rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent" placeholder="Masukkan alamat customer"></textarea>
                    </div>
                </div>
                <div class="space-y-4">
                    <h4 class="font-medium text-gray-700 border-b pb-2">Data Angkutan</h4>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Resi</label>
                        <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent" placeholder="Masukkan nomor resi">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Barang</label>
                        <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent" placeholder="Masukkan nama barang">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Berat (kg)</label>
                        <input type="number" step="0.1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent" placeholder="Masukkan berat barang">
                    </div>
                </div>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">Reset</button>
                <button type="submit" class="px-6 py-2 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200">
                    <i class="fas fa-save mr-2"></i>Simpan Data
                </button>
            </div>
        </form>
    </div>

    <!-- Kedatangan Tab -->
    <div id="kedatangan-content" class="tab-content hidden">
        <form action="{{ route('upload.kedatangan') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-kai-orange transition duration-200">
                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                <p class="text-lg font-medium text-gray-700 mb-2">Upload File Kedatangan</p>
                <p class="text-sm text-gray-500 mb-4">Format yang didukung: .xlsx, .csv (Maks. 10MB)</p>
                <input type="file" name="file" accept=".xlsx,.csv" class="hidden" id="kedatangan-file" onchange="previewFile(this, 'kedatangan')">
                <label for="kedatangan-file" class="cursor-pointer">
                    <span class="px-6 py-2 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200 inline-block">
                        <i class="fas fa-folder-open mr-2"></i>Pilih File
                    </span>
                </label>
                <div id="kedatangan-filename" class="mt-4 text-sm text-gray-600"></div>
            </div>

            <!-- Preview Area -->
            <div id="kedatangan-preview" class="hidden">
                <h4 class="font-medium text-gray-700 mb-4">Preview 10 Data Teratas:</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr id="kedatangan-headers"></tr>
                        </thead>
                        <tbody id="kedatangan-rows" class="bg-white divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <button type="button" onclick="resetUpload('kedatangan')" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">Reset</button>
                <button type="submit" class="px-6 py-2 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200">
                    <i class="fas fa-upload mr-2"></i>Upload & Simpan
                </button>
            </div>
        </form>
    </div>

    <!-- Muat Tab -->
    <div id="muat-content" class="tab-content hidden">
        <form action="{{ route('upload.muat') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-kai-orange transition duration-200">
                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                <p class="text-lg font-medium text-gray-700 mb-2">Upload File Muat</p>
                <p class="text-sm text-gray-500 mb-4">Format yang didukung: .xlsx, .csv (Maks. 10MB)</p>
                <input type="file" name="file" accept=".xlsx,.csv" class="hidden" id="muat-file" onchange="previewFile(this, 'muat')">
                <label for="muat-file" class="cursor-pointer">
                    <span class="px-6 py-2 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200 inline-block">
                        <i class="fas fa-folder-open mr-2"></i>Pilih File
                    </span>
                </label>
                <div id="muat-filename" class="mt-4 text-sm text-gray-600"></div>
            </div>

            <!-- Preview Area -->
            <div id="muat-preview" class="hidden">
                <h4 class="font-medium text-gray-700 mb-4">Preview 10 Data Teratas:</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr id="muat-headers"></tr>
                        </thead>
                        <tbody id="muat-rows" class="bg-white divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <button type="button" onclick="resetUpload('muat')" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">Reset</button>
                <button type="submit" class="px-6 py-2 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200">
                    <i class="fas fa-upload mr-2"></i>Upload & Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-kai-orange', 'text-kai-orange');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected content
    document.getElementById(tabName + '-content').classList.remove('hidden');
    
    // Add active state to selected tab
    const activeTab = document.getElementById(tabName + '-tab');
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-kai-orange', 'text-kai-orange');
}

function previewFile(input, type) {
    const file = input.files[0];
    if (file) {
        document.getElementById(type + '-filename').textContent = `File: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
        
        // Show loading state
        const previewDiv = document.getElementById(type + '-preview');
        previewDiv.classList.remove('hidden');
        document.getElementById(type + '-headers').innerHTML = '<tr><td colspan="10" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Reading file...</td></tr>';
        document.getElementById(type + '-rows').innerHTML = '';
        
        // Create FormData for AJAX upload
        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');
        
        // Send AJAX request to preview file
        fetch('/preview/upload', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Network response was not ok (HTTP ${response.status})`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayPreview(data.data, type);
            } else {
                // Hide preview and show error
                previewDiv.classList.add('hidden');
                alert('Error reading file: ' + data.error);
                // Reset file input
                input.value = '';
                document.getElementById(type + '-filename').textContent = '';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Hide preview and show error
            previewDiv.classList.add('hidden');
            alert('Error reading file: ' + error.message + '. Please check your file format and try again.');
            // Reset file input
            input.value = '';
            document.getElementById(type + '-filename').textContent = '';
        });
    }
}

function displayPreview(previewData, type) {
    // Show preview area
    document.getElementById(type + '-preview').classList.remove('hidden');
    
    // Check if we have data
    if (!previewData.headers || !previewData.rows || previewData.rows.length === 0) {
        document.getElementById(type + '-headers').innerHTML = '<tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No data found</th></tr>';
        document.getElementById(type + '-rows').innerHTML = '<tr><td class="px-6 py-4 text-center text-gray-500">The file appears to be empty or has no readable data</td></tr>';
        return;
    }
    
    // Display headers
    const headersHtml = previewData.headers.map(h => 
        `<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">${h || 'Unnamed'}</th>`
    ).join('');
    document.getElementById(type + '-headers').innerHTML = headersHtml;
    
    // Display rows
    let rowsHtml = '';
    previewData.rows.forEach((row, index) => {
        rowsHtml += '<tr>';
        previewData.headers.forEach(header => {
            const value = row[header] || '';
            const displayValue = value === '' ? '-' : value;
            rowsHtml += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${displayValue}</td>`;
        });
        rowsHtml += '</tr>';
    });
    
    // Add info about total rows
    if (previewData.rows.length > 0) {
        rowsHtml += `<tr><td colspan="${previewData.headers.length}" class="px-6 py-2 text-center text-xs text-gray-500">Showing first ${previewData.rows.length} rows of data</td></tr>`;
    }
    
    document.getElementById(type + '-rows').innerHTML = rowsHtml;
}

function resetUpload(type) {
    document.getElementById(type + '-file').value = '';
    document.getElementById(type + '-filename').textContent = '';
    document.getElementById(type + '-preview').classList.add('hidden');
}
</script>
@endsection
