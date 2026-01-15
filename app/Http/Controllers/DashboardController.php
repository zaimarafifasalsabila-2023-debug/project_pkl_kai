<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Angkutan;
use App\Models\Customer;
use App\Models\Station;

class DashboardController extends Controller
{
    public function index()
    {
        $totalAngkutan = Angkutan::count();
        $totalCustomer = Customer::count();
        $totalStation = Station::count();
        
        return view('dashboard.index', compact('totalAngkutan', 'totalCustomer', 'totalStation'));
    }

    public function inputData()
    {
        return view('dashboard.input-data');
    }

    public function previewData()
    {
        $data = Angkutan::orderBy('tanggal_keberangkatan_asal_ka', 'desc')->get();
            
        return view('dashboard.preview-data', compact('data'));
    }

    public function statistik()
    {
        // Data untuk statistik
        $monthlyData = Angkutan::selectRaw('MONTH(tanggal_keberangkatan_asal_ka) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
            
        $stationData = Angkutan::all()
            ->groupBy('nama_ka_stasiun_asal')
            ->map(function($item) {
                return $item->count();
            });
            
        return view('dashboard.statistik', compact('monthlyData', 'stationData'));
    }

    public function uploadKedatangan(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:10240'
        ]);

        try {
            $file = $request->file('file');
            $data = $this->processExcelFile($file, 'kedatangan');
            
            return redirect()->route('preview.data')->with('success', "Berhasil upload {$data['count']} data kedatangan!");
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
            
            return redirect()->route('preview.data')->with('success', "Berhasil upload {$data['count']} data muat!");
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
