<?php

namespace App\Exports;

use App\Models\Distribution;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DistributionsExport implements FromQuery, WithMapping, WithHeadings
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function map($distribution): array
    {
        $itemsStr = $distribution->items->map(function ($item) {
            $catName = $item->wasteCategory ? $item->wasteCategory->name : 'N/A';
            return "{$catName}: {$item->weight}kg (@Rp " . number_format($item->price_per_kg, 0, ',', '.') . ")";
        })->implode('; ');

        return [
            $distribution->id,
            $distribution->batch_date,
            $distribution->route === 'agent' ? 'Jual ke Agen' : 'Unit Pengolahan Internal',
            $distribution->agent_name ?: '-',
            $distribution->total_weight,
            $distribution->total_value,
            $itemsStr,
            $distribution->creator ? $distribution->creator->name : 'N/A',
            $distribution->notes ?: '-',
        ];
    }

    public function headings(): array
    {
        return [
            'ID Distribusi',
            'Tanggal Batch',
            'Jalur Distribusi',
            'Nama Agen/Unit',
            'Total Berat (Kg)',
            'Total Nilai (Rp)',
            'Rincian Item (Kategori: Berat @Harga)',
            'Dicatat Oleh',
            'Catatan',
        ];
    }
}
