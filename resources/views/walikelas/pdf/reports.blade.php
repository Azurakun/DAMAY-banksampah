<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi Kelas</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #1c2620; background: #faf9f2; }
        .header { text-align: center; border-bottom: 2px solid #123526; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 0; color: #123526; font-family: Georgia, serif; }
        .header p { margin: 4px 0 0 0; font-size: 12px; color: #55594E; }
        .meta-info { margin-bottom: 15px; font-size: 10px; color: #55594E; }
        .meta-info table { width: 100%; border: none; }
        .meta-info td { border: none; padding: 2px 0; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #c9c7b4; padding: 6px; text-align: left; }
        table.data-table th { background-color: #e9e8da; color: #123526; font-weight: bold; }
        table.data-table tr:nth-child(even) { background-color: #f3f2e7; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .badge-setor { background-color: #e9f5ec; color: #3f7d4a; border: 1px solid #3f7d4a; }
        .badge-tarik { background-color: #fcf1f0; color: #a63a2e; border: 1px solid #a63a2e; }
        .badge-success { background-color: #e9f5ec; color: #3f7d4a; }
        .badge-pending { background-color: #fdf5eb; color: #b8752b; }
        .badge-cancel { background-color: #fcf1f0; color: #a63a2e; }
        .summary-box { margin-top: 20px; border-top: 2px dashed #c9c7b4; padding-top: 10px; }
        .summary-box table { width: 40%; margin-left: auto; border-collapse: collapse; }
        .summary-box td { padding: 4px; font-size: 12px; }
        .summary-box td.label { font-weight: bold; color: #123526; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Transaksi Kelas Asuhan EcoBank</h1>
        <p>SMKN 2 Indramayu · Portal Wali Kelas</p>
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td><strong>Periode Laporan:</strong> {{ $filters['start_date'] ?? 'Awal' }} s/d {{ $filters['end_date'] ?? 'Hari Ini' }}</td>
                <td class="text-right"><strong>Wali Kelas:</strong> {{ Auth::user()->name }}</td>
            </tr>
            <tr>
                <td><strong>Kategori:</strong> {{ $categoryName ?? 'Semua Kategori' }}</td>
                <td class="text-right"><strong>Tanggal Cetak:</strong> {{ date('d-m-Y H:i:s') }}</td>
            </tr>
            <tr>
                <td><strong>Kelas:</strong> {{ $filters['class'] ?? 'Semua Kelas Asuhan' }}</td>
                <td class="text-right"><strong>Total Baris:</strong> {{ count($transactions) }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Nasabah (Siswa)</th>
                <th>Kelas</th>
                <th>Tipe</th>
                <th>Kategori Sampah</th>
                <th class="text-right">Berat (Kg)</th>
                <th class="text-right">Nominal (Rp)</th>
                <th class="text-right">Poin</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
                <tr>
                    <td>{{ $tx->id }}</td>
                    <td>{{ $tx->created_at->format('d-m-Y H:i') }}</td>
                    <td>{{ $tx->student->name ?? 'N/A' }}</td>
                    <td>{{ $tx->student->class ?? 'N/A' }}</td>
                    <td class="text-center">
                        <span class="badge {{ $tx->type === 'setor' ? 'badge-setor' : 'badge-tarik' }}">
                            {{ $tx->type === 'setor' ? 'Setor' : 'Tarik' }}
                        </span>
                    </td>
                    <td>{{ $tx->wasteCategory->name ?? ($tx->type === 'setor' ? 'Sampah' : 'Tarik Dana') }}</td>
                    <td class="text-right">{{ $tx->weight ? number_format($tx->weight, 2, ',', '.') : '-' }}</td>
                    <td class="text-right">Rp {{ number_format($tx->amount, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($tx->points, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="badge {{ $tx->status === 'Berhasil' ? 'badge-success' : ($tx->status === 'Menunggu' ? 'badge-pending' : 'badge-cancel') }}">
                            {{ $tx->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 20px;">Tidak ditemukan data transaksi untuk kelas asuhan Anda.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(count($transactions) > 0)
        <div class="summary-box">
            <table>
                <tr>
                    <td class="label">Total Setor Sampah:</td>
                    <td class="text-right">{{ number_format($totalWeight, 2, ',', '.') }} kg</td>
                </tr>
                <tr>
                    <td class="label">Total Uang Setoran:</td>
                    <td class="text-right">Rp {{ number_format($totalSetorAmount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Total Penarikan Dana:</td>
                    <td class="text-right">Rp {{ number_format($totalTarikAmount, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    @endif
</body>
</html>
