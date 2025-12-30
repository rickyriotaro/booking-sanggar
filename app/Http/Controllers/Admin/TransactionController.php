<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Order;
use App\Exports\TransactionsExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['order.user']);

        // Search by transaction code, order ID, or customer name/email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                  ->orWhereHas('order', function($orderQ) use ($search) {
                      $orderQ->where('order_code', 'like', "%{$search}%")
                             ->orWhere('id', 'like', "%{$search}%");
                  })
                  ->orWhereHas('order.user', function($userQ) use ($search) {
                      $userQ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('pg_status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->latest()->paginate(5);

        // Calculate statistics
        $totalTransactions = Transaction::count();
        $totalRevenue = Transaction::where('pg_status', 'settlement')->sum('amount');
        $successTransactions = Transaction::where('pg_status', 'settlement')->count();
        $pendingTransactions = Transaction::where('pg_status', 'pending')->count();

        return view('admin.transactions.index', compact(
            'transactions',
            'totalTransactions',
            'totalRevenue',
            'successTransactions',
            'pendingTransactions'
        ));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['order.user', 'order.orderDetails']);
        return view('admin.transactions.show', compact('transaction'));
    }

    public function report(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth());

        $transactions = Transaction::with(['order.user'])
            ->where('pg_status', 'settlement')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->get();

        $totalRevenue = $transactions->sum('amount');
        $transactionCount = $transactions->count();

        // Group by payment method
        $byPaymentMethod = $transactions->groupBy('payment_method')->map(function ($items) {
            return [
                'count' => $items->count(),
                'total' => $items->sum('amount')
            ];
        });

        return view('admin.transactions.report', compact(
            'transactions',
            'totalRevenue',
            'transactionCount',
            'byPaymentMethod',
            'startDate',
            'endDate'
        ));
    }

    public function exportExcel(Request $request)
    {
        $filters = [
            'start_date' => $request->date_from,
            'end_date' => $request->date_to,
            'pg_status' => $request->status,
        ];

        $filename = 'transaksi_' . date('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new TransactionsExport($filters), $filename);
    }

    public function exportPdf(Request $request)
    {
        $query = Transaction::with(['order.user']);

        // Apply filters
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->status) {
            $query->where('pg_status', $request->status);
        }

        $transactions = $query->latest('created_at')->get();
        $filters = $request->only(['date_from', 'date_to', 'status']);

        $pdf = Pdf::loadView('admin.transactions.pdf', compact('transactions', 'filters'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('transaksi_' . date('Y-m-d_His') . '.pdf');
    }
}
