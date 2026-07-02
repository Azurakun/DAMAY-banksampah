<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionsExport implements FromQuery, WithMapping, WithHeadings
{
    public function query()
    {
        // Load with student and category
        return Transaction::with(['student', 'wasteCategory'])->orderBy('created_at', 'desc');
    }

    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->created_at->format('Y-m-d H:i:s'),
            $transaction->student ? $transaction->student->name : 'N/A',
            $transaction->type,
            $transaction->wasteCategory ? $transaction->wasteCategory->name : '-',
            $transaction->weight,
            $transaction->amount,
            $transaction->status,
        ];
    }

    public function headings(): array
    {
        return [
            'ID Transaksi',
            'Tanggal',
            'Nama Siswa',
            'Tipe',
            'Kategori Sampah',
            'Berat (Kg)',
            'Nominal (Rp)',
            'Status',
        ];
    }
}
