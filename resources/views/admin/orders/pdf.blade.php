<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pemesanan</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 18px;
        }
        .filters {
            margin-bottom: 15px;
            font-size: 9px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background-color: #dc3545;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 9px;
            border: 1px solid #dee2e6;
        }
        td {
            padding: 6px 8px;
            border: 1px solid #dee2e6;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-warning { background-color: #ffc107; color: #000; }
        .badge-info { background-color: #17a2b8; color: #fff; }
        .badge-success { background-color: #28a745; color: #fff; }
        .badge-secondary { background-color: #6c757d; color: #fff; }
        .badge-danger { background-color: #dc3545; color: #fff; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN DATA PEMESANAN</h2>
        <p>RANTS - Ray Entertainments</p>
    </div>

    @if(isset($filters) && (isset($filters['start_date']) || isset($filters['end_date']) || isset($filters['status'])))
    <div class="filters">
        <strong>Filter:</strong>
        @if(isset($filters['start_date']))
            Dari: {{ date('d/m/Y', strtotime($filters['start_date'])) }}
        @endif
        @if(isset($filters['end_date']))
            Sampai: {{ date('d/m/Y', strtotime($filters['end_date'])) }}
        @endif
        @if(isset($filters['status']))
            | Status: {{ ucfirst($filters['status']) }}
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 10%;">Kode Order</th>
                <th style="width: 12%;">Customer</th>
                <th style="width: 10%;">Tanggal Mulai</th>
                <th style="width: 10%;">Tanggal Selesai</th>
                <th style="width: 20%;">Detail Order</th>
                <th style="width: 12%;" class="text-end">Total Harga</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 13%;">Status Pengembalian</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $index => $order)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $order->order_code ?? '-' }}</td>
                <td>{{ $order->user->name ?? '-' }}</td>
                <td class="text-center">{{ $order->start_date ? $order->start_date->format('d/m/Y') : '-' }}</td>
                <td class="text-center">{{ $order->end_date ? $order->end_date->format('d/m/Y') : '-' }}</td>
                <td>
                    @if($order->orderDetails->count() > 0)
                        @foreach($order->orderDetails as $detail)
                            <div style="margin-bottom: 3px;">
                                - {{ $detail->costume->name ?? $detail->danceService->name ?? $detail->makeupService->name ?? '-' }}
                                ({{ $detail->quantity }}x)
                            </div>
                        @endforeach
                    @else
                        -
                    @endif
                </td>
                <td class="text-end">Rp {{ number_format($order->total_price ?? 0, 0, ',', '.') }}</td>
                <td>
                    @if($order->status == 'pending')
                        <span class="badge badge-warning">Pending</span>
                    @elseif($order->status == 'processing')
                        <span class="badge badge-info">Diproses</span>
                    @elseif($order->status == 'completed')
                        <span class="badge badge-success">Selesai</span>
                    @else
                        <span class="badge badge-secondary">{{ ucfirst($order->status) }}</span>
                    @endif
                </td>
                <td>
                    @if($order->return_status == 'pending')
                        <span class="badge badge-warning">Belum Kembali</span>
                    @elseif($order->return_status == 'returned')
                        <span class="badge badge-success">Sudah Kembali</span>
                    @elseif($order->return_status == 'late')
                        <span class="badge badge-danger">Terlambat</span>
                    @else
                        <span class="badge badge-secondary">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">Tidak ada data pemesanan</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 9px;">
        <p>Total Data: {{ $orders->count() }} pemesanan</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
