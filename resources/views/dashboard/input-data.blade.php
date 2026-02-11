@extends('dashboard.layout')

@section('title', 'Input Data')

@section('header', 'Input Data Angkutan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
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

    @if(session('warning'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('warning') }}
        </div>
    @endif

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex flex-wrap gap-6">
            <button onclick="showTab('bongkar')" id="bongkar-tab" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                <i class="fas fa-train mr-2"></i>Bongkar
            </button>
            <button onclick="showTab('muat')" id="muat-tab" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                <i class="fas fa-box mr-2"></i>Muat
            </button>
            <button onclick="showTab('target')" id="target-tab" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                <i class="fas fa-bullseye mr-2"></i>Target
            </button>
        </nav>
    </div>

    <!-- Bongkar Tab -->
    <div id="bongkar-content" class="tab-content hidden">
        <form action="{{ route('upload.kedatangan') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 sm:p-8 text-center hover:border-kai-orange transition duration-200">
                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                <p class="text-lg font-medium text-gray-700 mb-2">Upload File Bongkar</p>
                <p class="text-sm text-gray-500 mb-4">Format yang didukung: .xlsx, .csv (Maks. 10MB)</p>
                <input type="file" name="file" accept=".xlsx,.csv" class="hidden" id="bongkar-file" onchange="previewFile(this, 'bongkar')">
                <label for="bongkar-file" class="cursor-pointer">
                    <span class="px-6 py-2 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200 inline-block">
                        <i class="fas fa-folder-open mr-2"></i>Pilih File
                    </span>
                </label>
                <div id="bongkar-filename" class="mt-4 text-sm text-gray-600"></div>
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-end gap-3">
                <button type="button" onclick="resetUpload('bongkar')" class="w-full sm:w-auto px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">Reset</button>
                <button type="submit" class="w-full sm:w-auto px-6 py-2 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200">
                    <i class="fas fa-upload mr-2"></i>Upload &amp; Simpan
                </button>
            </div>

            <!-- Preview Area -->
            <div id="bongkar-preview" class="hidden">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div class="text-sm text-gray-700">
                            <span class="font-semibold">Tahun Program:</span>
                            <span id="bongkar-tahunProgramText">-</span>
                            <span class="mx-2">|</span>
                            <span class="font-semibold">Total Baris:</span>
                            <span id="bongkar-totalRowsText">0</span>
                        </div>
                        <div id="bongkar-previewWarning" class="text-sm text-red-600 hidden"></div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr id="bongkar-headers"></tr>
                        </thead>
                        <tbody id="bongkar-rows" class="bg-white divide-y divide-gray-200"></tbody>
                    </table>
                </div>
                <p class="mt-2 text-xs text-gray-500">Menampilkan 10 baris teratas dari file.</p>
            </div>

            <div id="bongkar-previewError" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
        </form>
    </div>

    <!-- Muat Tab -->
    <div id="muat-content" class="tab-content hidden">
        <form action="{{ route('upload.muat') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 sm:p-8 text-center hover:border-kai-orange transition duration-200">
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

            <div class="flex flex-col sm:flex-row sm:justify-end gap-3">
                <button type="button" onclick="resetUpload('muat')" class="w-full sm:w-auto px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">Reset</button>
                <button type="submit" class="w-full sm:w-auto px-6 py-2 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200">
                    <i class="fas fa-upload mr-2"></i>Upload &amp; Simpan
                </button>
            </div>

            <!-- Preview Area -->
            <div id="muat-preview" class="hidden">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div class="text-sm text-gray-700">
                            <span class="font-semibold">Tahun Program:</span>
                            <span id="muat-tahunProgramText">-</span>
                            <span class="mx-2">|</span>
                            <span class="font-semibold">Total Baris:</span>
                            <span id="muat-totalRowsText">0</span>
                        </div>
                        <div id="muat-previewWarning" class="text-sm text-red-600 hidden"></div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr id="muat-headers"></tr>
                        </thead>
                        <tbody id="muat-rows" class="bg-white divide-y divide-gray-200"></tbody>
                    </table>
                </div>
                <p class="mt-2 text-xs text-gray-500">Menampilkan 10 baris teratas dari file.</p>
            </div>

            <div id="muat-previewError" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
        </form>
    </div>

    <!-- Target Tab -->
    <div id="target-content" class="tab-content hidden">
        <div class="space-y-6">
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 sm:p-8 text-center hover:border-kai-orange transition duration-200">
                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                <p class="text-lg font-medium text-gray-700 mb-2">Upload File Target</p>
                <p class="text-sm text-gray-500 mb-4">Format yang didukung: .xlsx, .csv (Maks. 10MB)</p>

                <input id="targetFile" type="file" accept=".xlsx,.csv" class="hidden" onchange="targetOnFileChange(this)" />
                <label for="targetFile" class="cursor-pointer">
                    <span class="px-6 py-2 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200 inline-block">
                        <i class="fas fa-folder-open mr-2"></i>Pilih File
                    </span>
                </label>
                <div id="target-filename" class="mt-4 text-sm text-gray-600"></div>
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-end gap-3">
                <button type="button" onclick="resetTargetUpload()" class="w-full sm:w-auto px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">Reset</button>
                <button type="button" id="btnTargetUpload" class="w-full sm:w-auto px-6 py-2 kai-orange-gradient text-white rounded-lg hover:opacity-90 transition duration-200" disabled>
                    <i class="fas fa-upload mr-2"></i>Upload &amp; Simpan
                </button>
            </div>

            <div id="target-preview" class="hidden">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div class="text-sm text-gray-700">
                            <span class="font-semibold">Tahun Program:</span>
                            <span id="target-tahunProgramText">-</span>
                            <span class="mx-2">|</span>
                            <span class="font-semibold">Total Baris:</span>
                            <span id="target-totalRowsText">0</span>
                        </div>
                        <div id="target-previewWarning" class="text-sm text-red-600 hidden"></div>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bulan</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tahun Program</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target (kg)</th>
                            </tr>
                        </thead>
                        <tbody id="target-previewTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                    </table>
                </div>

                <p class="mt-2 text-xs text-gray-500">Menampilkan 10 baris teratas dari file.</p>
            </div>

            <div id="target-previewError" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
            <div id="target-previewSuccess" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"></div>
        </div>
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

document.addEventListener('DOMContentLoaded', function() {
    const initialTab = '{{ session('activeTab', 'bongkar') }}' || 'bongkar';
    showTab(initialTab);

    const flashSuccess = @json(session('success'));
    const flashError = @json(session('error'));
    if (flashSuccess && typeof window.showToast === 'function') {
        window.showToast('success', flashSuccess);
    }
    if (flashError && typeof window.showToast === 'function') {
        window.showToast('error', flashError);
    }
});

function previewFile(input, type) {
    const file = input.files[0];
    if (file) {
        document.getElementById(type + '-filename').textContent = `File: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
        
        // Show loading state
        const previewDiv = document.getElementById(type + '-preview');
        previewDiv.classList.remove('hidden');
        document.getElementById(type + '-headers').innerHTML = '<tr><td colspan="10" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Membaca file...</td></tr>';
        document.getElementById(type + '-rows').innerHTML = '';

        const errEl = document.getElementById(type + '-previewError');
        if (errEl) {
            errEl.classList.add('hidden');
            errEl.textContent = '';
        }
        const warnEl = document.getElementById(type + '-previewWarning');
        if (warnEl) {
            warnEl.classList.add('hidden');
            warnEl.textContent = '';
        }
        const yearEl = document.getElementById(type + '-tahunProgramText');
        const totalEl = document.getElementById(type + '-totalRowsText');
        if (yearEl) yearEl.textContent = '-';
        if (totalEl) totalEl.textContent = '0';
        
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

                const totalRows = Number((data.data && data.data.total_rows) ? data.data.total_rows : 0);
                if (typeof window.showToast === 'function') {
                    window.showToast('success', 'Preview berhasil. Total baris: ' + String(totalRows));
                }
            } else {
                // Hide preview and show error
                previewDiv.classList.add('hidden');

                const errEl = document.getElementById(type + '-previewError');
                if (errEl) {
                    errEl.classList.remove('hidden');
                    errEl.textContent = 'Error membaca file: ' + (data.error || 'Unknown error');
                }
                if (typeof window.showToast === 'function') {
                    window.showToast('error', 'Error membaca file: ' + (data.error || 'Unknown error'));
                }
                // Reset file input
                input.value = '';
                document.getElementById(type + '-filename').textContent = '';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Hide preview and show error
            previewDiv.classList.add('hidden');

            const errEl = document.getElementById(type + '-previewError');
            if (errEl) {
                errEl.classList.remove('hidden');
                errEl.textContent = 'Error membaca file: ' + error.message + '. Silakan cek format file dan coba lagi.';
            }
            if (typeof window.showToast === 'function') {
                window.showToast('error', 'Error membaca file: ' + error.message);
            }
            // Reset file input
            input.value = '';
            document.getElementById(type + '-filename').textContent = '';
        });
    }
}

function displayPreview(previewData, type) {
    // Show preview area
    document.getElementById(type + '-preview').classList.remove('hidden');

    const yearEl = document.getElementById(type + '-tahunProgramText');
    const totalEl = document.getElementById(type + '-totalRowsText');
    const warnEl = document.getElementById(type + '-previewWarning');
    if (warnEl) {
        warnEl.classList.add('hidden');
        warnEl.textContent = '';
    }

    const tahunPrograms = Array.isArray(previewData.tahun_programs) ? previewData.tahun_programs : [];
    const totalRows = Number(previewData.total_rows || 0);
    if (yearEl) {
        yearEl.textContent = tahunPrograms.length ? tahunPrograms.join(', ') : '-';
    }
    if (totalEl) {
        totalEl.textContent = String(totalRows);
    }
    if (warnEl && tahunPrograms.length > 1) {
        warnEl.classList.remove('hidden');
        warnEl.textContent = 'Catatan: File berisi beberapa tahun program: ' + tahunPrograms.join(', ') + '. Setelah upload, data akan masuk semua tahun tsb.';
    }
    
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
        rowsHtml += `<tr><td colspan="${previewData.headers.length}" class="px-6 py-2 text-center text-xs text-gray-500">Menampilkan ${previewData.rows.length} baris teratas dari file</td></tr>`;
    }
    
    document.getElementById(type + '-rows').innerHTML = rowsHtml;
}

function resetUpload(type) {
    document.getElementById(type + '-file').value = '';
    document.getElementById(type + '-filename').textContent = '';
    document.getElementById(type + '-preview').classList.add('hidden');

    const errEl = document.getElementById(type + '-previewError');
    if (errEl) {
        errEl.classList.add('hidden');
        errEl.textContent = '';
    }
    const warnEl = document.getElementById(type + '-previewWarning');
    if (warnEl) {
        warnEl.classList.add('hidden');
        warnEl.textContent = '';
    }
    const yearEl = document.getElementById(type + '-tahunProgramText');
    const totalEl = document.getElementById(type + '-totalRowsText');
    if (yearEl) yearEl.textContent = '-';
    if (totalEl) totalEl.textContent = '0';
}

function targetOnFileChange(input) {
    const file = input.files && input.files[0];
    const nameEl = document.getElementById('target-filename');
    if (file) {
        nameEl.textContent = `File: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
    } else {
        nameEl.textContent = '';
    }

    document.getElementById('btnTargetUpload').disabled = true;
    document.getElementById('target-preview').classList.add('hidden');
    document.getElementById('target-previewError').classList.add('hidden');
    document.getElementById('target-previewSuccess').classList.add('hidden');

    if (file && typeof window.targetAutoPreview === 'function') {
        window.targetAutoPreview();
    }
}

function resetTargetUpload() {
    const input = document.getElementById('targetFile');
    input.value = '';
    document.getElementById('target-filename').textContent = '';
    document.getElementById('target-preview').classList.add('hidden');
    document.getElementById('target-previewError').classList.add('hidden');
    document.getElementById('target-previewSuccess').classList.add('hidden');
    document.getElementById('btnTargetUpload').disabled = true;
}

(function () {
    const csrfToken = "{{ csrf_token() }}";
    const previewUrl = "{{ route('preview.target.preview') }}";
    const storeUrl = "{{ route('preview.target.store') }}";

    const btnUpload = document.getElementById('btnTargetUpload');
    const inputFile = document.getElementById('targetFile');

    const previewBox = document.getElementById('target-preview');
    const previewError = document.getElementById('target-previewError');
    const previewSuccess = document.getElementById('target-previewSuccess');
    const tahunProgramText = document.getElementById('target-tahunProgramText');
    const totalRowsText = document.getElementById('target-totalRowsText');
    const previewTableBody = document.getElementById('target-previewTableBody');
    const previewWarning = document.getElementById('target-previewWarning');

    const monthNames = {
        1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April', 5: 'Mei', 6: 'Juni',
        7: 'Juli', 8: 'Agustus', 9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
    };

    function resetTargetMessages() {
        previewError.classList.add('hidden');
        previewError.textContent = '';
        previewSuccess.classList.add('hidden');
        previewSuccess.textContent = '';
        previewWarning.classList.add('hidden');
        previewWarning.textContent = '';
    }

    function setLoading(isLoading) {
        if (btnUpload) {
            btnUpload.disabled = isLoading || btnUpload.disabled;
            btnUpload.style.opacity = (isLoading || btnUpload.disabled) ? '0.7' : '1';
        }
    }

    function buildFormData() {
        const file = inputFile && inputFile.files && inputFile.files[0];
        if (!file) return null;
        const fd = new FormData();
        fd.append('file', file);
        return fd;
    }

    async function postFile(url) {
        const fd = buildFormData();
        if (!fd) throw new Error('Silakan pilih file terlebih dahulu');

        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: fd,
            credentials: 'same-origin'
        });

        return await res.json();
    }

    async function autoPreview() {
        resetTargetMessages();

        try {
            setLoading(true);
            const json = await postFile(previewUrl);

            if (!json || !json.success) {
                throw new Error((json && json.error) ? json.error : 'Gagal preview file');
            }

            const tahunProgram = Number(json.tahun_program || 0);
            const tahunPrograms = Array.isArray(json.tahun_programs) ? json.tahun_programs : (tahunProgram ? [tahunProgram] : []);

            tahunProgramText.textContent = tahunPrograms.length ? tahunPrograms.join(', ') : (tahunProgram ? String(tahunProgram) : '-');
            totalRowsText.textContent = String(json.total_rows || 0);

            previewTableBody.innerHTML = '';
            const rows = json.rows || [];
            for (const r of rows) {
                const tr = document.createElement('tr');

                const tdBulan = document.createElement('td');
                tdBulan.className = 'px-4 py-2 text-sm text-gray-900';
                tdBulan.textContent = monthNames[r.bulan] || String(r.bulan || '');

                const tdTahun = document.createElement('td');
                tdTahun.className = 'px-4 py-2 text-sm text-gray-900';
                tdTahun.textContent = String(r.tahun_program || tahunProgram || '');

                const tdKg = document.createElement('td');
                tdKg.className = 'px-4 py-2 text-sm text-gray-900';
                tdKg.textContent = (r.target_kg || 0).toLocaleString('id-ID');

                tr.appendChild(tdBulan);
                tr.appendChild(tdTahun);
                tr.appendChild(tdKg);
                previewTableBody.appendChild(tr);
            }

            if (tahunPrograms.length > 1) {
                previewWarning.classList.remove('hidden');
                previewWarning.textContent = 'Catatan: File berisi beberapa tahun program: ' + tahunPrograms.join(', ') + '. Setelah upload, data akan masuk semua tahun tsb.';
            }

            previewBox.classList.remove('hidden');
            btnUpload.disabled = false;

            if (typeof window.showToast === 'function') {
                window.showToast('success', 'Preview target berhasil. Total baris: ' + String(json.total_rows || 0));
            }
        } catch (e) {
            previewError.classList.remove('hidden');
            previewError.textContent = e.message || String(e);
            if (typeof window.showToast === 'function') {
                window.showToast('error', e.message || String(e));
            }
        } finally {
            setLoading(false);
        }
    }

    window.targetAutoPreview = autoPreview;

    if (btnUpload) {
        btnUpload.addEventListener('click', async function () {
            resetTargetMessages();

            try {
                setLoading(true);
                const json = await postFile(storeUrl);

                if (!json || !json.success) {
                    throw new Error((json && json.error) ? json.error : 'Gagal menyimpan data');
                }

                previewSuccess.classList.remove('hidden');
                previewSuccess.textContent = json.message || 'Berhasil menyimpan target.';

                if (typeof window.showToast === 'function') {
                    const inserted = Number(json.inserted || 0);
                    const skipped = Number(json.skipped || 0);
                    if (inserted > 0 && skipped > 0) {
                        window.showToast('warning', 'Target: masuk ' + inserted + ', duplikat ' + skipped + '.');
                    } else if (inserted > 0) {
                        window.showToast('success', 'Target: masuk ' + inserted + '.');
                    } else if (skipped > 0) {
                        window.showToast('warning', 'Target: data sudah ada semua. Duplikat ' + skipped + '.');
                    } else {
                        window.showToast('success', json.message || 'Berhasil menyimpan target.');
                    }
                }

                btnUpload.disabled = true;
            } catch (e) {
                previewError.classList.remove('hidden');
                previewError.textContent = e.message || String(e);
                if (typeof window.showToast === 'function') {
                    window.showToast('error', e.message || String(e));
                }
            } finally {
                setLoading(false);
            }
        });
    }
})();
</script>
@endsection
