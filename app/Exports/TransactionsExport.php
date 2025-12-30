<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Transaction::with(['order.user']);

        // Filter by date range
        if (!empty($this->filters['start_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['start_date']);
        }
        if (!empty($this->filters['end_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['end_date']);
        }

        // Filter by status
        if (!empty($this->filters['pg_status'])) {
            $query->where('pg_status', $this->filters['pg_status']);
        }

        return $query->latest('created_at')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Transaksi',
            'Kode Order',
            'Customer',
            'Email',
            'Metode Pembayaran',
            'Jumlah',
            'Status',
            'Tanggal Dibuat'
        ];
    }

    public function map($transaction): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $transaction->transaction_code ?? '-',
            $transaction->order->order_code ?? '-',
            $transaction->order->user->name ?? '-',
            $transaction->order->user->email ?? '-',
            strtoupper($transaction->payment_method ?? '-'),
            'Rp ' . number_format($transaction->amount ?? 0, 0, ',', '.'),
            strtoupper($transaction->pg_status ?? '-'),
            $transaction->created_at ? $transaction->created_at->format('d/m/Y H:i') : '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DC2626']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]
            ],
        ];
    }
}
