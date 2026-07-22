<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DynamicTransactionsExport implements FromQuery, WithMapping, WithHeadings
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

    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->created_at->format('Y-m-d H:i:s'),
            $transaction->student ? $transaction->student->name : 'N/A',
            $transaction->student ? $transaction->student->class : 'N/A',
            $transaction->type === 'setor' ? 'Setor' : 'Tarik',
            $transaction->wasteCategory ? $transaction->wasteCategory->name : 'Tarik Dana',
            $transaction->weight ? $transaction->weight : '-',
            $transaction->amount,
            $transaction->points,
            $transaction->status,
        ];
    }

    public function headings(): array
    {
        return [
            'ID Transaksi',
            'Tanggal',
            'Nama Siswa',
            'Kelas',
            'Tipe',
            'Kategori Sampah',
            'Berat (Kg)',
            'Nominal (Rp)',
            'Poin',
            'Status',
        ];
    }
}
