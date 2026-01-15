<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Operasional</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-white text-gray-900">
    <div class="max-w-6xl mx-auto p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold">Export Tabel Operasional</h1>
                <div class="text-sm text-gray-600 mt-1">
                    <div>Periode: {{ $tanggalAwal && $tanggalAkhir ? ($tanggalAwal . ' s/d ' . $tanggalAkhir) : ( ($bulan ? ('Bulan ' . $bulan . ' / ') : '') . $tahun ) }}</div>
                    <div>Jenis: {{ $jenis ?: 'Semua' }}</div>
                </div>
            </div>
            <div class="no-print flex gap-2">
                <button onclick="window.print()" class="px-4 py-2 bg-gray-900 text-white rounded-lg">Cetak</button>
                <button onclick="window.close()" class="px-4 py-2 border border-gray-300 rounded-lg">Tutup</button>
            </div>
        </div>

        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stasiun Asal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stasiun Tujuan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama KA</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Sarana</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Volume (kg)</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Koli</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($rows as $item)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">{{ optional($item->tanggal_keberangkatan_asal_ka)->format('Y-m-d') ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $item->nama_customer ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $item->stasiun_asal_sa ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $item->stasiun_tujuan_sa ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $item->nama_ka_stasiun_asal ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $item->nomor_sarana ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">{{ number_format((float)($item->volume_berat_kai ?? 0), 2) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">{{ number_format((int)($item->banyaknya_pengajuan ?? 0)) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $item->status_sa ?? 'Pending' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $item->jenis_angkutan ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="text-xs text-gray-500 mt-4">Dicetak pada: {{ now()->format('Y-m-d H:i') }}</div>
    </div>
</body>
</html>
