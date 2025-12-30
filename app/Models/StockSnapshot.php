<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockSnapshot extends Model
{
    protected $fillable = [
        'service_type',
        'service_id',
        'service_name',
        'stok_by_admin',
        'admin_history',
        'stok_from_orders',
        'sisa_stok_tersedia',
        'last_booking_date',
        'sisa_stok_setelah_booking',
        'last_edited_by_admin',
        'last_edited_at',
        'edit_reason'
    ];

    protected $casts = [
        'stok_by_admin' => 'integer',
        'stok_from_orders' => 'integer',
        'sisa_stok_tersedia' => 'integer',
        'sisa_stok_setelah_booking' => 'integer',
        'admin_history' => 'array',
        'last_booking_date' => 'datetime',
        'last_edited_at' => 'datetime',
    ];

    /**
     * Get the service (kostum, makeup, or dance)
     */
    public function getService()
    {
        return match($this->service_type) {
            'kostum' => Costume::find($this->service_id),
            'rias' => MakeupService::find($this->service_id),
            'tari' => DanceService::find($this->service_id),
            default => null
        };
    }

    /**
     * Add admin history entry
     */
    public function addAdminHistory($qty, $adminId, $reason = null)
    {
        // Ensure admin_history is array (not string)
        $history = $this->admin_history;
        
        if (is_string($history)) {
            $history = json_decode($history, true) ?? [];
        } elseif (!is_array($history)) {
            $history = [];
        }
        
        $history[] = [
            'qty' => $qty,
            'admin_id' => $adminId,
            'reason' => $reason,
            'date' => now()->toIso8601String(),
        ];
        
        $this->admin_history = $history;
        return $this;
    }

    /**
     * Calculate available quantity
     */
    public function calculateAvailable()
    {
        return max(0, $this->stok_by_admin - $this->stok_from_orders);
    }

    /**
     * Recalculate booked and available quantities from actual orders
     * 
     * PENTING:
     * - stok_by_admin = stok yang set admin (TIDAK BOLEH BERUBAH karena order)
     * - stok_from_orders = jumlah yang sedang di-booking (sum of all active orders)
     * - sisa_stok_tersedia = available qty pada HARI INI (stok_by_admin - booked qty hari ini)
     * - sisa_stok_setelah_booking = stok yang kembali setelah end_date (= stok_by_admin)
     */
    public function recalculate()
    {
        // Total qty yang sedang di-booking (semua status kecuali cancelled/returned)
        $totalBooked = OrderDetail::where('service_type', $this->service_type)
            ->where('detail_id', $this->service_id)
            ->whereHas('order', function ($q) {
                $q->where('status', '!=', 'cancelled')
                  ->where(function ($query) {
                      $query->where('return_status', '!=', 'sudah')
                            ->where('return_status', '!=', 'terlambat')
                            ->orWhereNull('return_status');
                  });
            })
            ->sum('quantity');

        // Available qty HARI INI = admin stock - qty yang booked hari ini
        $bookedToday = OrderDetail::where('service_type', $this->service_type)
            ->where('detail_id', $this->service_id)
            ->whereHas('order', function ($q) {
                $today = now()->format('Y-m-d');
                $q->where('status', '!=', 'cancelled')
                  ->where('start_date', '<=', $today)
                  ->where('end_date', '>=', $today)
                  ->where(function ($query) {
                      $query->where('return_status', '!=', 'sudah')
                            ->where('return_status', '!=', 'terlambat')
                            ->orWhereNull('return_status');
                  });
            })
            ->sum('quantity');

        $availableToday = max(0, $this->stok_by_admin - $bookedToday);

        // Stok setelah end_date = stok_by_admin (semua kembali)
        $stockAfterBooking = $this->stok_by_admin;

        // Get latest booking date (end_date dari order, bukan created_at)
        // Cari order dengan end_date paling belakang yang belum di-return
        $lastBooking = OrderDetail::where('service_type', $this->service_type)
            ->where('detail_id', $this->service_id)
            ->whereHas('order', function ($q) {
                $q->where('return_status', '!=', 'sudah')
                  ->where('return_status', '!=', 'terlambat')
                  ->orWhereNull('return_status');
            })
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->max('orders.end_date');

        // Set attributes (not update) so caller can save() if needed
        $this->stok_from_orders = $totalBooked;
        $this->sisa_stok_tersedia = $availableToday;
        $this->sisa_stok_setelah_booking = $stockAfterBooking;
        $this->last_booking_date = $lastBooking;

        return $availableToday;
    }
}
