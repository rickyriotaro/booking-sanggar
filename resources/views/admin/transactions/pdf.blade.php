<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi</title>
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
        .badge-success { background-color: #28a745; color: #fff; }
        .badge-danger { background-color: #dc3545; color: #fff; }
        .badge-info { background-color: #17a2b8; color: #fff; }
        .badge-secondary { background-color: #6c757d; color: #fff; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN DATA TRANSAKSI</h2>
        <p>RANTS - Ray Entertainments</p>
    </div>

    @if(isset($filters) && (isset($filters['date_from']) || isset($filters['date_to']) || isset($filters['status'])))
    <div class="filters">
        <strong>Filter:</strong>
        @if(isset($filters['date_from']))
            Dari: {{ date('d/m/Y', strtotime($filters['date_from'])) }}
        @endif
        @if(isset($filters['date_to']))
            Sampai: {{ date('d/m/Y', strtotime($filters['date_to'])) }}
        @endif
        @if(isset($filters['status']))
            | Status: {{ ucfirst($filters['status']) }}
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 12%;">Kode Transaksi</th>
                <th style="width: 12%;">Kode Order</th>
                <th style="width: 15%;">Customer</th>
                <th style="width: 15%;" class="text-end">Jumlah</th>
                <th style="width: 12%;">Metode Bayar</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 18%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $transaction)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $transaction->transaction_code ?? '-' }}</td>
                <td>{{ $transaction->order->order_code ?? '-' }}</td>
                <td>{{ $transaction->order->user->name ?? '-' }}</td>
                <td class="text-end">Rp {{ number_format($transaction->amount ?? 0, 0, ',', '.') }}</td>
                <td class="text-center">{{ strtoupper($transaction->payment_method ?? '-') }}</td>
                <td class="text-center">
                    {{ $transaction->created_at ? $transaction->created_at->format('d/m/Y H:i') : '-' }}
                </td>
                <td>
                    @if($transaction->pg_status == 'pending')
                        <span class="badge badge-warning">Pending</span>
                    @elseif($transaction->pg_status == 'settlement' || $transaction->pg_status == 'capture')
                        <span class="badge badge-success">Berhasil</span>
                    @elseif($transaction->pg_status == 'expire')
                        <span class="badge badge-danger">Kadaluarsa</span>
                    @elseif($transaction->pg_status == 'cancel')
                        <span class="badge badge-danger">Dibatalkan</span>
                    @elseif($transaction->pg_status == 'deny')
                        <span class="badge badge-danger">Ditolak</span>
                    @else
                        <span class="badge badge-secondary">{{ ucfirst($transaction->pg_status ?? '-') }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data transaksi</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 9px;">
        <p>Total Data: {{ $transactions->count() }} transaksi</p>
        <p>Total Nominal: Rp {{ number_format($transactions->sum('amount'), 0, ',', '.') }}</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
