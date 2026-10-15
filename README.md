# 🌟 RentalHub Nusantara — Modern End-to-End Rental Web Application

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white)](https://alpinejs.dev)
[![Tests](https://img.shields.io/badge/Tests-55%20Passed%20(196%20assertions)-success?style=for-the-badge&logo=checkmarx&logoColor=white)](#-pengujian-otomatis-automated-testing)

Aplikasi web rental serbaguna (*multi-purpose equipment rental*) end-to-end yang dibangun menggunakan **Laravel 12**, **Tailwind CSS v4**, dan **Alpine.js**. Dirancang ramping, tangguh, aman, dan tanpa ketergantungan library eksternal yang membengkak (*Zero External Packages*).

Aplikasi ini mengotomatiskan seluruh siklus bisnis penyewaan (kamera, multimedia, outdoor camping, proyektor, peralatan medis, dll.): mulai dari pencarian katalog publik, pencegahan *double-booking* berbasis algoritma matematika *sweep-line*, pemesanan walk-in kasir counter, verifikasi pembayaran multi-metode (Transfer, QRIS, Tunai), serah-terima unit (*pickup*), hingga pengembalian (*return*) beserta kalkulasi otomatis denda keterlambatan dan penyelesaian deposit (*settlement*).

---

## 🎯 Fitur & Fungsionalitas Utama

### 🛒 1. Portal Pelanggan (Customer Facing)
- **Katalog Publik Cerdas**:
  - Filter kategori instan, pencarian kata kunci, dan filter ketersediaan rentang tanggal (`[Mulai - Selesai]`).
  - Proteksi tanggal: peringatan langsung jika tanggal selesai mendahului tanggal mulai.
- **Halaman Detail Unit**:
  - Galeri multi-foto, spesifikasi teknis dinamis (JSON attributes), indikator sisa stok fisik, dan widget cek ketersediaan live via AJAX.
- **Formulir Pemesanan Modern (Searchable Modal Picker)**:
  - Pemilih unit interaktif dengan dialog modal pencarian instan + filter pil kategori (*Zero dependency Alpine.js*).
  - Kartu konfirmasi unit terpilih dengan foto asli, rincian tarif per hari, deposit jaminan, dan badge stok.
  - Stepper kuantitas dengan *auto-clamp* agar tidak pernah melebihi stok fisik yang tersedia.
  - Perhitungan live durasi sewa, total sewa, diskon sewa mingguan (&ge; 7 hari otomatis diskon 15%), dan deposit jaminan transparan sebelum submit.
- **Pembayaran & Verifikasi Mandiri**:
  - Rekening transfer bank & tampilan QRIS statis toko.
  - Form unggah bukti transfer dengan **pratinjau gambar instan** dan pengecekan ukuran file.
- **Dashboard "Pesanan Saya"**:
  - Visual timeline status pesanan legal (`Pending` &rarr; `Confirmed` &rarr; `Picked Up` &rarr; `Returned`).
  - Tombol pembatalan mandiri sebelum jadwal ambil (otomatis mencatat refund deposit & biaya sewa).
  - Faktur sewa ramah cetak (**Invoice `@media print`**).
  - Tombol WhatsApp terhubung langsung ke admin dengan teks pesan terisi otomatis (*pre-filled*).

---

### 🏪 2. Kasir Counter & Tim Operasional (Staff Facing)
- **Dashboard Operasional Real-Time**:
  - Ringkasan aksi hari ini: Jadwal Ambil Hari Ini, Jadwal Kembali Hari Ini, Unit Sedang Disewa, Peringatan Terlambat (*Overdue*), dan Bukti Bayar Menunggu Verifikasi.
- **Pemesanan Walk-in (Counter)**:
  - Form kasir super cepat untuk melayani pelanggan datang langsung di tempat.
  - Cukup masukkan Nama + Nomor WhatsApp (jika nomor sudah pernah sewa, otomatis dikaitkan ke akun lama).
  - Searchable Modal Picker untuk memilih barang dengan visual foto produk, mencegah kesalahan kasir saat unit peralatan serupa (mis. Sony A7 IV vs A7S III).
  - Opsi bayar langsung lunas (Tunai/QRIS/Transfer) dan opsi serah-terima langsung di tempat (*direct pickup*).
- **Proses Serah-Terima (Pickup Checklist)**:
  - Form verifikasi kondisi fisik awal dan kelengkapan unit sebelum diserahkan ke pelanggan.
- **Proses Pengembalian & Kalkulator Settlement (Return)**:
  - Deteksi otomatis keterlambatan jam/hari pengembalian.
  - Perhitungan denda telat otomatis sesuai persentase pengaturan toko.
  - Input biaya kerusakan/kehilangan jika ada.
  - *Settlement Otomatis*:
    - Jika deposit > total potongan &rarr; selisih dicatat sebagai `refund` (dikembalikan ke pelanggan).
    - Jika total potongan > deposit &rarr; selisih dicatat sebagai `additional_fee` (kurang bayar ditagihkan ke pelanggan).
- **Kalender Matriks Ketersediaan (14 Hari)**:
  - Matriks visual ketersediaan unit dan jadwal sewa selama 2 minggu.
  - Pencarian unit instan *client-side* untuk menyaring baris tabel seketika tanpa reload halaman.
- **Manajemen Inventori & Blackout**:
  - CRUD Kategori dan CRUD Unit dengan upload multi-foto.
  - Form input tarif sewa dan deposit dilengkapi badge visual format Rupiah resmi (*anti-typo* kelebihan nol).
  - Modal Tambah Blackout (jadwal servis / pemblokiran unit) dengan pencarian unit dan proteksi rentang tanggal.

---

### 📊 3. Administrator Portal (Admin Facing)
- **Laporan & Analisis Finansial**:
  - Filter periode tanggal laporan fleksibel.
  - Ringkasan pendapatan bersih: `Omset Sewa + Total Denda - Diskon`.
  - Peringkat unit paling laris dipinjam dan penyumbang omset tertinggi.
  - **Export CSV**: Ekspor laporan dalam format CSV berstandar UTF-8 BOM yang langsung rapi dibuka di Microsoft Excel.
- **Pengaturan Bisnis Dinamis (`settings`)**:
  - Profil toko (Nama toko, nomor WhatsApp resmi, alamat).
  - Rekening bank toko & upload gambar QRIS statis dengan live preview.
  - Parameter bisnis: Batas waktu bayar (*expires hours*), persentase denda keterlambatan per hari, persentase refund pembatalan, dan persentase diskon mingguan.
- **Manajemen Akun Staff**: Tambah, edit, dan kelola akun tim kasir operasional dan administrator.

---

## 🏗️ Prinsip Arsitektur & Aturan Bisnis

### 1. Anti Double-Booking & Sweep-Line Algorithm
Ketersediaan barang dihitung menggunakan algoritma **Sweep-Line** di PHP (`AvailabilityService`):
- Seluruh booking aktif (`confirmed`, `picked_up`, serta `pending` dengan bukti bayar) dievaluasi pada titik waktu perubahan (*event points* `start_at` & `end_at`).
- Menjamin dua pesanan yang tidak tumpang-tindih (misal: booking Senin-Selasa dan Kamis-Jumat) tidak dijumlahkan sebagai bentrok pada pencarian Senin-Jumat.
- Saat pemesanan dibuat (`CreateBookingAction`), database melakukan transaksi terkunci (`lockForUpdate()`) dengan urutan ID unit untuk mencegah *race condition* dan *deadlock*.

### 2. State Machine & Audit Trail Terpusat
- Seluruh perubahan status sewa wajib melewati `TransitionBookingStatusAction`.
- Memvalidasi seluruh transisi legal:
  - `pending` &rarr; `confirmed` &rarr; `picked_up` &rarr; `returned`
  - Jalur pembatalan/penolakan: `cancelled`, `rejected`, `expired`.
- Setiap transisi otomatis tercatat ke tabel audit `booking_status_logs` mencakup aktor pengubah, waktu, dan catatan alasan.
- Atribut `overdue` adalah status turunan / query scope (`status === picked_up && now() > end_at`), bukan kolom database redundan.

### 3. Otomasi Scheduler Console (`rental:process-schedules`)
Perintah konsol otomatis yang dijalankan secara terjadwal:
1. Mengubah booking `pending` yang melewati `expires_at` tanpa bukti bayar menjadi `expired` sehingga kuantitas unit langsung bebas kembali.
2. Mendeteksi sewa yang terlambat (*overdue*) dan mengirim notifikasi peringatan (maks. 1x per hari per pesanan).
3. Mengirimkan notifikasi pengingat H-1 sebelum jadwal ambil dan H-1 sebelum jadwal pengembalian.

### 4. Zero External Packages & Safe Image Resizing
- Menggunakan ekstensi native **PHP GD** (`ImageResizeService`) untuk me-resize dan mengonversi seluruh foto unit yang diunggah ke format WebP/JPG (maksimum lebar 1200px), sehingga server bebas dari konflik dependensi library pihak ketiga.

---

## 🗄️ Skema Basis Data Inti

| Tabel | Deskripsi & Kolom Utama |
| :--- | :--- |
| `users` | `name`, `email`, `phone`, `password`, `role` (`admin`, `staff`, `customer`) |
| `categories` | `name`, `slug`, `is_active` |
| `items` | `category_id`, `name`, `slug`, `description`, `price_per_day`, `deposit`, `stock`, `specs` (JSON), `status` (`active`, `maintenance`, `inactive`) |
| `item_images` | `item_id`, `path`, `sort_order`, `is_primary` |
| `blackouts` | `item_id`, `start_at`, `end_at`, `reason` |
| `bookings` | `code` (unik: `RNT-YYYYMMDD-XXXX`), `user_id`, `start_at`, `end_at`, `picked_up_at`, `returned_at`, `status`, `subtotal_rental`, `deposit_total`, `discount_amount`, `late_fee`, `damage_fee`, `grand_total`, `handover_checklist` (JSON), `expires_at` |
| `booking_items` | `booking_id`, `item_id`, `qty`, `unit_price`, `unit_deposit`, `subtotal` (snapshot harga transaksi) |
| `payments` | `booking_id`, `type` (`rental_and_deposit`, `additional_fee`, `refund`), `method` (`transfer`, `qris`, `cash`), `amount`, `proof_path`, `status` (`pending`, `verified`, `rejected`), `verified_by`, `verified_at`, `notes` |
| `booking_status_logs` | `booking_id`, `from_status`, `to_status`, `actor_id`, `reason`, `created_at` |
| `settings` | `key`, `value` (dengan caching otomatis dan invalidasi saat diperbarui) |

---

## 🔑 Kredensial Akun Demo (Seeder)

Password untuk semua akun demo adalah: **`password`**

| Peran (Role) | Nomor HP / Login | Email | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Administrator** | `081200000001` | `admin@rental.local` | Akses penuh seluruh sistem, laporan finansial, CSV, pengaturan toko, & akun staff |
| **Staff Kasir** | `081200000002` | `staff@rental.local` | Kasir walk-in, operasional pesanan, checklist serah-terima, pengembalian, & kalender |
| **Pelanggan 1** | `081200000003` | `budi@customer.local` | Akun pelanggan dengan riwayat sewa selesai & aktif |
| **Pelanggan 2** | `081200000004` | `siti@customer.local` | Akun pelanggan terdaftar |

> **Catatan UX**: Halaman Login dilengkapi tombol *Quick Demo Fill* untuk mengisi kredensial dengan 1 klik.

---

## 🚀 Panduan Instalasi & Menjalankan Proyek

### Prasyarat
- PHP &ge; 8.2 (dengan ekstensi `pdo`, `sqlite`/`mysql`, `gd`, `intl`, `mbstring`)
- Composer &ge; 2.x
- Node.js &ge; 20.x & npm

### Langkah Instalasi Mandiri (Local Environment)

```bash
# 1. Clone repositori
git clone git@github.com:Adanix/rental-laravel_13.git
cd rental-laravel_13

# 2. Install dependensi PHP
composer install

# 3. Setup file konfigurasi environment
cp .env.example .env
php artisan key:generate

# 4. Jalankan migrasi basis data dan seeder demo
php artisan migrate:fresh --seed

# 5. Hubungkan symlink storage publik
php artisan storage:link

# 6. Install dependensi frontend dan kompilasi asset
npm install
npm run build

# 7. Jalankan server lokal
php artisan serve
```

### Menjalankan di Lingkungan Adanix / Docker

```bash
# Migrasi dan seeding data demo
docker exec rental-app-1 php artisan migrate:fresh --seed

# Symlink storage publik
docker exec rental-app-1 php artisan storage:link

# Kompilasi frontend assets
/opt/adanix/bin/adanix build rental

# Jalankan scheduler berkala
docker exec rental-app-1 php artisan rental:process-schedules
```

---

## 🧪 Pengujian Otomatis (Automated Testing)

Proyek ini dilengkapi rangkaian pengujian unit dan fitur komprehensif yang mencakup seluruh aturan bisnis, ketersediaan sweep-line, kalkulasi denda, otorisasi, dan mitigasi celah keamanan:

```bash
php artisan test
```

**Hasil Pengujian:**
```text
   PASS  Tests\Unit\AvailabilityServiceTest
  ✓ available stock returns full stock when no bookings
  ✓ inactive item returns zero availability
  ✓ blackout returns zero availability
  ✓ sweep line distinguishes sequential bookings from concurrent
  ✓ concurrent bookings properly deplete stock
  ✓ expired pending booking does not block stock
  ✓ pending booking with pending payment proof blocks stock

   PASS  Tests\Unit\PricingServiceTest
  ✓ duration days calculation
  ✓ pricing calculation standard
  ✓ long rental weekly discount applied

   PASS  Tests\Feature\F0FoundationTest
  ✓ helpers format rupiah and date
  ✓ settings get and set with cache
  ✓ models and enums work properly
  ✓ login screen can be rendered
  ✓ user can login with phone
  ✓ user can login with email
  ✓ user can register
  ✓ role middleware blocks unauthorized access

   PASS  Tests\Feature\F1CatalogAndInventoryTest
  ✓ public catalog page renders successfully
  ✓ public catalog can filter by category and search
  ✓ public catalog filters by availability date range
  ✓ item detail page renders successfully
  ✓ check availability endpoint returns json
  ✓ staff can view and manage categories
  ✓ staff can create and update items
  ✓ staff can manage blackouts

   PASS  Tests\Feature\F2BookingCoreTest
  ✓ customer can view booking form
  ✓ customer can create booking successfully
  ✓ anti double booking rejects insufficient stock
  ✓ transition booking status action validates transitions
  ✓ customer can cancel pending booking
  ✓ customer cancelling confirmed booking generates refund
  ✓ customer cannot cancel booking after start at
  ✓ customer can view invoice page
  ✓ customer can view my bookings list without lazy loading violation

   PASS  Tests\Feature\F3OperationalAndRentalCycleTest
  ✓ customer can upload payment proof
  ✓ staff can view bookings list and details
  ✓ staff verifies payment and booking becomes confirmed
  ✓ staff rejects payment and booking becomes rejected
  ✓ staff performs pickup handover
  ✓ staff performs return with deposit refund
  ✓ staff performs return with late fee and damage fee exceeding deposit
  ✓ walk in booking creates confirmed or picked up booking
  ✓ process rental schedules command expires unpaid bookings and notifications

   PASS  Tests\Feature\F4DashboardAndReportsTest
  ✓ dashboard renders metrics for staff and admin
  ✓ calendar matrix renders for staff
  ✓ admin can view reports and metrics
  ✓ admin can export csv report
  ✓ admin can update settings
  ✓ admin can manage staff accounts

   PASS  Tests\Feature\F5HardeningAndSecurityTest
  ✓ login rate limiting triggers too many requests
  ✓ customer cannot view or cancel another customer booking
  ✓ payment proof upload validates mime and size

  Tests:    55 passed (196 assertions)
```

---

## 📂 Struktur Direktori Utama

```text
app/
├── Actions/
│   ├── CreateBookingAction.php          # Atomic pessimistic reservation
│   └── TransitionBookingStatusAction.php # State machine & audit logger
├── Console/Commands/
│   └── ProcessRentalSchedules.php       # Expire unpaid, overdue alerts, H-1 reminders
├── Contracts/
│   └── PaymentGatewayInterface.php      # Abstraksi gateway pembayaran modular
├── Enums/                               # PHP Backed Enums untuk seluruh status
├── Helpers/helpers.php                  # Helper format_rupiah & format_date_id
├── Http/Controllers/
│   ├── Admin/                           # Dashboard, Booking, Calendar, Reports, Settings, Staff
│   ├── Auth/                            # Login (Phone/Email), Register, Logout
│   ├── BookingController.php            # Alur pesanan pelanggan
│   └── CatalogController.php            # Katalog publik & live availability
├── Models/                              # Eloquent Models & relasi
├── Notifications/                       # Database queue notification
└── Services/
    ├── AvailabilityService.php          # Sweep-line concurrency availability
    ├── ImageResizeService.php           # GD image processor & WebP auto-resizer
    ├── ManualPaymentGateway.php         # Gateway manual (Transfer/QRIS/Tunai)
    └── PricingService.php               # Durasi, diskon mingguan, & settlement deposit
```

---

## 📄 Lisensi

Proyek ini dirilis di bawah lisensi terbuka [MIT License](LICENSE).
