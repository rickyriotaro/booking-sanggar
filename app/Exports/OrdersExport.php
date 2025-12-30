<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Order::with(['user', 'orderDetails']);

        // Filter by date range
        if (!empty($this->filters['start_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['start_date']);
        }
        if (!empty($this->filters['end_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['end_date']);
        }

        // Filter by status
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Order',
            'Customer',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Total Items',
            'Total Harga',
            'Status',
            'Status Pengembalian',
            'Tanggal Order'
        ];
    }

    public function map($order): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $order->order_code ?? 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
            $order->user->name,
            $order->start_date->format('d/m/Y'),
            $order->end_date->format('d/m/Y'),
            $order->orderDetails->sum('quantity'),
            'Rp ' . number_format($order->total_price ?? $order->total_amount, 0, ',', '.'),
            ucfirst($order->status),
            ucfirst($order->return_status),
            $order->created_at->format('d/m/Y H:i')
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
