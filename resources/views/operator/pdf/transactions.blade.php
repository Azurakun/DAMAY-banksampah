<!DOCTYPE html>
<html>
<head>
    <title>Laporan Riwayat Transaksi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>Laporan Riwayat Transaksi - EcoBank SMKN 2 Indramayu</h2>
    <p>Dicetak pada: {{ date('d-m-Y H:i:s') }}</p>
    <table>
        <thead>
            <tr>
                <th>ID Transaksi</th>
                <th>Tanggal</th>
                <th>Nama Siswa</th>
                <th>Tipe</th>
                <th>Kategori Sampah</th>
                <th>Berat (Kg)</th>
                <th>Nominal (Rp)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $trx)
            <tr>
                <td>{{ $trx->id }}</td>
                <td>{{ $trx->created_at->format('d-m-Y H:i') }}</td>
                <td>{{ $trx->student ? $trx->student->name : '-' }}</td>
                <td>{{ ucfirst($trx->type) }}</td>
                <td>{{ $trx->wasteCategory ? $trx->wasteCategory->name : '-' }}</td>
                <td>{{ $trx->weight ?? 0 }}</td>
                <td>{{ number_format($trx->amount, 0, ',', '.') }}</td>
                <td>{{ $trx->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
