<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Distribusi Sampah Keluar</title>
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
        .badge-agent { background-color: #e9f5ec; color: #3f7d4a; border: 1px solid #3f7d4a; }
        .badge-unit { background-color: #fdf5eb; color: #b8752b; border: 1px solid #b8752b; }
        .summary-box { margin-top: 20px; border-top: 2px dashed #c9c7b4; padding-top: 10px; }
        .summary-box table { width: 40%; margin-left: auto; border-collapse: collapse; }
        .summary-box td { padding: 4px; font-size: 12px; }
        .summary-box td.label { font-weight: bold; color: #123526; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Distribusi Sampah Keluar EcoBank</h1>
        <p>SMKN 2 Indramayu · Portal Manajemen</p>
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td><strong>Periode Laporan:</strong> Semua Riwayat Distribusi</td>
                <td class="text-right"><strong>Dicetak Oleh:</strong> {{ Auth::user()->name }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal Cetak:</strong> {{ date('d-m-Y H:i:s') }}</td>
                <td class="text-right"><strong>Total Baris:</strong> {{ count($distributions) }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal Batch</th>
                <th>Jalur</th>
                <th>Nama Agen / Unit</th>
                <th>Rincian Item</th>
                <th class="text-right">Total Berat (Kg)</th>
                <th class="text-right">Total Nilai (Rp)</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($distributions as $dist)
                <tr>
                    <td>{{ $dist->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($dist->batch_date)->format('d-m-Y') }}</td>
                    <td class="text-center">
                        <span class="badge {{ $dist->route === 'agent' ? 'badge-agent' : 'badge-unit' }}">
                            {{ $dist->route === 'agent' ? 'Agen' : 'Internal' }}
                        </span>
                    </td>
                    <td>{{ $dist->agent_name ?: '-' }}</td>
                    <td>
                        @foreach($dist->items as $item)
                            <div>· {{ $item->wasteCategory->name ?? 'N/A' }}: {{ number_format($item->weight, 2, ',', '.') }} kg @Rp {{ number_format($item->price_per_kg, 0, ',', '.') }}</div>
                        @endforeach
                    </td>
                    <td class="text-right">{{ number_format($dist->total_weight, 2, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($dist->total_value, 0, ',', '.') }}</td>
                    <td>{{ $dist->notes ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px;">Belum ada riwayat distribusi sampah.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(count($distributions) > 0)
        <div class="summary-box">
            <table>
                <tr>
                    <td class="label">Total Berat Keluar:</td>
                    <td class="text-right">{{ number_format($distributions->sum('total_weight'), 2, ',', '.') }} kg</td>
                </tr>
                <tr>
                    <td class="label">Total Kas Masuk (Agen):</td>
                    <td class="text-right">Rp {{ number_format($distributions->where('route', 'agent')->sum('total_value'), 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    @endif
</body>
</html>
