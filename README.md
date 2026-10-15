# RentalHub - Web App Rental Serbaguna End-to-End

Aplikasi web rental serbaguna (kamera, multimedia, perlengkapan outdoor, alat berat, dll.) yang dibangun dengan prinsip arsitektur monolith modern Laravel 13, Tailwind CSS v4, dan Alpine.js. Dirancang ramping, cepat, aman, dan mengotomatiskan seluruh siklus sewa mulai dari pencarian unit, pengecekan ketersediaan anti-double-booking, pembayaran transfer/QRIS, serah-terima unit (pickup), hingga pengembalian (return) dan penyelesaian deposit (deposit settlement).

---

## 🚀 Fitur Utama & Keunggulan

### 1. Pelanggan (Customer)
- **Katalog Publik & Pencarian**: Filter kategori, pencarian teks, dan filter tanggal "Tersedia pada rentang tanggal [Mulai - Selesai]".
- **Live Durasi & Harga (Alpine.js)**: Kalkulasi durasi sewa, total tarif sewa, diskon sewa mingguan (&ge; 7 hari), dan total deposit jaminan secara transparan dan instan.
- **Instruksi Pembayaran**: Petunjuk transfer bank manual & scan QRIS statis lengkap dengan form upload bukti bayar.
- **Visual Status Timeline**: Lacak perjalanan status pesanan secara visual (Dibuat &rarr; Terkonfirmasi &rarr; Sedang Disewa &rarr; Selesai).
- **Invoice Ramah Cetak**: Halaman faktur sewa siap cetak atau simpan ke PDF (`@media print`).
- **Integrasi WhatsApp**: Tombol pre-filled pesan WhatsApp langsung ke nomor admin toko.

### 2. Tim Operasional & Staff
- **Dashboard Operasional Harian**: Ringkasan aksi harian mencakup Jadwal Pickup Hari Ini, Jadwal Return Hari Ini, Unit Sedang Disewa, Peringatan Keterlambatan (Overdue), dan Bukti Bayar Menunggu Verifikasi.
- **Pemesanan Walk-in / Kasir**: Input pemesanan langsung di counter toko untuk pelanggan lama atau baru (cukup Nama & No. HP), opsi bayar tunai/QRIS langsung lunas, dan opsi langsung serah-terima unit (*direct pickup*).
- **Serah-Terima Unit (Pickup Checklist)**: Checklist dan pencatatan kondisi awal unit saat diserahkan ke pelanggan.
- **Pengembalian Unit & Settlement Deposit (Return)**:
  - Deteksi otomatis keterlambatan jam/hari pengembalian.
  - Perhitungan denda keterlambatan otomatis sesuai pengaturan tarif harian.
  - Form input biaya kerusakan unit (jika ada).
  - *Settlement Otomatis*: Jika deposit lebih besar dari total denda/kerusakan, sistem mencatat sisa pengembalian dana (*refund*). Jika biaya denda melebihi deposit, sistem mencatat tagihan tambahan (*additional fee*).
- **Kalender Jadwal Matriks (14 Hari)**: Tampilan visual jadwal sewa unit dan masa blokir perbaikan (*blackout*) dengan indikator status warna interaktif.
- **Manajemen Inventori**: CRUD kategori, CRUD unit sewa beserta multi-upload foto dan pengatur foto utama, serta jadwal blackout.

### 3. Administrator
- **Laporan & Analisis Pendapatan**:
  - Filter periode tanggal laporan.
  - Ringkasan pendapatan bersih: `Omset Sewa + Total Denda - Diskon`.
  - Peringkat unit terlaris berdasarkan frekuensi peminjaman dan pendapatan yang dihasilkan.
  - **Export CSV**: Unduh data laporan ke format spreadsheet CSV dengan encoding UTF-8 BOM.
- **Pengaturan Sistem (Settings)**:
  - Profil toko (Nama, No WhatsApp, Alamat).
  - Rekening bank dan upload gambar QRIS statis.
  - Aturan bisnis: Batas waktu bayar (*expires hours*), persentase denda keterlambatan per hari, persentase refund pembatalan, dan persentase diskon mingguan.
- **Manajemen Staff**: Tambah, perbarui, dan kelola peran akun tim operasional dan administrator.

---

## 🛡️ Arsitektur & Aturan Bisnis

1. **Anti Double-Booking & Sweep-Line Algorithm**:
   - Pengecekan ketersediaan unit menggunakan algoritma *sweep-line* di PHP pada event point `start_at` dan `end_at`, sehingga pesanan non-kontemporer tidak dihitung sebagai tumpang-tindih.
   - Saat pemesanan dibuat, transaksi dibungkus dalam `DB::transaction()` dengan pessimistic locking `Item::whereIn(...)->orderBy('id')->lockForUpdate()` untuk mencegah *race condition* dan *deadlock*.
2. **State Machine Terpusat**:
   - Seluruh perubahan status sewa dikontrol melalui `TransitionBookingStatusAction` yang memvalidasi transisi legal dan otomatis mencatat ke tabel audit `booking_status_logs`.
   - Perubahan status otomatis memberitahukan pengguna melalui notifikasi antrean `BookingStatusChangedNotification`.
3. **Otomasi Scheduler (`rental:process-schedules`)**:
   - Otomatis mengubah booking `pending` yang melewati `expires_at` tanpa bukti bayar menjadi `expired` agar stok kembali tersedia.
   - Deteksi pesanan terlambat (*overdue*) dan pengiriman pengingat 1x per hari.
   - Notifikasi pengingat H-1 sebelum jadwal pickup dan H-1 sebelum jadwal return.
4. **Keamanan & Hardening**:
   - `Model::preventLazyLoading` aktif di non-produksi untuk mencegah masalah N+1.
   - Rate limiting throttle pada endpoint login (`5 per menit per IP`) dan pembuatan booking (`10 per menit`).
   - Validasi ketat tipe MIME dan ukuran file upload bukti bayar serta gambar unit.
   - Akses kontrol berbasis otorisasi Policy dan Middleware peran (`EnsureRole`).

---

## 🔑 Akun Demo (Seeder)

Seluruh password akun demo adalah: `password`

| Peran (Role) | Nomor HP / Login | Email | Keterangan |
| :--- | :--- | :--- | :--- |
| **Administrator** | `081200000001` | `admin@rentalhub.id` | Akses penuh seluruh fitur, laporan CSV, settings, & staff |
| **Staff Operasional** | `081200000002` | `staff@rentalhub.id` | Manajemen pesanan, pickup, return, walk-in, kalender |
| **Pelanggan 1** | `081200000003` | `budi@customer.id` | Pelanggan terdaftar dengan contoh riwayat sewa |
| **Pelanggan 2** | `081200000004` | `siti@customer.id` | Pelanggan terdaftar dengan contoh pesanan aktif |

---

## ⚙️ Panduan Menjalankan Perintah Artisan

Perintah dieksekusi di dalam container aplikasi Adanix (`rental-app-1`):

```bash
# 1. Jalankan migrasi dan isi data demo realistis
docker exec rental-app-1 php artisan migrate:fresh --seed

# 2. Hubungkan symlink storage publik
docker exec rental-app-1 php artisan storage:link

# 3. Jalankan automated test suite (54 test kasus, 193 assertion)
docker exec rental-app-1 php artisan test

# 4. Jalankan perintah scheduler rental
docker exec rental-app-1 php artisan rental:process-schedules

# 5. Kompilasi asset frontend (Tailwind CSS v4 & Alpine.js)
/opt/adanix/bin/adanix build rental
```

---

## 📂 Struktur Proyek

```
app/
├── Actions/
│   ├── CreateBookingAction.php          # Atomic reservation & lock
│   └── TransitionBookingStatusAction.php # State machine & audit log
├── Console/Commands/
│   └── ProcessRentalSchedules.php       # Expire pending, overdue, H-1 reminder
├── Contracts/
│   └── PaymentGatewayInterface.php      # Abstraksi gateway pembayaran
├── Enums/                               # PHP Backed Enums untuk semua status
├── Helpers/helpers.php                  # format_rupiah, format_date_id
├── Http/Controllers/
│   ├── Admin/                           # Dashboard, Bookings, Calendar, Reports, Settings, Staff
│   ├── Auth/                            # Login, Register, Logout
│   ├── BookingController.php            # Alur pesanan pelanggan
│   └── CatalogController.php            # Katalog publik & live check
├── Models/                              # Eloquent models & relasi
├── Notifications/                       # Notifikasi status & reminder
└── Services/
    ├── AvailabilityService.php          # Sweep-line ketersediaan stok
    ├── ImageResizeService.php           # GD image resize & webp fallback
    ├── ManualPaymentGateway.php         # Implementasi gateway transfer/QRIS/cash
    └── PricingService.php               # Durasi, diskon, & kalkulasi harga
```
