<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Angkutan;
use App\Models\Customer;
use App\Models\Station;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalAngkutan = Angkutan::count();
        $totalCustomer = Customer::count();
        $totalStation = Station::count();

        return view('dashboard.index', compact('totalAngkutan', 'totalCustomer', 'totalStation'));
    }

    public function exportDashboardExcel(Request $request)
    {
        $now = now();
        $tahun = (int) ($request->input('tahun') ?? $now->year);
        $bulanInput = $request->input('bulan');
        $bulan = $bulanInput === null ? $now->month : ($bulanInput === '' ? null : (int) $bulanInput);
        $jenis = $request->input('jenis');
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        $query = Angkutan::query();

        if (!empty($jenis)) {
            $query->where('jenis_angkutan', $jenis);
        }

        if (!empty($tanggalAwal) && !empty($tanggalAkhir)) {
            $query->whereBetween('tanggal_keberangkatan_asal_ka', [$tanggalAwal, $tanggalAkhir]);
        } else {
            $query->whereYear('tanggal_keberangkatan_asal_ka', $tahun);
            if (!empty($bulan)) {
                $query->whereMonth('tanggal_keberangkatan_asal_ka', (int) $bulan);
            }
        }

        if ($request->filled('status_sa')) {
            $query->where('status_sa', $request->string('status_sa')->toString());
        }

        if ($request->filled('customer')) {
            $query->where('nama_customer', $request->string('customer')->toString());
        }

        if ($request->filled('search')) {
            $s = $request->string('search')->toString();
            $query->where(function ($q) use ($s) {
                $q->where('nomor_sarana', 'like', '%' . $s . '%')
                    ->orWhere('nama_ka_stasiun_asal', 'like', '%' . $s . '%')
                    ->orWhere('stasiun_asal_sa', 'like', '%' . $s . '%')
                    ->orWhere('stasiun_tujuan_sa', 'like', '%' . $s . '%')
                    ->orWhere('nama_customer', 'like', '%' . $s . '%');
            });
        }

        $rows = $query
            ->orderBy('tanggal_keberangkatan_asal_ka', 'desc')
            ->get();

        $fileName = 'dashboard-operasional.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Tanggal',
                'Customer',
                'Stasiun Asal',
                'Stasiun Tujuan',
                'Nama KA',
                'Nomor Sarana',
                'Volume (kg)',
                'Koli',
                'Status',
                'Jenis'
            ]);

            foreach ($rows as $item) {
                fputcsv($out, [
                    optional($item->tanggal_keberangkatan_asal_ka)->format('Y-m-d'),
                    $item->nama_customer,
                    $item->stasiun_asal_sa,
                    $item->stasiun_tujuan_sa,
                    $item->nama_ka_stasiun_asal,
                    $item->nomor_sarana,
                    (string) $item->volume_berat_kai,
                    (string) $item->banyaknya_pengajuan,
                    (string) $item->status_sa,
                    (string) $item->jenis_angkutan,
                ]);
            }
            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportDashboardPdf(Request $request)
    {
        $now = now();
        $tahun = (int) ($request->input('tahun') ?? $now->year);
        $bulanInput = $request->input('bulan');
        $bulan = $bulanInput === null ? $now->month : ($bulanInput === '' ? null : (int) $bulanInput);
        $jenis = $request->input('jenis');
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        $query = Angkutan::query();

        if (!empty($jenis)) {
            $query->where('jenis_angkutan', $jenis);
        }

        if (!empty($tanggalAwal) && !empty($tanggalAkhir)) {
            $query->whereBetween('tanggal_keberangkatan_asal_ka', [$tanggalAwal, $tanggalAkhir]);
        } else {
            $query->whereYear('tanggal_keberangkatan_asal_ka', $tahun);
            if (!empty($bulan)) {
                $query->whereMonth('tanggal_keberangkatan_asal_ka', (int) $bulan);
            }
        }

        if ($request->filled('status_sa')) {
            $query->where('status_sa', $request->string('status_sa')->toString());
        }

        if ($request->filled('customer')) {
            $query->where('nama_customer', $request->string('customer')->toString());
        }

        if ($request->filled('search')) {
            $s = $request->string('search')->toString();
            $query->where(function ($q) use ($s) {
                $q->where('nomor_sarana', 'like', '%' . $s . '%')
                    ->orWhere('nama_ka_stasiun_asal', 'like', '%' . $s . '%')
                    ->orWhere('stasiun_asal_sa', 'like', '%' . $s . '%')
                    ->orWhere('stasiun_tujuan_sa', 'like', '%' . $s . '%')
                    ->orWhere('nama_customer', 'like', '%' . $s . '%');
            });
        }

        $rows = $query
            ->orderBy('tanggal_keberangkatan_asal_ka', 'desc')
            ->limit(2000)
            ->get();

        return view('dashboard.export-pdf', compact('rows', 'tahun', 'bulan', 'jenis', 'tanggalAwal', 'tanggalAkhir'));
    }

    public function inputData()
    {
        return view('dashboard.input-data');
    }

    public function previewData(Request $request)
    {
        $query = Angkutan::query();

        if ($request->filled('nomor_sarana')) {
            $query->where('nomor_sarana', 'like', '%' . $request->string('nomor_sarana')->toString() . '%');
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_keberangkatan_asal_ka', $request->string('tanggal')->toString());
        }

        if ($request->filled('nama_customer')) {
            $query->where('nama_customer', 'like', '%' . $request->string('nama_customer')->toString() . '%');
        }

        if ($request->filled('stasiun_asal_sa')) {
            $query->where('stasiun_asal_sa', $request->string('stasiun_asal_sa')->toString());
        }

        if ($request->filled('stasiun_tujuan_sa')) {
            $query->where('stasiun_tujuan_sa', $request->string('stasiun_tujuan_sa')->toString());
        }

        if ($request->filled('jenis_angkutan')) {
            $query->where('jenis_angkutan', $request->string('jenis_angkutan')->toString());
        }

        $data = $query
            ->orderBy('tanggal_keberangkatan_asal_ka', 'desc')
            ->paginate(20)
            ->withQueryString();

        $customers = Customer::query()
            ->select('nama_customer')
            ->distinct()
            ->orderBy('nama_customer')
            ->pluck('nama_customer');

        $stasiunAsalList = Angkutan::query()
            ->select('stasiun_asal_sa')
            ->whereNotNull('stasiun_asal_sa')
            ->distinct()
            ->orderBy('stasiun_asal_sa')
            ->pluck('stasiun_asal_sa');

        $stasiunTujuanList = Angkutan::query()
            ->select('stasiun_tujuan_sa')
            ->whereNotNull('stasiun_tujuan_sa')
            ->distinct()
            ->orderBy('stasiun_tujuan_sa')
            ->pluck('stasiun_tujuan_sa');

        return view('dashboard.preview-data', compact('data', 'customers', 'stasiunAsalList', 'stasiunTujuanList'));
    }

    public function statistik(Request $request)
    {
        $now = now();

        $tahunKedatangan = (int) ($request->input('tahun_kedatangan') ?? $now->year);
        $tahunMuat = (int) ($request->input('tahun_muat') ?? $now->year);

        $mitraTahun = (int) ($request->input('mitra_tahun') ?? $now->year);
        $mitraBulan = (int) ($request->input('mitra_bulan') ?? $now->month);

        $saTahun = (int) ($request->input('sa_tahun') ?? $now->year);
        $saBulan = (int) ($request->input('sa_bulan') ?? $now->month);

        $topCustomerJenis = (string) ($request->input('top_customer_jenis') ?? 'kedatangan');
        $topCustomerMode = (string) ($request->input('top_customer_mode') ?? 'volume');
        $topCustomerTahun = (int) ($request->input('top_customer_tahun') ?? $now->year);
        $topCustomerBulan = (int) ($request->input('top_customer_bulan') ?? $now->month);

        if (!in_array($topCustomerJenis, ['kedatangan', 'muat'], true)) {
            $topCustomerJenis = 'kedatangan';
        }
        if (!in_array($topCustomerMode, ['volume', 'sa'], true)) {
            $topCustomerMode = 'volume';
        }

        $bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $buildMonthlyVolumeByStation = function (int $tahun, string $jenisAngkutan, string $field, string $stationCode) {
            $rows = Angkutan::query()
                ->selectRaw('MONTH(tanggal_keberangkatan_asal_ka) as bulan, SUM(volume_berat_kai) as total')
                ->where('jenis_angkutan', $jenisAngkutan)
                ->whereYear('tanggal_keberangkatan_asal_ka', $tahun)
                ->whereNotNull('tanggal_keberangkatan_asal_ka')
                ->whereRaw('UPPER(' . $field . ') LIKE ?', ['%' . strtoupper($stationCode) . '%'])
                ->groupBy('bulan')
                ->get();

            $data = array_fill(0, 12, 0.0);
            foreach ($rows as $r) {
                $idx = ((int) $r->bulan) - 1;
                if ($idx >= 0 && $idx < 12) {
                    $data[$idx] = (float) $r->total;
                }
            }
            return $data;
        };

        $kedatanganSBI = $buildMonthlyVolumeByStation($tahunKedatangan, 'kedatangan', 'stasiun_tujuan_sa', 'SBI');
        $kedatanganBBT = $buildMonthlyVolumeByStation($tahunKedatangan, 'kedatangan', 'stasiun_tujuan_sa', 'BBT');
        $kedatanganBJ = $buildMonthlyVolumeByStation($tahunKedatangan, 'kedatangan', 'stasiun_tujuan_sa', 'BJ');

        $muatSBI = $buildMonthlyVolumeByStation($tahunMuat, 'muat', 'stasiun_asal_sa', 'SBI');
        $muatBBT = $buildMonthlyVolumeByStation($tahunMuat, 'muat', 'stasiun_asal_sa', 'BBT');
        $muatBJ = $buildMonthlyVolumeByStation($tahunMuat, 'muat', 'stasiun_asal_sa', 'BJ');

        $mitraRows = Angkutan::query()
            ->select('nama_customer')
            ->selectRaw('SUM(volume_berat_kai) as total_volume')
            ->whereNotNull('tanggal_keberangkatan_asal_ka')
            ->whereYear('tanggal_keberangkatan_asal_ka', $mitraTahun)
            ->whereMonth('tanggal_keberangkatan_asal_ka', $mitraBulan)
            ->whereNotNull('nama_customer')
            ->groupBy('nama_customer')
            ->orderByDesc('total_volume')
            ->limit(20)
            ->get();

        $mitraLabels = $mitraRows->pluck('nama_customer')->values()->all();
        $mitraVolumes = $mitraRows->pluck('total_volume')->map(fn ($v) => (float) $v)->values()->all();

        $years = Angkutan::query()
            ->whereNotNull('tanggal_keberangkatan_asal_ka')
            ->selectRaw('DISTINCT YEAR(tanggal_keberangkatan_asal_ka) as tahun')
            ->orderBy('tahun')
            ->pluck('tahun')
            ->map(fn ($y) => (int) $y)
            ->values();

        if ($years->isEmpty()) {
            $years = collect([(int) $now->year]);
        }

        $kedatanganYearRows = Angkutan::query()
            ->whereNotNull('tanggal_keberangkatan_asal_ka')
            ->where('jenis_angkutan', 'kedatangan')
            ->selectRaw('YEAR(tanggal_keberangkatan_asal_ka) as tahun, SUM(volume_berat_kai) as total')
            ->groupBy('tahun')
            ->get()
            ->keyBy('tahun');

        $muatYearRows = Angkutan::query()
            ->whereNotNull('tanggal_keberangkatan_asal_ka')
            ->where('jenis_angkutan', 'muat')
            ->selectRaw('YEAR(tanggal_keberangkatan_asal_ka) as tahun, SUM(volume_berat_kai) as total')
            ->groupBy('tahun')
            ->get()
            ->keyBy('tahun');

        $volYearKedatangan = $years->map(function ($y) use ($kedatanganYearRows) {
            return (float) (($kedatanganYearRows[$y]->total ?? 0) ?: 0);
        })->values()->all();

        $volYearMuat = $years->map(function ($y) use ($muatYearRows) {
            return (float) (($muatYearRows[$y]->total ?? 0) ?: 0);
        })->values()->all();

        $start = Carbon::create($saTahun, $saBulan, 1)->startOfDay();
        $end = (clone $start)->endOfMonth()->endOfDay();
        $dateKeys = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dateKeys[] = $cursor->format('Y-m-d');
            $cursor->addDay();
        }

        $harianKedRows = Angkutan::query()
            ->whereNotNull('tanggal_keberangkatan_asal_ka')
            ->whereBetween('tanggal_keberangkatan_asal_ka', [$start->toDateString(), $end->toDateString()])
            ->where('jenis_angkutan', 'kedatangan')
            ->selectRaw('DATE(tanggal_keberangkatan_asal_ka) as tgl, COUNT(*) as total')
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get()
            ->keyBy('tgl');

        $harianMuatRows = Angkutan::query()
            ->whereNotNull('tanggal_keberangkatan_asal_ka')
            ->whereBetween('tanggal_keberangkatan_asal_ka', [$start->toDateString(), $end->toDateString()])
            ->where('jenis_angkutan', 'muat')
            ->selectRaw('DATE(tanggal_keberangkatan_asal_ka) as tgl, COUNT(*) as total')
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get()
            ->keyBy('tgl');

        $harianDates = $dateKeys;
        $harianKedatangan = [];
        $harianMuat = [];
        foreach ($dateKeys as $d) {
            $harianKedatangan[] = (int) (($harianKedRows[$d]->total ?? 0) ?: 0);
            $harianMuat[] = (int) (($harianMuatRows[$d]->total ?? 0) ?: 0);
        }

        $topCustomerQuery = Angkutan::query()
            ->whereNotNull('tanggal_keberangkatan_asal_ka')
            ->where('jenis_angkutan', $topCustomerJenis)
            ->whereYear('tanggal_keberangkatan_asal_ka', $topCustomerTahun)
            ->whereMonth('tanggal_keberangkatan_asal_ka', $topCustomerBulan)
            ->whereNotNull('nama_customer')
            ->select('nama_customer')
            ->groupBy('nama_customer');

        if ($topCustomerMode === 'sa') {
            $topCustomerQuery->selectRaw('COUNT(*) as total_metric');
        } else {
            $topCustomerQuery->selectRaw('SUM(volume_berat_kai) as total_metric');
        }

        $topCustomerRows = $topCustomerQuery
            ->orderByDesc('total_metric')
            ->limit(15)
            ->get();

        $topCustomerLabels = $topCustomerRows->pluck('nama_customer')->values()->all();
        $topCustomerValues = $topCustomerRows->pluck('total_metric')->map(function ($v) use ($topCustomerMode) {
            return $topCustomerMode === 'sa' ? (int) $v : (float) $v;
        })->values()->all();

        return view('dashboard.statistik', compact(
            'bulanLabels',
            'tahunKedatangan',
            'tahunMuat',
            'kedatanganSBI',
            'kedatanganBBT',
            'kedatanganBJ',
            'muatSBI',
            'muatBBT',
            'muatBJ',
            'mitraTahun',
            'mitraBulan',
            'mitraLabels',
            'mitraVolumes',
            'years',
            'volYearKedatangan',
            'volYearMuat',
            'saTahun',
            'saBulan',
            'harianDates',
            'harianKedatangan',
            'harianMuat',
            'topCustomerJenis',
            'topCustomerMode',
            'topCustomerTahun',
            'topCustomerBulan',
            'topCustomerLabels',
            'topCustomerValues'
        ));
    }

    public function uploadKedatangan(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:10240'
        ]);

        try {
            $file = $request->file('file');
            $data = $this->processExcelFile($file, 'kedatangan');
            
            return redirect()->route('input.data')
                ->with('success', "Berhasil upload {$data['count']} data kedatangan!")
                ->with('activeTab', 'kedatangan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    public function uploadMuat(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:10240'
        ]);

        try {
            $file = $request->file('file');
            $data = $this->processExcelFile($file, 'muat');
            
            return redirect()->route('input.data')
                ->with('success', "Berhasil upload {$data['count']} data muat!")
                ->with('activeTab', 'muat');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    public function previewUpload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,csv|max:10240'
            ]);

            $file = $request->file('file');
            
            if (!$file || !$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid file upload'
                ]);
            }

            $previewData = $this->readExcelPreview($file);
            
            return response()->json([
                'success' => true,
                'data' => $previewData
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(', ', $e->errors())
            ]);
        } catch (\Throwable $e) {
            \Log::error('File preview error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to process file: ' . $e->getMessage()
            ], 200);
        }
    }

    private function readExcelPreview($file)
    {
        try {
            $extension = $file->getClientOriginalExtension();
            
            if ($extension == 'csv') {
                $data = $this->readCsvFile($file);
            } else {
                $data = $this->readExcelFile($file);
            }
            
            if (empty($data)) {
                throw new \Exception('File is empty or has invalid format');
            }
            
            // Return only first 10 rows for preview
            return [
                'headers' => array_keys($data[0] ?? []),
                'rows' => array_slice($data, 0, 10)
            ];
        } catch (\Throwable $e) {
            throw new \Exception('Failed to read file: ' . $e->getMessage());
        }
    }

    private function readCsvFile($file)
    {
        $data = [];
        $handle = fopen($file->getPathname(), 'r');
        
        if ($handle === FALSE) {
            throw new \Exception('Cannot open CSV file');
        }
        
        try {
            $allRows = [];
            $rowIndex = 0;
            
            // Read all rows first
            while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $allRows[] = $row;
                $rowIndex++;
            }
            
            if (empty($allRows)) {
                throw new \Exception('CSV file is empty');
            }
            
            // Try to find header row within first 15 rows
            $headerRow = 0;
            for ($i = 0; $i < min(15, count($allRows)); $i++) {
                $rowData = $allRows[$i];
                $cleanRow = array_map('trim', $rowData);
                
                // Check if this row contains common headers
                $commonHeadersCount = 0;
                $expectedHeaders = [
                    'nama customer', 'stasiun asal sa', 'stasiun tujuan sa', 'nama ka stasiun asal',
                    'tanggal keberangkatan asal ka', 'nomor sarana', 'volume berat kai',
                    'banyaknya pengajuan', 'status sa', 'nomor sa', 'tanggal pembuatan sa',
                    'tanggal sa', 'jenis hari operasi', 'nomor manifest', 'komoditi'
                ];
                
                foreach ($cleanRow as $cellValue) {
                    if (in_array(strtolower($cellValue), $expectedHeaders)) {
                        $commonHeadersCount++;
                    }
                }
                
                // If a significant number of common headers are found, assume this is the header row
                if ($commonHeadersCount >= 4) {
                    $headerRow = $i;
                    break;
                }
            }
            
            // Get headers from identified row
            $headers = array_map('trim', $allRows[$headerRow]);
            
            // Process data rows (start from row after header)
            for ($i = $headerRow + 1; $i < count($allRows); $i++) {
                $row = $allRows[$i];
                if (count($headers) == count($row)) {
                    $trimmedRow = array_map('trim', $row);
                    $data[] = array_combine($headers, $trimmedRow);
                }
            }
            
        } finally {
            fclose($handle);
        }
        
        return $data;
    }

    private function readExcelFile($file)
    {
        try {
            if (!class_exists('\\ZipArchive')) {
                throw new \Exception('PHP extension ZipArchive (ext-zip) tidak aktif. Aktifkan ext-zip di PHP.');
            }

            $rowsByNumber = $this->readXlsxRows($file->getPathname());
            if (empty($rowsByNumber)) {
                throw new \Exception('Tidak ada data yang bisa dibaca dari file XLSX.');
            }

            $expectedHeaders = [
                'nama customer', 'stasiun asal sa', 'stasiun tujuan sa', 'nama ka stasiun asal',
                'tanggal keberangkatan asal ka', 'nomor sarana', 'volume berat kai',
                'banyaknya pengajuan', 'status sa', 'nomor sa', 'tanggal pembuatan sa',
                'tanggal sa', 'jenis hari operasi', 'nomor manifest', 'komoditi'
            ];

            // Cari baris header dari 1..30
            $headerRow = null;
            for ($r = 1; $r <= 30; $r++) {
                if (!isset($rowsByNumber[$r])) {
                    continue;
                }

                $values = array_values($rowsByNumber[$r]);
                $values = array_map(fn($v) => strtolower(trim((string) $v)), $values);

                $match = 0;
                foreach ($values as $v) {
                    if ($v !== '' && in_array($v, $expectedHeaders, true)) {
                        $match++;
                    }
                }

                if ($match >= 4) {
                    $headerRow = $r;
                    break;
                }
            }

            if ($headerRow === null) {
                // fallback: pakai baris pertama yang punya isi paling banyak
                $maxCount = 0;
                for ($r = 1; $r <= 30; $r++) {
                    $count = isset($rowsByNumber[$r]) ? count(array_filter($rowsByNumber[$r], fn($v) => $v !== null && trim((string) $v) !== '')) : 0;
                    if ($count > $maxCount) {
                        $maxCount = $count;
                        $headerRow = $r;
                    }
                }
            }

            if ($headerRow === null || !isset($rowsByNumber[$headerRow])) {
                throw new \Exception('Header tidak ditemukan di file XLSX.');
            }

            $headersByColumn = [];
            foreach ($rowsByNumber[$headerRow] as $col => $val) {
                $headersByColumn[$col] = trim((string) $val);
            }

            $nonEmptyHeaders = array_filter($headersByColumn, fn($v) => $v !== null && $v !== '');
            if (empty($nonEmptyHeaders)) {
                throw new \Exception('Header kosong / tidak valid.');
            }

            $data = [];
            foreach ($rowsByNumber as $rowNum => $cols) {
                if ($rowNum <= $headerRow) {
                    continue;
                }

                $hasData = false;
                $rowData = [];
                foreach ($headersByColumn as $col => $header) {
                    if ($header === null || $header === '') {
                        continue;
                    }

                    $cellValue = $cols[$col] ?? null;
                    if ($cellValue !== null && trim((string) $cellValue) !== '') {
                        $hasData = true;
                    }

                    $rowData[$header] = $cellValue;
                }

                if ($hasData) {
                    $data[] = $rowData;
                }
            }

            return $data;
        } catch (\Throwable $e) {
            throw new \Exception('Failed to read Excel file: ' . $e->getMessage());
        }
    }

    private function readXlsxRows(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \Exception('Tidak bisa membuka file XLSX (zip).');
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $ss = @simplexml_load_string($sharedXml);
            if ($ss !== false && isset($ss->si)) {
                foreach ($ss->si as $si) {
                    // Shared string bisa terdiri dari banyak <t> di <r>
                    $text = '';
                    if (isset($si->t)) {
                        $text = (string) $si->t;
                    } elseif (isset($si->r)) {
                        foreach ($si->r as $r) {
                            $text .= (string) $r->t;
                        }
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            // fallback: ambil worksheet pertama yang ada
            for ($i = 1; $i <= 10; $i++) {
                $try = $zip->getFromName("xl/worksheets/sheet{$i}.xml");
                if ($try !== false) {
                    $sheetXml = $try;
                    break;
                }
            }
        }

        if ($sheetXml === false) {
            $zip->close();
            throw new \Exception('Worksheet tidak ditemukan di file XLSX.');
        }

        $xml = @simplexml_load_string($sheetXml);
        if ($xml === false || !isset($xml->sheetData)) {
            $zip->close();
            throw new \Exception('Format XLSX tidak valid.');
        }

        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $rowNum = (int) $row['r'];
            if (!isset($rows[$rowNum])) {
                $rows[$rowNum] = [];
            }

            foreach ($row->c as $c) {
                $ref = (string) $c['r'];
                if (!preg_match('/^([A-Z]+)(\d+)$/', $ref, $m)) {
                    continue;
                }
                $col = $m[1];

                $type = (string) $c['t'];
                $value = null;

                if ($type === 's') {
                    $idx = (int) $c->v;
                    $value = $sharedStrings[$idx] ?? null;
                } elseif ($type === 'inlineStr') {
                    $value = isset($c->is->t) ? (string) $c->is->t : null;
                } else {
                    $value = isset($c->v) ? (string) $c->v : null;
                }

                $rows[$rowNum][$col] = $value;
            }
        }

        $zip->close();
        ksort($rows);

        // normalisasi kolom: pastikan urutan kolom konsisten
        $maxCol = 'A';
        foreach ($rows as $r => $cols) {
            foreach (array_keys($cols) as $col) {
                if ($this->colToIndex($col) > $this->colToIndex($maxCol)) {
                    $maxCol = $col;
                }
            }
        }

        $normalized = [];
        foreach ($rows as $r => $cols) {
            $normalized[$r] = [];
            $maxIndex = $this->colToIndex($maxCol);
            for ($i = 1; $i <= $maxIndex; $i++) {
                $col = $this->indexToCol($i);
                $normalized[$r][$col] = $cols[$col] ?? null;
            }
        }

        return $normalized;
    }

    private function colToIndex(string $col): int
    {
        $col = strtoupper($col);
        $len = strlen($col);
        $n = 0;
        for ($i = 0; $i < $len; $i++) {
            $n = $n * 26 + (ord($col[$i]) - 64);
        }
        return $n;
    }

    private function indexToCol(int $index): string
    {
        $col = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $col = chr(65 + $mod) . $col;
            $index = intdiv($index - 1, 26);
        }
        return $col;
    }

    private function processExcelFile($file, $jenisAngkutan)
    {
        $extension = $file->getClientOriginalExtension();
        
        if ($extension == 'csv') {
            $data = $this->readCsvFile($file);
        } else {
            $data = $this->readExcelFile($file);
        }
        
        $processedCount = 0;
        
        foreach ($data as $row) {
            try {
                // Map Excel columns to database fields (case-insensitive)
                $angkutanData = [
                    'jenis_angkutan' => $jenisAngkutan,
                    'nama_customer' => $this->findColumnValue($row, ['nama_customer', 'nama customer', 'customer', 'customer name']),
                    'stasiun_asal_sa' => $this->findColumnValue($row, ['stasiun_asal_sa', 'stasiun asal sa', 'stasiun asal', 'asal']),
                    'stasiun_tujuan_sa' => $this->findColumnValue($row, ['stasiun_tujuan_sa', 'stasiun tujuan sa', 'stasiun tujuan', 'tujuan']),
                    'nama_ka_stasiun_asal' => $this->findColumnValue($row, ['nama_ka_stasiun_asal', 'nama ka stasiun asal', 'nama ka', 'ka', 'nomor ka stasiun asal']),
                    'tanggal_keberangkatan_asal_ka' => $this->parseDate($this->findColumnValue($row, ['tanggal_keberangkatan_asal_ka', 'tanggal keberangkatan asal ka', 'tanggal', 'tanggal keberangkatan', 'date'])),
                    'nomor_sarana' => $this->findColumnValue($row, ['nomor_sarana', 'nomor sarana', 'sarana', 'no sarana']),
                    'volume_berat_kai' => $this->parseDecimal($this->findColumnValue($row, ['volume_berat_kai', 'volume berat kai', 'volume', 'berat', 'weight'])),
                    'banyaknya_pengajuan' => $this->parseInt($this->findColumnValue($row, ['banyaknya_pengajuan', 'banyaknya pengajuan', 'pengajuan', 'jumlah'])),
                    'status_sa' => $this->findColumnValue($row, ['status_sa', 'status sa', 'status']),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                // Validate required fields
                if (empty($angkutanData['nama_customer']) || empty($angkutanData['stasiun_asal_sa']) || empty($angkutanData['nama_ka_stasiun_asal'])) {
                    continue; // Skip rows with missing required data
                }
                
                // Create customer if not exists
                Customer::firstOrCreate([
                    'nama_customer' => $angkutanData['nama_customer']
                ]);
                
                // Create angkutan record
                Angkutan::create($angkutanData);
                $processedCount++;
                
            } catch (\Throwable $e) {
                // Skip invalid rows but continue processing
                \Log::error('Error processing row: ' . $e->getMessage());
                continue;
            }
        }
        
        return ['count' => $processedCount];
    }

    private function findColumnValue($row, $possibleKeys)
    {
        // Convert all keys to lowercase for case-insensitive matching
        $lowerRow = [];
        foreach ($row as $key => $value) {
            $lowerRow[strtolower(trim($key))] = $value;
        }
        
        foreach ($possibleKeys as $key) {
            $lowerKey = strtolower(trim($key));
            if (isset($lowerRow[$lowerKey]) && $lowerRow[$lowerKey] !== '') {
                return $lowerRow[$lowerKey];
            }
        }
        
        return null;
    }

    private function parseDate($date)
    {
        if (is_numeric($date)) {
            // Excel date format
            try {
                $serial = (float) $date;
                // Excel serial date: days since 1899-12-30
                $timestamp = (int) round(($serial - 25569) * 86400);
                if ($timestamp <= 0) {
                    return date('Y-m-d');
                }
                return gmdate('Y-m-d', $timestamp);
            } catch (\Exception $e) {
                return date('Y-m-d');
            }
        }
        
        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return date('Y-m-d');
        }
    }

    private function parseDecimal($value)
    {
        return is_numeric($value) ? (float) $value : 0.00;
    }

    private function parseInt($value)
    {
        return is_numeric($value) ? (int) $value : 0;
    }
}
