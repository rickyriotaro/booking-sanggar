# 📸 Panduan Sistem Upload Gambar untuk Hosting (Production)

**Tanggal Dibuat:** 28 Desember 2025  
**Status:** ✅ Sudah Diperbaiki & Production Ready  
**Referensi:** Sistem RANTS

---

## 📋 Ringkasan Masalah & Solusi

### ❌ Masalah Sebelumnya

Di hosting (cPanel/shared hosting), sistem upload gambar menggunakan Laravel Storage dengan symbolic link (`storage:link`) **TIDAK BERFUNGSI** dengan baik karena:

1. Shared hosting tidak mengizinkan symbolic link
2. Folder `storage/app/public` tidak bisa di-akses dari web
3. Gambar yang di-upload tidak bisa ditampilkan

### ✅ Solusi

**Upload gambar langsung ke folder `public/storage/`** tanpa menggunakan symbolic link. Dengan cara ini:

1. Gambar tersimpan di folder yang bisa diakses langsung dari web
2. Tidak perlu symbolic link
3. Bekerja di semua jenis hosting

---

## 🔧 Struktur Folder Upload

```
project/
├── public/
│   ├── storage/                 ← Folder utama untuk upload (BUAT MANUAL JIKA BELUM ADA)
│   │   ├── gallery/             ← Untuk galeri foto
│   │   ├── costumes/            ← Untuk foto kostum
│   │   ├── dance-services/      ← Untuk foto jasa tari
│   │   ├── makeup-services/     ← Untuk foto jasa rias
│   │   ├── profiles/            ← Untuk foto profil user
│   │   └── chat-images/         ← Untuk gambar di chat
│   ├── index.php
│   └── ...
└── ...
```

---

## 🚀 Langkah Implementasi

### 1️⃣ Buat Folder Upload di `public/storage/`

Di cPanel File Manager atau via FTP, buat folder-folder berikut:

```
public/storage/
public/storage/gallery/
public/storage/costumes/
public/storage/dance-services/
public/storage/makeup-services/
public/storage/profiles/
public/storage/chat-images/
```

**Set permission folder: `0755`**

### 2️⃣ Implementasi Controller untuk Upload

#### Template Code Upload Gambar (PHP/Laravel)

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExampleController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi file
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048' // max 2MB
        ]);

        // 2. Proses upload gambar
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            // 3. Generate nama file unik (mencegah overwrite)
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // 4. Set direktori upload (LANGSUNG KE PUBLIC)
            $uploadDir = public_path('storage/gallery'); // Ganti sesuai kebutuhan

            // 5. Buat folder jika belum ada
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // 6. Pindahkan file ke folder upload
            $file->move($uploadDir, $filename);

            // 7. Simpan path RELATIF untuk database
            $imagePath = 'gallery/' . $filename;
        }

        // 8. Simpan ke database
        Model::create([
            'title' => $validated['title'],
            'image_path' => $imagePath, // Simpan path relatif
        ]);

        return redirect()->back()->with('success', 'Berhasil upload!');
    }
}
```

### 3️⃣ Cara Update/Edit Gambar

```php
public function update(Request $request, Model $model)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
    ]);

    // Proses gambar jika ada file baru
    if ($request->hasFile('image')) {
        // 1. Hapus gambar lama dari public/storage
        if ($model->image_path) {
            $oldFilePath = public_path('storage/' . $model->image_path);
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }

        // 2. Upload gambar baru
        $file = $request->file('image');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        $uploadDir = public_path('storage/gallery');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $file->move($uploadDir, $filename);
        $validated['image_path'] = 'gallery/' . $filename;
    }

    $model->update($validated);

    return redirect()->back()->with('success', 'Berhasil update!');
}
```

### 4️⃣ Cara Hapus Gambar

```php
public function destroy(Model $model)
{
    // Hapus file dari storage
    if ($model->image_path) {
        $filePath = public_path('storage/' . $model->image_path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $model->delete();

    return redirect()->back()->with('success', 'Berhasil hapus!');
}
```

---

## 🌐 Cara Menampilkan Gambar

### Di Blade Template (Web)

```blade
{{-- Dari path relatif di database --}}
<img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->title }}">

{{-- Contoh dengan default image jika kosong --}}
@if($item->image_path)
    <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->title }}">
@else
    <img src="{{ asset('images/default.png') }}" alt="No image">
@endif
```

### Di API Response (untuk Mobile App)

```php
// Di Controller API
public function index()
{
    $items = Model::all()->map(function($item) {
        return [
            'id' => $item->id,
            'title' => $item->title,
            // Generate URL lengkap untuk mobile
            'image_url' => $item->image_path
                ? asset('storage/' . $item->image_path)
                : null,
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $items
    ]);
}
```

### Di Model (Accessor)

```php
// app/Models/Example.php
class Example extends Model
{
    // Accessor untuk auto-generate URL
    public function getImageUrlAttribute()
    {
        return $this->image_path
            ? asset('storage/' . $this->image_path)
            : null;
    }
}

// Penggunaan:
// $example->image_url → https://domain.com/storage/gallery/1234_abc.jpg
```

---

## 📱 Contoh Upload dari Flutter (Mobile App)

### Menggunakan Multipart Request

```dart
import 'package:http/http.dart' as http;

Future<void> uploadImage(File imageFile) async {
  var request = http.MultipartRequest(
    'POST',
    Uri.parse('https://yourdomain.com/api/upload'),
  );

  // Add headers (jika pakai auth)
  request.headers['Authorization'] = 'Bearer $token';

  // Add file
  request.files.add(
    await http.MultipartFile.fromPath('image', imageFile.path),
  );

  // Add other fields
  request.fields['title'] = 'Judul Gambar';

  var response = await request.send();

  if (response.statusCode == 200) {
    print('Upload berhasil!');
  }
}
```

---

## ⚙️ Konfigurasi Tambahan

### File Validation Best Practice

```php
$request->validate([
    'image' => [
        'required',
        'image',
        'mimes:jpeg,png,jpg,webp', // Format yang diizinkan
        'max:2048',                 // Max 2MB
        'dimensions:max_width=2000,max_height=2000', // Optional: max dimensi
    ],
]);
```

### Generate Nama File yang Aman

```php
// Metode 1: Timestamp + Unique ID (RECOMMENDED)
$filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

// Metode 2: Hash based
$filename = md5(time() . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();

// Metode 3: UUID
$filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
```

---

## 📊 Database Schema untuk Image

### Kolom yang Dibutuhkan

```sql
-- Untuk menyimpan path gambar
ALTER TABLE `your_table`
ADD COLUMN `image_path` VARCHAR(255) DEFAULT NULL;

-- ATAU jika support multiple images
CREATE TABLE `item_images` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `item_id` BIGINT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `image_name` VARCHAR(255) DEFAULT NULL,
    `image_size` BIGINT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`item_id`) REFERENCES `your_table`(`id`) ON DELETE CASCADE
);
```

---

## 🔒 Keamanan

### 1. Validasi Tipe File

```php
// Selalu validasi di backend, jangan percaya extension saja
$validated = $request->validate([
    'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
]);
```

### 2. Rename File yang Diupload

```php
// JANGAN gunakan nama asli file untuk mencegah overwrite & security issues
$filename = time() . '_' . uniqid() . '.' . $extension;
```

### 3. Set Permission yang Benar

```
Folder: 0755 (drwxr-xr-x)
File: 0644 (-rw-r--r--)
```

### 4. Batasi Ukuran File

```php
// Di controller
'image' => 'max:2048' // 2MB

// Di php.ini (hosting)
upload_max_filesize = 10M
post_max_size = 10M
```

---

## 📋 Checklist Implementasi

### Persiapan Server/Hosting

-   [ ] Buat folder `public/storage/`
-   [ ] Buat subfolder sesuai kebutuhan (gallery, costumes, dll)
-   [ ] Set permission folder ke `0755`
-   [ ] Pastikan `upload_max_filesize` di php.ini cukup besar

### Implementasi Code

-   [ ] Buat validasi file di controller
-   [ ] Gunakan `public_path('storage/...')` untuk direktori upload
-   [ ] Gunakan `$file->move()` untuk upload
-   [ ] Simpan path RELATIF ke database (bukan URL lengkap)
-   [ ] Handle delete old file saat update
-   [ ] Handle delete file saat destroy

### Testing

-   [ ] Test upload gambar baru
-   [ ] Test update gambar (ganti dengan yang baru)
-   [ ] Test hapus data (pastikan file terhapus)
-   [ ] Test tampilkan gambar di web
-   [ ] Test API response untuk mobile app

---

## 🔑 Poin Kunci yang Harus Diingat

| ❌ JANGAN                        | ✅ GUNAKAN                                |
| -------------------------------- | ----------------------------------------- |
| `Storage::disk('public')->put()` | `$file->move(public_path('storage/...'))` |
| `php artisan storage:link`       | Langsung ke `public/storage/`             |
| Simpan URL lengkap di database   | Simpan path relatif saja                  |
| Nama file asli                   | Generate nama file unik                   |
| Trust file extension             | Validasi dengan `mimes`                   |

---

## 📞 Referensi Controller di RANTS

Lihat contoh implementasi nyata di:

-   `app/Http/Controllers/Admin/GalleryController.php`
-   `app/Http/Controllers/Admin/CostumeController.php`
-   `app/Http/Controllers/Admin/DanceServiceController.php`
-   `app/Http/Controllers/Admin/MakeupServiceController.php`
-   `app/Http/Controllers/Api/ProfileController.php`

---

## 🎉 Kesimpulan

Sistem upload gambar untuk **hosting/production** di Laravel:

1. **Upload langsung ke `public/storage/`** menggunakan `$file->move()`
2. **Simpan path relatif** di database (contoh: `gallery/1234_abc.jpg`)
3. **Tampilkan dengan `asset('storage/' . $path)`**
4. **Jangan gunakan symbolic link** (`storage:link`) di shared hosting

Dengan pendekatan ini, sistem upload gambar akan **bekerja di semua jenis hosting** tanpa masalah!

---

**Last Updated:** 28 Desember 2025  
**Author:** Generated from RANTS System Analysis  
**Status:** ✅ Production Ready
