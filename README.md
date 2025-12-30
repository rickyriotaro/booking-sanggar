# Sistem Booking Jasa Sanggar (RANTS)

Aplikasi ini adalah sistem manajemen booking dan administrasi untuk sanggar seni (RANTS) yang mencakup penyewaan kostum, layanan tari, layanan makeup, dan pengelolaan jadwal. Dibangun menggunakan framework Laravel.

## Fitur Utama

### 1. Dashboard Admin

-   **Ringkasan**: Menampilkan statistik penting (total order, pendapatan, jadwal mendatang).
-   **Grafik**: Visualisasi data transaksi dan performa layanan.

### 2. Manajemen Layanan

-   **Kostum**: Kelola data kostum (tambah, edit, hapus, stok, harga).
-   **Layanan Tari**: Kelola jenis tarian dan paket layanan.
-   **Layanan Makeup**: Kelola paket dan harga layanan makeup.

### 3. Manajemen Order & Transaksi

-   **Daftar Order**: Lihat dan kelola status order (Pending, Paid, Completed, Cancelled).
-   **Status Pengembalian**: Lacak status pengembalian barang sewaan.
-   **Laporan Transaksi**: Ekspor laporan transaksi ke format PDF dan Excel.

### 4. Jadwal & Kalender (Schedule)

-   **Kalender Interaktif**: Tampilan visual jadwal booking untuk menghindari bentrok jadwal.
-   **Cek Ketersediaan**: Fitur untuk melihat tanggal yang sudah di-booking.

### 5. Chat Support

-   **Fitur Chat**: Komunikasi langsung dengan pelanggan melalui dashboard admin.
-   **Manajemen Sesi Chat**: Assign chat ke admin tertentu, tutup sesi chat.

### 6. Galeri

-   **Manajemen Galeri**: Upload dan kelola foto kegiatan atau portofolio sanggar.

### 7. Manajemen Pengguna & Keamanan

-   **Role Management**: Pengaturan hak akses (Admin).
-   **Profil Admin**: Update profil dan password.
-   **Autentikasi**: Sistem login aman.

## Teknologi yang Digunakan

-   **Backend**: Laravel 11/12 (PHP ^8.2)
-   **Database**: MySQL / MariaDB
-   **Frontend**: Blade Templating, Bootstrap / Tailwind (sesuaikan dengan yang digunakan)
-   **Libraries Penting**:
    -   `barryvdh/laravel-dompdf`: Export PDF.
    -   `maatwebsite/excel`: Export Excel.
    -   `kreait/firebase-php`: Integrasi Firebase (kemungkinan untuk notifikasi atau chat real-time).
    -   `midtrans/midtrans-php`: Payment gateway.

## Instalasi

1. **Clone Repository**

    ```bash
    git clone https://github.com/username/repo-name.git
    cd nama-folder-project
    ```

2. **Install Dependencies**

    ```bash
    composer install
    npm install && npm run build
    ```

3. **Konfigurasi Environment**

    - Salin file `.env.example` menjadi `.env`:
        ```bash
        cp .env.example .env
        ```
    - Atur konfigurasi database di file `.env`:
        ```
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=nama_database_anda
        DB_USERNAME=root
        DB_PASSWORD=
        ```

4. **Generate Key & Migrasi**

    ```bash
    php artisan key:generate
    php artisan migrate --seed
    ```

5. **Jalankan Aplikasi**
    ```bash
    php artisan serve
    ```
    Akses aplikasi di `http://localhost:8000`.

## Catatan Tambahan

-   Pastikan konfigurasi Midtrans di `.env` sudah sesuai jika menggunakan fitur pembayaran online.
-   Untuk fitur export PDF/Excel, pastikan extension PHP yang dibutuhkan (seperti `gd` atau `zip`) sudah aktif.

## Lisensi

[RANTS Sanggar]
