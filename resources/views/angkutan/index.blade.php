<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Angkutan</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #f3f3f3; }
    </style>
</head>
<body>

<h2>Data Angkutan Parcel</h2>

<table>
    <thead>
        <tr>
            <th>Nama Customer</th>
            <th>Stasiun Asal</th>
            <th>Nama KA Stasiun</th>
            <th>Tgl Keberangkatan</th>
            <th>Nomor Sarana</th>
            <th>Tonase</th>
            <th>Koli</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
        <tr>
            <td>{{ $row->customer->nama_customer }}</td>
            <td>{{ $row->station->kode_stasiun }}</td>
            <td>{{ $row->station->nama_ka_stasiun }}</td>
            <td>{{ $row->tanggal_keberangkatan->format('Y-m-d') }}</td>
            <td>{{ $row->nomor_sarana }}</td>
            <td>{{ number_format($row->tonase, 0, ',', '.') }}</td>
            <td>{{ $row->koli }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>