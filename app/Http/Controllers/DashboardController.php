<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Angkutan;
use App\Models\Customer;
use App\Models\Station;
use App\Models\TargetVolumeMuat;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = now();

        $availableYears = Angkutan::query()
            ->whereNotNull('tanggal_keberangkatan_asal_ka')
            ->where('jenis_angkutan', 'muat')
            ->selectRaw('DISTINCT YEAR(tanggal_keberangkatan_asal_ka) as tahun')
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->map(fn ($y) => (int) $y)
            ->values();

        if ($availableYears->isEmpty()) {
            $availableYears = collect([(int) $now->year]);
        }

        $tahun = (int) ($request->input('tahun') ?? $availableYears->first());

        // Base query untuk data MUAT saja (bukan kedatangan)
        // Catatan: jangan filter volume_berat_kai di base, karena akan mempengaruhi perhitungan lain (mis. total customer)
        $baseMuatQuery = Angkutan::query()
            ->where('jenis_angkutan', 'muat')
            ->whereNotNull('tanggal_keberangkatan_asal_ka')
            ->whereYear('tanggal_keberangkatan_asal_ka', $tahun);

        // Base query untuk semua data pada tahun tsb (MUAT + KEDATANGAN)
        $baseYearQuery = Angkutan::query()
            ->whereNotNull('tanggal_keberangkatan_asal_ka')
            ->whereYear('tanggal_keberangkatan_asal_ka', $tahun);

        // Total volume MUAT seluruh angkutan (akumulasi semua stasiun asal)
        $totalVolumeAll = (float) (clone $baseMuatQuery)
            ->whereNotNull('volume_berat_kai')
            ->sum('volume_berat_kai');

        $totalVolumeKedatanganAll = (float) (clone $baseYearQuery)
            ->where('jenis_angkutan', 'kedatangan')
            ->whereNotNull('volume_berat_kai')
            ->sum('volume_berat_kai');

        // Total customer pada tahun tsb (MUAT + KEDATANGAN)
        // Ini menyesuaikan angka dengan master aktivitas customer pada tahun terpilih.
        $totalCustomer = (int) (clone $baseYearQuery)
            ->whereNotNull('nama_customer')
            ->distinct()
            ->count('nama_customer');

        // Volume MUAT per stasiun asal (SBI/BBT/BJ)
        // Gunakan agregasi 1x query agar konsisten dan menghindari filter NOT LIKE yang bisa membuat hasil 0.
        $stationVolumes = (clone $baseMuatQuery)
            ->whereNotNull('volume_berat_kai')
            ->whereNotNull('stasiun_asal_sa')
            ->selectRaw("\n                SUM(CASE WHEN UPPER(TRIM(stasiun_asal_sa)) LIKE '%SBI%' THEN volume_berat_kai ELSE 0 END) as sbi,\n                SUM(CASE WHEN UPPER(TRIM(stasiun_asal_sa)) LIKE '%BBT%' THEN volume_berat_kai ELSE 0 END) as bbt,\n                SUM(CASE WHEN UPPER(TRIM(stasiun_asal_sa)) LIKE '%BJ%' THEN volume_berat_kai ELSE 0 END) as bj\n            ")
            ->first();

        $muatVolumeSBI = (float) ($stationVolumes->sbi ?? 0);
        $muatVolumeBBT = (float) ($stationVolumes->bbt ?? 0);
        $muatVolumeBJ = (float) ($stationVolumes->bj ?? 0);

        return view('dashboard.index', compact(
            'availableYears',
            'tahun',
            'totalCustomer',
            'totalVolumeAll',
            'totalVolumeKedatanganAll',
            'muatVolumeSBI',
            'muatVolumeBBT',
            'muatVolumeBJ'
        ));
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

        // If print parameter is present, return all data as JSON
        if ($request->has('print')) {
            $allData = $query
                ->orderBy('tanggal_keberangkatan_asal_ka', 'desc')
                ->get();
            
            return response()->json([
                'data' => $allData
            ]);
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

    public function previewTarget(Request $request)
    {
        $now = now();

        $yearsFromTarget = TargetVolumeMuat::query()
            ->select('tahun_program')
            ->distinct()
            ->orderByDesc('tahun_program')
            ->pluck('tahun_program')
            ->map(fn ($y) => (int) $y)
            ->values();

        $yearsFromMuat = Angkutan::query()
            ->whereNotNull('tanggal_keberangkatan_asal_ka')
            ->where('jenis_angkutan', 'muat')
            ->selectRaw('DISTINCT YEAR(tanggal_keberangkatan_asal_ka) as tahun')
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->map(fn ($y) => (int) $y)
            ->values();

        $availableYears = $yearsFromTarget
            ->merge($yearsFromMuat)
            ->unique()
            ->sortDesc()
            ->values();

        if ($availableYears->isEmpty()) {
            $availableYears = collect([(int) $now->year]);
        }

        $tahun = (int) ($request->input('tahun') ?? $availableYears->first());

        $chartYearNow = (int) ($request->input('chart_tahun_sekarang') ?? $tahun);
        $chartYearPrev = (int) ($request->input('chart_tahun_sebelumnya') ?? ($chartYearNow - 1));
        $chartYearTarget = (int) ($request->input('chart_tahun_target') ?? $tahun);

        if ($chartYearNow <= 0) {
            $chartYearNow = (int) $tahun;
        }
        if ($chartYearPrev <= 0) {
            $chartYearPrev = max(1, $chartYearNow - 1);
        }
        if ($chartYearTarget <= 0) {
            $chartYearTarget = (int) $tahun;
        }

        $bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $bulanFullLabels = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $targetRows = TargetVolumeMuat::query()
            ->where('tahun_program', $tahun)
            ->orderBy('bulan')
            ->get();

        $chartTargetRows = TargetVolumeMuat::query()
            ->where('tahun_program', $chartYearTarget)
            ->orderBy('bulan')
            ->get();

        $targetListQuery = TargetVolumeMuat::query()
            ->where('tahun_program', $tahun);

        $targetList = $targetListQuery
            ->orderBy('bulan')
            ->paginate(24)
            ->appends($request->query());

        $targetByMonth = [];
        foreach ($targetRows as $row) {
            $targetByMonth[(int) $row->bulan] = (float) $row->target_kg;
        }

        $chartTargetByMonth = [];
        foreach ($chartTargetRows as $row) {
            $chartTargetByMonth[(int) $row->bulan] = (float) $row->target_kg;
        }

        $muatMonthlyRows = Angkutan::query()
            ->selectRaw('MONTH(tanggal_keberangkatan_asal_ka) as bulan, SUM(volume_berat_kai) as total')
            ->where('jenis_angkutan', 'muat')
            ->whereNotNull('tanggal_keberangkatan_asal_ka')
            ->whereNotNull('volume_berat_kai')
            ->whereYear('tanggal_keberangkatan_asal_ka', $tahun)
            ->groupByRaw('MONTH(tanggal_keberangkatan_asal_ka)')
            ->pluck('total', 'bulan');

        $muatMonthlyRowsNow = Angkutan::query()
            ->selectRaw('MONTH(tanggal_keberangkatan_asal_ka) as bulan, SUM(volume_berat_kai) as total')
            ->where('jenis_angkutan', 'muat')
            ->whereNotNull('tanggal_keberangkatan_asal_ka')
            ->whereNotNull('volume_berat_kai')
            ->whereYear('tanggal_keberangkatan_asal_ka', $chartYearNow)
            ->groupByRaw('MONTH(tanggal_keberangkatan_asal_ka)')
            ->pluck('total', 'bulan');

        $muatMonthlyRowsPrev = Angkutan::query()
            ->selectRaw('MONTH(tanggal_keberangkatan_asal_ka) as bulan, SUM(volume_berat_kai) as total')
            ->where('jenis_angkutan', 'muat')
            ->whereNotNull('tanggal_keberangkatan_asal_ka')
            ->whereNotNull('volume_berat_kai')
            ->whereYear('tanggal_keberangkatan_asal_ka', $chartYearPrev)
            ->groupByRaw('MONTH(tanggal_keberangkatan_asal_ka)')
            ->pluck('total', 'bulan');

        $chartActualTons = [];
        $chartTargetTons = [];
        $chartNowTons = [];
        $chartPrevTons = [];
        $chartTargetCompareTons = [];
        $achievementCards = [];
        $yearActualKg = 0.0;
        $yearTargetKg = 0.0;
        for ($m = 1; $m <= 12; $m++) {
            $actualKg = (float) ((($muatMonthlyRows[$m] ?? 0) ?: 0));
            $targetKg = (float) ((($targetByMonth[$m] ?? 0) ?: 0));

            $yearActualKg += $actualKg;
            $yearTargetKg += $targetKg;

            $chartActualTons[] = (float) ($actualKg / 1000);
            $chartTargetTons[] = (float) ($targetKg / 1000);

            $nowKg = (float) ((($muatMonthlyRowsNow[$m] ?? 0) ?: 0));
            $prevKg = (float) ((($muatMonthlyRowsPrev[$m] ?? 0) ?: 0));
            $targetCompareKg = (float) ((($chartTargetByMonth[$m] ?? 0) ?: 0));
            $chartNowTons[] = (float) ($nowKg / 1000);
            $chartPrevTons[] = (float) ($prevKg / 1000);
            $chartTargetCompareTons[] = (float) ($targetCompareKg / 1000);

            $text = 'Belum ada data';
            $type = 'neutral';
            $percent = null;

            if ($targetKg > 0) {
                $diffPct = (($actualKg - $targetKg) / $targetKg) * 100;
                $percent = (float) abs($diffPct);
                if ($diffPct >= 0) {
                    $text = 'Lebih dari target ' . number_format($percent, 2) . '%';
                    $type = 'over';
                } else {
                    $text = 'Kurang dari target ' . number_format($percent, 2) . '%';
                    $type = 'under';
                }
            } elseif ($actualKg > 0) {
                $text = 'Target belum ada';
                $type = 'neutral';
            }

            $achievementCards[] = [
                'bulan' => $m,
                'label' => $bulanFullLabels[$m - 1] ?? (string) $m,
                'text' => $text,
                'type' => $type,
                'actual_ton' => (float) ($actualKg / 1000),
                'target_ton' => (float) ($targetKg / 1000),
            ];
        }

        $yearText = 'Belum ada data';
        $yearType = 'neutral';
        if ($yearTargetKg > 0) {
            $diffPct = (($yearActualKg - $yearTargetKg) / $yearTargetKg) * 100;
            $pct = (float) abs($diffPct);
            if ($diffPct >= 0) {
                $yearText = 'Lebih dari target ' . number_format($pct, 2) . '%';
                $yearType = 'over';
            } else {
                $yearText = 'Kurang dari target ' . number_format($pct, 2) . '%';
                $yearType = 'under';
            }
        } elseif ($yearActualKg > 0) {
            $yearText = 'Target belum ada';
            $yearType = 'neutral';
        }

        $initialMonth = (int) $now->month;
        if ($initialMonth < 1 || $initialMonth > 12) {
            $initialMonth = 1;
        }

        return view('dashboard.preview-target', [
            'availableYears' => $availableYears,
            'tahun' => $tahun,
            'targetRows' => $targetRows,
            'targetList' => $targetList,
            'bulanLabels' => $bulanLabels,
            'achievementCards' => $achievementCards,
            'yearAchievement' => [
                'label' => 'Tahun ' . $tahun,
                'text' => $yearText,
                'type' => $yearType,
                'actual_ton' => (float) ($yearActualKg / 1000),
                'target_ton' => (float) ($yearTargetKg / 1000),
            ],
            'initialMonth' => $initialMonth,
            'chartActualTons' => $chartActualTons,
            'chartTargetTons' => $chartTargetTons,
            'chartYearNow' => $chartYearNow,
            'chartYearPrev' => $chartYearPrev,
            'chartYearTarget' => $chartYearTarget,
            'chartNowTons' => $chartNowTons,
            'chartPrevTons' => $chartPrevTons,
            'chartTargetCompareTons' => $chartTargetCompareTons,
        ]);
    }

    public function previewTargetPreview(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,csv|max:10240',
            ]);

            $file = $request->file('file');
            if (!$file || !$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid file upload',
                ]);
            }

            $parsed = $this->readTargetFile($file);

            return response()->json([
                'success' => true,
                'tahun_program' => $parsed['tahun_program'],
                'tahun_programs' => $parsed['tahun_programs'] ?? [$parsed['tahun_program']],
                'rows' => array_slice($parsed['rows'], 0, 10),
                'total_rows' => count($parsed['rows']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(', ', $e->errors()),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Preview target error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to process file: ' . $e->getMessage(),
            ], 200);
        }
    }

    public function previewTargetStore(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,csv|max:10240',
            ]);

            $file = $request->file('file');
            if (!$file || !$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid file upload',
                ]);
            }

            $parsed = $this->readTargetFile($file);
            $rows = $parsed['rows'];

            $tahunPrograms = $parsed['tahun_programs'] ?? [$parsed['tahun_program']];
            $tahunPrograms = array_values(array_unique(array_map(fn ($y) => (int) $y, $tahunPrograms)));
            sort($tahunPrograms);

            $existing = TargetVolumeMuat::query()
                ->whereIn('tahun_program', $tahunPrograms)
                ->get(['tahun_program', 'bulan']);

            $existingSet = [];
            foreach ($existing as $e) {
                $existingSet[((int) $e->tahun_program) . '-' . ((int) $e->bulan)] = true;
            }

            $inserted = 0;
            $skipped = 0;

            DB::transaction(function () use ($rows, &$inserted, &$skipped, &$existingSet) {
                foreach ($rows as $row) {
                    $y = (int) $row['tahun_program'];
                    $m = (int) $row['bulan'];
                    $key = $y . '-' . $m;
                    if (isset($existingSet[$key])) {
                        $skipped++;
                        continue;
                    }

                    TargetVolumeMuat::query()->create([
                        'tahun_program' => $y,
                        'bulan' => $m,
                        'target_kg' => (int) $row['target_kg'],
                    ]);
                    $existingSet[$key] = true;
                    $inserted++;
                }
            });

            $redirectYear = null;
            if (!empty($tahunPrograms)) {
                $redirectYear = $tahunPrograms[0];
            }

            return response()->json([
                'success' => true,
                'message' => 'Upload selesai. Baris baru: ' . $inserted . ', duplikat dilewati: ' . $skipped . '.',
                'inserted' => $inserted,
                'skipped' => $skipped,
                'tahun_program' => $redirectYear,
                'tahun_programs' => $tahunPrograms,
                'total_rows' => count($rows),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(', ', $e->errors()),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Store target error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to save data: ' . $e->getMessage(),
            ], 200);
        }
    }

    private function readTargetFile($file): array
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());

        if ($extension === 'csv') {
            return $this->readTargetCsvFile($file);
        }

        return $this->readTargetExcelFile($file);
    }

    private function readTargetCsvFile($file): array
    {
        $handle = fopen($file->getPathname(), 'r');
        if ($handle === false) {
            throw new \Exception('Cannot open CSV file');
        }

        try {
            $rows = [];
            while (($row = fgetcsv($handle, 2000, ',')) !== false) {
                $rows[] = $row;
            }

            if (empty($rows)) {
                throw new \Exception('CSV file is empty');
            }

            $tahunProgram = null;
            $headerRowIndex = null;
            $bulanCol = null;
            $kgCol = null;

            for ($i = 0; $i < min(25, count($rows)); $i++) {
                foreach ($rows[$i] as $c => $cell) {
                    $cellStr = trim((string) $cell);
                    if ($tahunProgram === null && preg_match('/PROGRAM\s*TAHUN\s*(\d{4})/i', $cellStr, $m)) {
                        $tahunProgram = (int) $m[1];
                    }
                    if ($bulanCol === null && strtoupper($cellStr) === 'BULAN') {
                        $headerRowIndex = $i;
                        $bulanCol = $c;
                    }
                }
            }

            if ($headerRowIndex === null || $bulanCol === null) {
                throw new \Exception('Header BULAN tidak ditemukan di CSV');
            }

            for ($c = 0; $c < count($rows[$headerRowIndex]); $c++) {
                $cellStr = strtoupper(trim((string) ($rows[$headerRowIndex][$c] ?? '')));
                if ($cellStr === 'KG') {
                    $kgCol = $c;
                    break;
                }
            }

            if ($kgCol === null) {
                for ($c = 0; $c < count($rows[$headerRowIndex]); $c++) {
                    $cellStr = strtoupper(trim((string) ($rows[$headerRowIndex][$c] ?? '')));
                    if (str_contains($cellStr, 'KG')) {
                        $kgCol = $c;
                        break;
                    }
                }
            }

            if ($kgCol === null) {
                throw new \Exception('Kolom KG tidak ditemukan di CSV');
            }

            if ($tahunProgram === null) {
                throw new \Exception('Tahun program tidak ditemukan di CSV (PROGRAM TAHUN XXXX)');
            }

            $parsedRows = [];
            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                $bulanRaw = trim((string) ($rows[$i][$bulanCol] ?? ''));
                if ($bulanRaw === '') {
                    continue;
                }

                if (strtoupper($bulanRaw) === 'TOTAL') {
                    break;
                }

                $bulanNum = $this->parseBulanToNumber($bulanRaw);
                if ($bulanNum === null) {
                    continue;
                }

                $kgRaw = $rows[$i][$kgCol] ?? null;
                $kg = $this->parseNumberToInt($kgRaw);

                $parsedRows[] = [
                    'tahun_program' => $tahunProgram,
                    'bulan' => $bulanNum,
                    'target_kg' => $kg,
                ];
            }

            if (empty($parsedRows)) {
                throw new \Exception('Tidak ada data target yang terbaca dari CSV');
            }

            return [
                'tahun_program' => $tahunProgram,
                'tahun_programs' => [$tahunProgram],
                'rows' => $parsedRows,
            ];
        } finally {
            fclose($handle);
        }
    }

    private function readTargetExcelFile($file): array
    {
        if (!class_exists('\\ZipArchive')) {
            throw new \Exception('PHP extension ZipArchive (ext-zip) tidak aktif. Aktifkan ext-zip di PHP.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($file->getPathname()) !== true) {
            throw new \Exception('Tidak bisa membuka file XLSX (zip).');
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $ss = @simplexml_load_string($sharedXml);
            if ($ss !== false && isset($ss->si)) {
                foreach ($ss->si as $si) {
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

        $allRows = [];
        $tahunPrograms = [];
        for ($i = 1; $i <= 30; $i++) {
            $sheetXml = $zip->getFromName("xl/worksheets/sheet{$i}.xml");
            if ($sheetXml === false) {
                continue;
            }

            $rowsByNumber = $this->readXlsxRowsFromXml($sheetXml, $sharedStrings);
            if (empty($rowsByNumber)) {
                continue;
            }

            $parsedSheet = $this->parseTargetFromSheetRows($rowsByNumber);
            if ($parsedSheet === null) {
                continue;
            }

            $tahunPrograms[] = (int) $parsedSheet['tahun_program'];
            foreach (($parsedSheet['rows'] ?? []) as $r) {
                $allRows[] = $r;
            }
        }

        $zip->close();

        if (empty($allRows)) {
            throw new \Exception('Tidak ada data target yang terbaca dari XLSX');
        }

        $tahunPrograms = array_values(array_unique(array_map(fn ($y) => (int) $y, $tahunPrograms)));
        sort($tahunPrograms);

        usort($allRows, function ($a, $b) {
            $ya = (int) ($a['tahun_program'] ?? 0);
            $yb = (int) ($b['tahun_program'] ?? 0);
            if ($ya !== $yb) {
                return $ya <=> $yb;
            }
            return ((int) ($a['bulan'] ?? 0)) <=> ((int) ($b['bulan'] ?? 0));
        });

        $primaryYear = $tahunPrograms[0] ?? null;
        if ($primaryYear === null) {
            $primaryYear = (int) ($allRows[0]['tahun_program'] ?? 0);
        }

        return [
            'tahun_program' => $primaryYear,
            'tahun_programs' => $tahunPrograms,
            'rows' => $allRows,
        ];
    }

    private function readXlsxRowsFromXml(string $sheetXml, array $sharedStrings): array
    {
        $xml = @simplexml_load_string($sheetXml);
        if ($xml === false || !isset($xml->sheetData)) {
            return [];
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

        ksort($rows);
        if (empty($rows)) {
            return [];
        }

        $maxCol = 'A';
        foreach ($rows as $cols) {
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

    private function parseTargetFromSheetRows(array $rowsByNumber): ?array
    {
        $tahunProgram = null;
        $programHeaderRow = null;
        $programHeaderCol = null;
        $bulanCol = null;
        $kgCol = null;
        $dataStartRow = null;

        for ($r = 1; $r <= 40; $r++) {
            if (!isset($rowsByNumber[$r])) {
                continue;
            }

            foreach ($rowsByNumber[$r] as $col => $val) {
                $cellStr = trim((string) $val);
                if ($bulanCol === null && strtoupper($cellStr) === 'BULAN') {
                    $bulanCol = $col;
                }

                if ($tahunProgram === null && preg_match('/PROGRAM\s*TAHUN\s*(\d{4})/i', $cellStr, $m)) {
                    $tahunProgram = (int) $m[1];
                    $programHeaderRow = $r;
                    $programHeaderCol = $col;
                }
            }
        }

        if ($tahunProgram === null) {
            for ($r = 1; $r <= 60; $r++) {
                if (!isset($rowsByNumber[$r])) {
                    continue;
                }
                foreach ($rowsByNumber[$r] as $col => $val) {
                    $cellStr = trim((string) $val);
                    if (preg_match('/PROGRAM\s*TAHUN\s*(\d{4})/i', $cellStr, $m)) {
                        $tahunProgram = (int) $m[1];
                        $programHeaderRow = $r;
                        $programHeaderCol = $col;
                        break 2;
                    }
                }
            }
        }

        if ($bulanCol === null || $tahunProgram === null) {
            return null;
        }

        $maxCol = null;
        $firstRowKey = array_key_first($rowsByNumber);
        if ($firstRowKey !== null && isset($rowsByNumber[$firstRowKey])) {
            $maxCol = array_key_last($rowsByNumber[$firstRowKey]);
        }
        if ($maxCol === null) {
            $maxCol = 'Z';
        }

        if ($programHeaderRow !== null && $programHeaderCol !== null && isset($rowsByNumber[$programHeaderRow])) {
            $programStartIdx = $this->colToIndex($programHeaderCol);
            $maxIdx = $this->colToIndex((string) $maxCol);

            $groupStarts = [];
            foreach ($rowsByNumber[$programHeaderRow] as $col => $val) {
                $cell = strtoupper(trim((string) $val));
                if ($cell === '') {
                    continue;
                }
                if (str_contains($cell, 'PROGRAM') || $cell === 'MUAT' || $cell === 'BONGKAR' || $cell === 'PENDAPATAN') {
                    $groupStarts[$this->colToIndex((string) $col)] = (string) $col;
                }
            }
            ksort($groupStarts);
            $groupStartIdxs = array_keys($groupStarts);

            $programEndIdx = $maxIdx;
            $pos = array_search($programStartIdx, $groupStartIdxs, true);
            if ($pos !== false && isset($groupStartIdxs[$pos + 1])) {
                $programEndIdx = $groupStartIdxs[$pos + 1] - 1;
            }

            $startSearchRow = $programHeaderRow;
            $endSearchRow = min($programHeaderRow + 15, 120);
            for ($r = $startSearchRow; $r <= $endSearchRow; $r++) {
                if (!isset($rowsByNumber[$r])) {
                    continue;
                }

                foreach ($rowsByNumber[$r] as $col => $val) {
                    $cellStr = strtoupper(trim((string) $val));
                    if ($cellStr !== 'KG') {
                        continue;
                    }

                    $idx = $this->colToIndex((string) $col);
                    if ($idx >= $programStartIdx && $idx <= $programEndIdx) {
                        $kgCol = (string) $col;
                        $dataStartRow = $r + 1;
                        break 2;
                    }
                }
            }
        }

        if ($kgCol === null) {
            for ($r = 1; $r <= 60; $r++) {
                if (!isset($rowsByNumber[$r])) {
                    continue;
                }

                foreach ($rowsByNumber[$r] as $col => $val) {
                    $cellStr = strtoupper(trim((string) $val));
                    if ($cellStr === 'KG') {
                        $kgCol = $col;
                        $dataStartRow = $r + 1;
                        break 2;
                    }
                }
            }
        }

        if ($kgCol === null || $dataStartRow === null) {
            return null;
        }

        $parsedRows = [];
        for ($r = $dataStartRow; $r <= 500; $r++) {
            if (!isset($rowsByNumber[$r])) {
                continue;
            }

            $bulanRaw = trim((string) ($rowsByNumber[$r][$bulanCol] ?? ''));
            if ($bulanRaw === '') {
                continue;
            }

            if (strtoupper($bulanRaw) === 'TOTAL') {
                break;
            }

            $bulanNum = $this->parseBulanToNumber($bulanRaw);
            if ($bulanNum === null) {
                continue;
            }

            $kgRaw = $rowsByNumber[$r][$kgCol] ?? null;
            $kg = $this->parseNumberToInt($kgRaw);

            $parsedRows[] = [
                'tahun_program' => $tahunProgram,
                'bulan' => $bulanNum,
                'target_kg' => $kg,
            ];
        }

        if (empty($parsedRows)) {
            return null;
        }

        usort($parsedRows, fn ($a, $b) => ((int) $a['bulan']) <=> ((int) $b['bulan']));

        return [
            'tahun_program' => $tahunProgram,
            'rows' => $parsedRows,
        ];
    }

    private function parseBulanToNumber(string $bulanRaw): ?int
    {
        $bulanRaw = trim($bulanRaw);
        if ($bulanRaw === '') {
            return null;
        }

        if (is_numeric($bulanRaw)) {
            $n = (int) $bulanRaw;
            return ($n >= 1 && $n <= 12) ? $n : null;
        }

        $key = strtoupper($bulanRaw);
        $map = [
            'JANUARI' => 1,
            'JAN' => 1,
            'FEBRUARI' => 2,
            'FEB' => 2,
            'MARET' => 3,
            'MAR' => 3,
            'APRIL' => 4,
            'APR' => 4,
            'MEI' => 5,
            'MAY' => 5,
            'JUNI' => 6,
            'JUN' => 6,
            'JULI' => 7,
            'JUL' => 7,
            'AGUSTUS' => 8,
            'AGU' => 8,
            'AUG' => 8,
            'SEPTEMBER' => 9,
            'SEP' => 9,
            'OKTOBER' => 10,
            'OKT' => 10,
            'OCT' => 10,
            'NOVEMBER' => 11,
            'NOV' => 11,
            'DESEMBER' => 12,
            'DES' => 12,
            'DEC' => 12,
        ];

        return $map[$key] ?? null;
    }

    private function parseNumberToInt($value): int
    {
        if ($value === null) {
            return 0;
        }

        if (is_numeric($value)) {
            return (int) round((float) $value);
        }

        $str = (string) $value;
        $str = trim($str);
        if ($str === '') {
            return 0;
        }

        $digits = preg_replace('/[^0-9]/', '', $str);
        if ($digits === null || $digits === '') {
            return 0;
        }

        return (int) $digits;
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
        $saJenis = (string) ($request->input('sa_jenis') ?? 'keduanya');

        $topCustomerJenis = (string) ($request->input('top_customer_jenis') ?? 'kedatangan');
        $topCustomerMode = (string) ($request->input('top_customer_mode') ?? 'volume');
        $topCustomerTahun = (int) ($request->input('top_customer_tahun') ?? $now->year);
        $topCustomerBulan = (int) ($request->input('top_customer_bulan') ?? $now->month);

        // New: Get year range for Line Chart filter
        $yearCompareStart = $request->input('year_compare_start') ? (int) $request->input('year_compare_start') : null;
        $yearCompareEnd = $request->input('year_compare_end') ? (int) $request->input('year_compare_end') : null;

        if (!in_array($topCustomerJenis, ['kedatangan', 'muat', 'keduanya'], true)) {
            $topCustomerJenis = 'kedatangan';
        }
        if (!in_array($topCustomerMode, ['volume', 'sa'], true)) {
            $topCustomerMode = 'volume';
        }

        $bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $buildMonthlyVolumeByStation = function (int $tahun, string $jenisAngkutan, string $field, string $stationCode) {
            $query = Angkutan::query()
                ->selectRaw('MONTH(tanggal_keberangkatan_asal_ka) as bulan, SUM(volume_berat_kai) as total')
                ->where('jenis_angkutan', $jenisAngkutan)
                ->whereNotNull('tanggal_keberangkatan_asal_ka')
                ->whereNotNull('volume_berat_kai')
                ->whereYear('tanggal_keberangkatan_asal_ka', $tahun)
                ->whereNotNull($field);
            
            // Filter stasiun dengan lebih tepat
            if ($stationCode === 'SBI') {
                $query->whereRaw('UPPER(TRIM(' . $field . ')) LIKE ?', ['%SBI%'])
                      ->whereRaw('UPPER(TRIM(' . $field . ')) NOT LIKE ?', ['%BBT%'])
                      ->whereRaw('UPPER(TRIM(' . $field . ')) NOT LIKE ?', ['%BJ%']);
            } elseif ($stationCode === 'BBT') {
                $query->whereRaw('UPPER(TRIM(' . $field . ')) LIKE ?', ['%BBT%'])
                      ->whereRaw('UPPER(TRIM(' . $field . ')) NOT LIKE ?', ['%SBI%'])
                      ->whereRaw('UPPER(TRIM(' . $field . ')) NOT LIKE ?', ['%BJ%']);
            } elseif ($stationCode === 'BJ') {
                $query->whereRaw('UPPER(TRIM(' . $field . ')) LIKE ?', ['%BJ%'])
                      ->whereRaw('UPPER(TRIM(' . $field . ')) NOT LIKE ?', ['%SBI%'])
                      ->whereRaw('UPPER(TRIM(' . $field . ')) NOT LIKE ?', ['%BBT%']);
            }
            
            $rows = $query->groupBy('bulan')->get();

            $data = array_fill(0, 12, 0.0);
            foreach ($rows as $r) {
                $idx = ((int) $r->bulan) - 1;
                if ($idx >= 0 && $idx < 12) {
                    $data[$idx] = (float) $r->total;
                }
            }
            return array_map(fn ($v) => (float) $v / 1000, $data);
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
            ->whereNotNull('volume_berat_kai')
            ->whereYear('tanggal_keberangkatan_asal_ka', $mitraTahun)
            ->whereMonth('tanggal_keberangkatan_asal_ka', $mitraBulan)
            ->whereNotNull('nama_customer')
            ->groupBy('nama_customer')
            ->orderByDesc('total_volume')
            ->limit(20)
            ->get();

        $mitraLabels = $mitraRows->pluck('nama_customer')->values()->all();
        $mitraVolumes = $mitraRows->pluck('total_volume')->map(fn ($v) => (float) $v / 1000)->values()->all();

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

        // For year comparison, use single year selected by user
        $yearCompareDisplay = (int) ($request->input('year_compare_display') ?? $now->year);
        
        // Get monthly data for the selected year
        $bulanLabelsChart = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        $kedatanganMonthlyRows = Angkutan::query()
            ->whereNotNull('tanggal_keberangkatan_asal_ka')
            ->whereNotNull('volume_berat_kai')
            ->where('jenis_angkutan', 'kedatangan')
            ->whereYear('tanggal_keberangkatan_asal_ka', $yearCompareDisplay)
            ->selectRaw('MONTH(tanggal_keberangkatan_asal_ka) as bulan, SUM(volume_berat_kai) as total')
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        $muatMonthlyRows = Angkutan::query()
            ->whereNotNull('tanggal_keberangkatan_asal_ka')
            ->whereNotNull('volume_berat_kai')
            ->where('jenis_angkutan', 'muat')
            ->whereYear('tanggal_keberangkatan_asal_ka', $yearCompareDisplay)
            ->selectRaw('MONTH(tanggal_keberangkatan_asal_ka) as bulan, SUM(volume_berat_kai) as total')
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        // Build monthly data arrays
        $volMonthKedatangan = [];
        $volMonthMuat = [];
        for ($m = 1; $m <= 12; $m++) {
            $volMonthKedatangan[] = (float) (((($kedatanganMonthlyRows[$m]->total ?? 0) ?: 0)) / 1000);
            $volMonthMuat[] = (float) (((($muatMonthlyRows[$m]->total ?? 0) ?: 0)) / 1000);
        }

        // Keep old year-based data for potential future use
        $yearsForChart = $years;

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

        // Handle station distribution query
        if ($topCustomerJenis === 'keduanya') {
            // Combine both asal and tujuan stations
            $asalData = Angkutan::query()
                ->whereNotNull('tanggal_keberangkatan_asal_ka')
                ->whereYear('tanggal_keberangkatan_asal_ka', $topCustomerTahun)
                ->whereMonth('tanggal_keberangkatan_asal_ka', $topCustomerBulan)
                ->whereNotNull('stasiun_asal_sa')
                ->select('stasiun_asal_sa as stasiun')
                ->selectRaw('COUNT(*) as total_sa, SUM(volume_berat_kai) as total_volume')
                ->groupBy('stasiun_asal_sa')
                ->get();
            
            $tujuanData = Angkutan::query()
                ->whereNotNull('tanggal_keberangkatan_asal_ka')
                ->whereYear('tanggal_keberangkatan_asal_ka', $topCustomerTahun)
                ->whereMonth('tanggal_keberangkatan_asal_ka', $topCustomerBulan)
                ->whereNotNull('stasiun_tujuan_sa')
                ->select('stasiun_tujuan_sa as stasiun')
                ->selectRaw('COUNT(*) as total_sa, SUM(volume_berat_kai) as total_volume')
                ->groupBy('stasiun_tujuan_sa')
                ->get();
            
            // Merge and aggregate by stasiun
            $merged = [];
            foreach ($asalData as $row) {
                $stasiun = $row->stasiun;
                if (!isset($merged[$stasiun])) {
                    $merged[$stasiun] = ['total_sa' => 0, 'total_volume' => 0];
                }
                $merged[$stasiun]['total_sa'] += $row->total_sa;
                $merged[$stasiun]['total_volume'] += $row->total_volume;
            }
            
            foreach ($tujuanData as $row) {
                $stasiun = $row->stasiun;
                if (!isset($merged[$stasiun])) {
                    $merged[$stasiun] = ['total_sa' => 0, 'total_volume' => 0];
                }
                $merged[$stasiun]['total_sa'] += $row->total_sa;
                $merged[$stasiun]['total_volume'] += $row->total_volume;
            }
            
            // Sort and limit to 15
            uasort($merged, fn($a, $b) => $b['total_sa'] <=> $a['total_sa']);
            $merged = array_slice($merged, 0, 15, true);
            
            $topCustomerLabels = array_keys($merged);
            $topCustomerValues = array_map(fn($v) => (int) $v['total_sa'], $merged);
        } else {
            // Determine field based on jenis
            $groupByField = $topCustomerJenis === 'kedatangan' ? 'stasiun_asal_sa' : 'stasiun_tujuan_sa';
            
            $topCustomerQuery = Angkutan::query()
                ->whereNotNull('tanggal_keberangkatan_asal_ka')
                ->where('jenis_angkutan', $topCustomerJenis)
                ->whereYear('tanggal_keberangkatan_asal_ka', $topCustomerTahun)
                ->whereMonth('tanggal_keberangkatan_asal_ka', $topCustomerBulan)
                ->whereNotNull($groupByField)
                ->select($groupByField)
                ->selectRaw('COUNT(*) as total_sa, SUM(volume_berat_kai) as total_volume')
                ->groupBy($groupByField)
                ->orderByDesc('total_sa')
                ->limit(15)
                ->get();

            $topCustomerLabels = $topCustomerQuery->pluck($groupByField)->values()->all();
            $topCustomerValues = $topCustomerQuery->pluck('total_sa')->map(fn ($v) => (int) $v)->values()->all();
        }

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
            'yearsForChart',
            'bulanLabelsChart',
            'volMonthKedatangan',
            'volMonthMuat',
            'yearCompareDisplay',
            'saTahun',
            'saBulan',
            'saJenis',
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
                ->with('success', "Upload bongkar selesai. Data baru: {$data['inserted']}, duplikat dilewati: {$data['skipped']}.")
                ->with('activeTab', 'bongkar');
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
                ->with('success', "Upload muat selesai. Data baru: {$data['inserted']}, duplikat dilewati: {$data['skipped']}.")
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

            $tahunPrograms = [];
            foreach ($data as $row) {
                $rawDate = $this->findColumnValue($row, [
                    'tanggal_keberangkatan_asal_ka',
                    'tanggal keberangkatan asal ka',
                    'tanggal',
                    'tanggal keberangkatan',
                    'date'
                ]);
                if ($rawDate === null || $rawDate === '') {
                    continue;
                }

                $ymd = $this->parseDate($rawDate);
                if (is_string($ymd) && strlen($ymd) >= 4) {
                    $tahunPrograms[] = (int) substr($ymd, 0, 4);
                }
            }
            $tahunPrograms = collect($tahunPrograms)->filter()->unique()->sort()->values()->all();
            
            // Return only first 10 rows for preview
            return [
                'headers' => array_keys($data[0] ?? []),
                'rows' => array_slice($data, 0, 10),
                'total_rows' => count($data),
                'tahun_programs' => $tahunPrograms,
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

            $zip = new \ZipArchive();
            if ($zip->open($file->getPathname()) !== true) {
                throw new \Exception('Tidak bisa membuka file XLSX (zip).');
            }

            $sharedStrings = [];
            $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
            if ($sharedXml !== false) {
                $ss = @simplexml_load_string($sharedXml);
                if ($ss !== false && isset($ss->si)) {
                    foreach ($ss->si as $si) {
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

            $data = [];
            for ($i = 1; $i <= 30; $i++) {
                $sheetXml = $zip->getFromName("xl/worksheets/sheet{$i}.xml");
                if ($sheetXml === false) {
                    continue;
                }

                $rowsByNumber = $this->readXlsxRowsFromXml($sheetXml, $sharedStrings);
                if (empty($rowsByNumber)) {
                    continue;
                }

                $sheetData = $this->parseAngkutanFromSheetRows($rowsByNumber);
                foreach ($sheetData as $r) {
                    $data[] = $r;
                }
            }

            $zip->close();

            if (empty($data)) {
                throw new \Exception('Tidak ada data yang bisa dibaca dari file XLSX.');
            }

            return $data;
        } catch (\Throwable $e) {
            throw new \Exception('Failed to read Excel file: ' . $e->getMessage());
        }
    }

    private function parseAngkutanFromSheetRows(array $rowsByNumber): array
    {
        if (empty($rowsByNumber)) {
            return [];
        }

        $expectedHeaders = [
            'nama customer', 'stasiun asal sa', 'stasiun tujuan sa', 'nama ka stasiun asal',
            'tanggal keberangkatan asal ka', 'nomor sarana', 'volume berat kai',
            'banyaknya pengajuan', 'status sa', 'nomor sa', 'tanggal pembuatan sa',
            'tanggal sa', 'jenis hari operasi', 'nomor manifest', 'komoditi'
        ];

        $headerRow = null;
        for ($r = 1; $r <= 60; $r++) {
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
            $maxCount = 0;
            for ($r = 1; $r <= 60; $r++) {
                $count = isset($rowsByNumber[$r]) ? count(array_filter($rowsByNumber[$r], fn($v) => $v !== null && trim((string) $v) !== '')) : 0;
                if ($count > $maxCount) {
                    $maxCount = $count;
                    $headerRow = $r;
                }
            }
        }

        if ($headerRow === null || !isset($rowsByNumber[$headerRow])) {
            return [];
        }

        $headersByColumn = [];
        foreach ($rowsByNumber[$headerRow] as $col => $val) {
            $headersByColumn[$col] = trim((string) $val);
        }

        $nonEmptyHeaders = array_filter($headersByColumn, fn($v) => $v !== null && $v !== '');
        if (empty($nonEmptyHeaders)) {
            return [];
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
        @set_time_limit(0);

        $extension = $file->getClientOriginalExtension();
        
        if ($extension == 'csv') {
            $data = $this->readCsvFile($file);
        } else {
            $data = $this->readExcelFile($file);
        }
        
        $inserted = 0;
        $skipped = 0;
        $seenKeys = [];

        $candidateRows = [];
        
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

                $dedupHash = $this->buildAngkutanDedupHash($jenisAngkutan, $angkutanData);
                if (isset($seenKeys[$dedupHash])) {
                    $skipped++;
                    continue;
                }

                $seenKeys[$dedupHash] = true;
                $angkutanData['_dedup_hash'] = $dedupHash;
                $candidateRows[] = $angkutanData;
                
            } catch (\Throwable $e) {
                // Skip invalid rows but continue processing
                \Log::error('Error processing row: ' . $e->getMessage());
                continue;
            }
        }

        if (empty($candidateRows)) {
            return ['inserted' => $inserted, 'skipped' => $skipped];
        }

        $hashExpr = $this->getAngkutanDedupHashSqlExpression();
        $chunkSize = 400;
        foreach (array_chunk($candidateRows, $chunkSize) as $chunk) {
            $hashes = array_values(array_unique(array_map(fn ($r) => $r['_dedup_hash'], $chunk)));

            $existingHashes = Angkutan::query()
                ->where('jenis_angkutan', $jenisAngkutan)
                ->whereIn(DB::raw($hashExpr), $hashes)
                ->selectRaw($hashExpr . ' as dedup_hash')
                ->pluck('dedup_hash')
                ->all();

            $existingSet = [];
            foreach ($existingHashes as $h) {
                $existingSet[$h] = true;
            }

            $rowsToInsert = [];
            $customerNames = [];
            foreach ($chunk as $row) {
                $h = $row['_dedup_hash'];
                if (isset($existingSet[$h])) {
                    $skipped++;
                    continue;
                }

                unset($row['_dedup_hash']);
                $rowsToInsert[] = $row;
                if (!empty($row['nama_customer'])) {
                    $customerNames[] = $row['nama_customer'];
                }
            }

            if (!empty($customerNames)) {
                $customerNames = array_values(array_unique($customerNames));
                $existingCustomers = Customer::query()
                    ->whereIn('nama_customer', $customerNames)
                    ->pluck('nama_customer')
                    ->all();

                $existingCustomerSet = [];
                foreach ($existingCustomers as $name) {
                    $existingCustomerSet[$name] = true;
                }

                $newCustomers = [];
                foreach ($customerNames as $name) {
                    if (isset($existingCustomerSet[$name])) {
                        continue;
                    }
                    $newCustomers[] = [
                        'nama_customer' => $name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($newCustomers)) {
                    Customer::query()->insert($newCustomers);
                }
            }

            if (!empty($rowsToInsert)) {
                foreach (array_chunk($rowsToInsert, 500) as $insertChunk) {
                    DB::table('angkutan')->insert($insertChunk);
                    $inserted += count($insertChunk);
                }
            }
        }
        
        return ['inserted' => $inserted, 'skipped' => $skipped];
    }

    private function buildAngkutanDedupHash(string $jenisAngkutan, array $angkutanData): string
    {
        $parts = [
            strtolower(trim((string) $jenisAngkutan)),
            (string) ($angkutanData['tanggal_keberangkatan_asal_ka'] ?? ''),
            strtolower(trim((string) ($angkutanData['nama_customer'] ?? ''))),
            strtolower(trim((string) ($angkutanData['stasiun_asal_sa'] ?? ''))),
            strtolower(trim((string) ($angkutanData['nama_ka_stasiun_asal'] ?? ''))),
            strtolower(trim((string) ($angkutanData['nomor_sarana'] ?? ''))),
            strtolower(trim((string) ($angkutanData['stasiun_tujuan_sa'] ?? ''))),
        ];

        return md5(implode('|', $parts));
    }

    private function getAngkutanDedupHashSqlExpression(): string
    {
        return "MD5(CONCAT_WS('|', jenis_angkutan, DATE(tanggal_keberangkatan_asal_ka), LOWER(TRIM(nama_customer)), LOWER(TRIM(stasiun_asal_sa)), LOWER(TRIM(nama_ka_stasiun_asal)), LOWER(TRIM(COALESCE(nomor_sarana,''))), LOWER(TRIM(COALESCE(stasiun_tujuan_sa,'')))))";
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

    public function exportPreviewDataExcel(Request $request)
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
            ->get();

        // Get format from request (csv or xlsx), default to csv
        $format = $request->input('format', 'csv');
        
        if ($format === 'xlsx') {
            return $this->exportPreviewDataXlsx($data);
        }

        $fileName = 'preview-data-angkutan.csv';

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Jenis Angkutan',
                'Customer',
                'Stasiun Asal',
                'Stasiun Tujuan',
                'Nama KA',
                'Tanggal',
                'No. Sarana',
                'Volume (kg)',
                'Koli',
                'Status',
            ]);

            foreach ($data as $item) {
                fputcsv($out, [
                    ucfirst($item->jenis_angkutan),
                    $item->nama_customer,
                    $item->stasiun_asal_sa,
                    $item->stasiun_tujuan_sa ?? '-',
                    $item->nama_ka_stasiun_asal,
                    optional($item->tanggal_keberangkatan_asal_ka)->format('Y-m-d'),
                    $item->nomor_sarana ?? '-',
                    (string) $item->volume_berat_kai,
                    (string) $item->banyaknya_pengajuan,
                    ucfirst($item->status_sa ?? 'pending'),
                ]);
            }
            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function exportPreviewDataXlsx($data)
    {
        $fileName = 'preview-data-angkutan.xlsx';
        
        // Create proper XLSX using PHP with ZIP format
        return response()->streamDownload(function () use ($data) {
            // Create temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
            $zip = new \ZipArchive();
            $zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            
            // Add mimetype file
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
<Override PartName="/xl/theme/theme1.xml" ContentType="application/vnd.openxmlformats-officedocument.theme+xml"/>
<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
</Types>');
            
            // Add relationships
            $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
</Relationships>');
            
            // Add workbook relationships
            $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="theme/theme1.xml"/>
</Relationships>');
            
            // Add styles
            $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<fonts count="2">
<font><sz val="11"/><color theme="1"/><name val="Calibri"/></font>
<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>
</fonts>
<fills count="3">
<fill><patternFill patternType="none"/></fill>
<fill><patternFill patternType="gray125"/></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FF366092"/></patternFill></fill>
</fills>
<borders count="2">
<border><left/><right/><top/><bottom/><diagonal/></border>
<border><left style="thin"><color auto/></left><right style="thin"><color auto/></right><top style="thin"><color auto/></top><bottom style="thin"><color auto/></bottom></border>
</borders>
<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
<cellXfs count="3">
<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>
<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>
</cellXfs>
<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
<dxfs count="0"/><tableStyles count="0"/></styleSheet>');
            
            // Add theme
            $zip->addFromString('xl/theme/theme1.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<a:theme xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" name="Office Theme">
<a:themeElements><a:clrScheme name="Office"><a:dk1><a:srgbClr val="000000"/></a:dk1><a:lt1><a:srgbClr val="FFFFFF"/></a:lt1></a:clrScheme></a:themeElements>
</a:theme>');
            
            // Generate worksheet XML
            $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<sheetData>';
            
            // Header row
            $headers = [
                'Jenis Angkutan',
                'Customer',
                'Stasiun Asal',
                'Stasiun Tujuan',
                'Nama KA',
                'Tanggal',
                'No. Sarana',
                'Volume (kg)',
                'Koli',
                'Status',
            ];
            
            $xml .= '<row r="1">';
            foreach ($headers as $col => $header) {
                $cellRef = chr(65 + $col) . '1';
                $xml .= '<c r="' . $cellRef . '" s="1" t="str"><v>' . htmlspecialchars($header) . '</v></c>';
            }
            $xml .= '</row>';
            
            // Data rows
            $rowNum = 2;
            foreach ($data as $item) {
                $xml .= '<row r="' . $rowNum . '">';
                $xml .= '<c r="A' . $rowNum . '" t="str"><v>' . htmlspecialchars(ucfirst($item->jenis_angkutan)) . '</v></c>';
                $xml .= '<c r="B' . $rowNum . '" t="str"><v>' . htmlspecialchars($item->nama_customer) . '</v></c>';
                $xml .= '<c r="C' . $rowNum . '" t="str"><v>' . htmlspecialchars($item->stasiun_asal_sa) . '</v></c>';
                $xml .= '<c r="D' . $rowNum . '" t="str"><v>' . htmlspecialchars($item->stasiun_tujuan_sa ?? '-') . '</v></c>';
                $xml .= '<c r="E' . $rowNum . '" t="str"><v>' . htmlspecialchars($item->nama_ka_stasiun_asal) . '</v></c>';
                $xml .= '<c r="F' . $rowNum . '" t="str"><v>' . htmlspecialchars(optional($item->tanggal_keberangkatan_asal_ka)->format('Y-m-d') ?? '-') . '</v></c>';
                $xml .= '<c r="G' . $rowNum . '" t="str"><v>' . htmlspecialchars($item->nomor_sarana ?? '-') . '</v></c>';
                $xml .= '<c r="H' . $rowNum . '" t="n"><v>' . $item->volume_berat_kai . '</v></c>';
                $xml .= '<c r="I' . $rowNum . '" t="n"><v>' . $item->banyaknya_pengajuan . '</v></c>';
                $xml .= '<c r="J' . $rowNum . '" t="str"><v>' . htmlspecialchars(ucfirst($item->status_sa ?? 'pending')) . '</v></c>';
                $xml .= '</row>';
                $rowNum++;
            }
            
            $xml .= '</sheetData></worksheet>';
            $zip->addFromString('xl/worksheets/sheet1.xml', $xml);
            
            // Add workbook
            $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
<workbookPr date1904="false"/>
<sheets><sheet name="Data" sheetId="1" r:id="rId1"/></sheets>
</workbook>');
            
            // Add core properties
            $zip->addFromString('docProps/core.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/officeDocument/2006/custom-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<dc:title>Data Angkutan</dc:title>
<dc:creator>KAI</dc:creator>
<cp:lastModifiedBy>KAI</cp:lastModifiedBy>
<dcterms:created xsi:type="dcterms:W3CDTF">' . now()->toIso8601String() . '</dcterms:created>
<dcterms:modified xsi:type="dcterms:W3CDTF">' . now()->toIso8601String() . '</dcterms:modified>
</cp:coreProperties>');
            
            $zip->close();
            
            readfile($tempFile);
            unlink($tempFile);
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="preview-data-angkutan.xlsx"',
        ]);
    }
}

