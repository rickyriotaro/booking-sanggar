# Dashboard Update - Final Version

## ✅ Perubahan yang Telah Dilakukan:

### 1. 📊 **Card "Order Berjalan" - UPDATED**

**Logic Baru:**

```php
$activeOrders = Order::where('status', 'paid')
    ->where(function($query) {
        $query->whereNull('return_status')
              ->orWhere('return_status', 'belum');
    })
    ->count();
```

**Penjelasan:**

-   Menampilkan order yang **sudah dibayar (`paid`)**
-   DAN **belum dikembalikan** (`return_status` = NULL atau 'belum')
-   Ini adalah order yang **aktif/sedang berjalan**

---

### 2. 🥧 **Grafik Pie Chart - Ganti dari Order Status ke Return Status**

**Sebelumnya:** Menampilkan breakdown Order Status (Pending, Confirmed, Completed, Cancelled)

**Sekarang:** Menampilkan breakdown Return Status:

#### **4 Kategori Return Status:**

1. **🟡 Belum Dikembalikan** (Yellow)

    - Query: `status = 'paid'` AND (`return_status` is NULL OR `return_status = 'belum'`)
    - Meaning: Sudah dibayar tapi belum dikembalikan

2. **🟢 Sudah Dikembalikan** (Green)

    - Query: `return_status = 'sudah'`
    - Meaning: Sudah dikembalikan tepat waktu

3. **🟠 Terlambat** (Orange)

    - Query: `return_status = 'terlambat'`
    - Meaning: Dikembalikan terlambat

4. **🔴 Gagal** (Red)
    - Query: `return_status = 'gagal'`
    - Meaning: Gagal dikembalikan

**Catatan Penting:**

-   Untuk kategori "Belum", hanya menghitung order yang **SUDAH PAID**
-   Jadi order yang belum dibayar (`pending`) **TIDAK AKAN MUNCUL** di grafik ini

---

### 3. 📈 **Summary Cards:**

1. **🔵 Total User** - Jumlah user (exclude admin)
2. **🟠 Order Berjalan** - Order yang sudah paid tapi belum dikembalikan
3. **🟢 Item Dikembalikan** - Item dengan return_status = sudah atau terlambat
4. **🔴 Total Pendapatan** - Total revenue dari transaksi settlement

---

## 🎯 **Schema Return Status di Database:**

Berdasarkan implementasi, return_status kemungkinan values:

-   `NULL` atau `'belum'` - Belum dikembalikan
-   `'sudah'` - Sudah dikembalikan
-   `'terlambat'` - Terlambat dikembalikan
-   `'gagal'` - Gagal dikembalikan

---

## 📝 **File yang Dimodifikasi:**

1. **`app/Http/Controllers/Admin/DashboardController.php`**

    - Updated `$activeOrders` query
    - Replaced `$orderStatusData` with `$returnStatusData`
    - Added 4 separate queries for each return status

2. **`resources/views/admin/dashboard.blade.php`**
    - Updated pie chart title: "Status Pesanan" → "Status Pengembalian"
    - Changed canvas ID: `orderStatusChart` → `returnStatusChart`
    - Updated Chart.js data to use `$returnStatusData`
    - Changed labels and colors to match return status categories

---

## ✨ **Hasil Akhir:**

Dashboard sekarang menampilkan:

-   ✅ Card "Order Berjalan" = Orders yang paid tapi belum dikembalikan
-   ✅ Pie Chart "Status Pengembalian" = Breakdown berdasarkan return_status (hanya yang paid untuk "belum")

Refresh halaman dashboard untuk melihat perubahan! 🎉
