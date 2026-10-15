# Master Plan & Arsitektur: Rental Web App (Adanix)

## 1. Ringkasan Skema Database & Relasi (10 Tabel Inti)
- `users`: id, name, email (nullable, unique), phone (unique), password (nullable), role (`admin`|`staff`|`customer`), timestamps.
- `categories`: id, name, slug (unique), is_active (bool), timestamps.
- `items`: id, category_id (FK categories), name, slug (unique), description (text), price_per_day (int), deposit (int), stock (int), specs (JSON nullable), status (`active`|`maintenance`|`inactive`), timestamps.
- `item_images`: id, item_id (FK items), path, sort_order (int), is_primary (bool), timestamps.
- `blackouts`: id, item_id (FK items), start_at, end_at, reason (nullable), timestamps.
  *Index*: `(item_id, start_at, end_at)`.
- `bookings`: id, code (unique, e.g. `RNT-YYYYMMDD-XXXX`), user_id (FK users), start_at, end_at, picked_up_at (nullable), returned_at (nullable), status (`pending`|`confirmed`|`picked_up`|`returned`|`cancelled`|`rejected`|`expired`), subtotal_rental (int), deposit_total (int), discount_amount (int), late_fee (int), damage_fee (int), grand_total (int), handover_checklist (JSON nullable), expires_at (nullable), timestamps.
  *Index*: `(status, start_at, end_at)`, `(expires_at)`. Scope/Atribut: `is_overdue` (`picked_up` && `now > end_at`).
- `booking_items`: id, booking_id (FK bookings), item_id (FK items), qty (int), unit_price (int snapshot), unit_deposit (int snapshot), subtotal (int), timestamps.
  *Index*: `(item_id, booking_id)`.
- `payments`: id, booking_id (FK bookings), type (`rental_and_deposit`|`additional_fee`|`refund`), method (`transfer`|`qris`|`cash`), amount (int), proof_path (nullable), status (`pending`|`verified`|`rejected`), verified_by (FK users nullable), verified_at (nullable), notes (text nullable), timestamps.
- `booking_status_logs`: id, booking_id (FK bookings), from_status (nullable), to_status, actor_id (FK users nullable), reason (text nullable), created_at.
- `settings`: key (string PK), value (text/JSON), cached & invalidated on update.

## 2. Aturan Bisnis & Logika Kunci
- **Ketersediaan**: Sweep-line algorithm di PHP atas interval aktif (`confirmed`, `picked_up`, dan `pending` valid) + blackout period. Ketersediaan = `stock - peak_concurrent_qty`.
- **Anti Double-Booking**: `DB::transaction()` + `Item::whereIn(...)->orderBy('id')->lockForUpdate()`.
- **Harga & Durasi**: Hari sewa = `max(1, ceil(durasi_jam / 24))`. Diskon mingguan jika hari >= setting.
- **State Machine**: Satu pintu lewat `TransitionBookingStatusAction` dengan log audit otomatis.
- **Serah Terima & Denda**: Terlambat dihitung hari per 24 jam x denda harian. Deposit dipotong denda + kerusakan; sisa dicatat otomatis sebagai payment `refund` (pending) atau `additional_fee` jika minus.

## 3. Daftar Route & Halaman Utama
- **Publik**:
  - `GET /`: Landing page & katalog unit (filter kategori, harga, tanggal sewa).
  - `GET /items/{slug}`: Detail unit, spesifikasi, galeri foto, live checker ketersediaan.
  - `GET /login`, `POST /login`, `GET /register`, `POST /register`, `POST /logout`.
- **Pelanggan (`auth`, role: customer|staff|admin)**:
  - `GET /bookings/create`: Form pemesanan unit dengan live calculation Alpine.js.
  - `POST /bookings`: Submit booking (DB transaction + pessimistic lock).
  - `GET /my-bookings`: Riwayat pesanan & visual status timeline.
  - `GET /my-bookings/{code}`: Detail pesanan & instruksi bayar.
  - `POST /my-bookings/{code}/payment`: Upload bukti pembayaran (Transfer/QRIS).
  - `POST /my-bookings/{code}/cancel`: Batalkan pesanan (kebijakan refund).
  - `GET /my-bookings/{code}/invoice`: Faktur ramah-cetak (`@media print`).
- **Staff / Admin Panel (`auth`, role: staff|admin, prefix `/admin`)**:
  - `GET /admin/dashboard`: Metrik operasional harian (pickup/return hari ini, overdue, pending bayar).
  - `GET /admin/bookings`: Daftar pesanan, filter status & tanggal.
  - `GET /admin/bookings/{id}`: Detail pesanan, riwayat log, bukti bayar.
  - `POST /admin/bookings/{id}/verify-payment`: Verifikasi atau tolak pembayaran.
  - `POST /admin/bookings/{id}/pickup`: Handover checklist & konfirmasi serah-terima.
  - `POST /admin/bookings/{id}/return`: Return checklist, kalkulasi denda telat/rusak, settlement deposit.
  - `GET /admin/bookings/walk-in`: Form buat pesanan manual / walk-in customer (pembayaran tunai langsung).
  - `POST /admin/bookings/walk-in`: Simpan pesanan walk-in.
  - `GET /admin/calendar`: Kalender visual ketersediaan unit & blackout.
  - CRUD: `/admin/categories`, `/admin/items` (+ multi image upload & auto-resize GD), `/admin/blackouts`.
- **Admin Eksklusif (`role: admin`)**:
  - `GET /admin/reports`: Laporan pendapatan bersih & unit terlaris + `GET /admin/reports/export` (CSV).
  - `GET /admin/settings`, `POST /admin/settings`: Pengaturan toko, rekening, QRIS, denda, refund.
  - CRUD `/admin/staff`: Kelola akun tim operasional.

## 4. Asumsi & Keputusan Teknis
- Standar Monolith Laravel 13, Tailwind CSS v4, Alpine.js, PHP Backed Enum.
- Format mata uang Rupiah (`Rp`), tanggal lokalisasi `id_ID` (Asia/Jakarta).
- Service kecil: `AvailabilityService`, `PricingService`, `ImageResizeService`, `ManualPaymentGateway`.
- Queue driver database + Scheduler Laravel (`rental:process-schedules`) untuk auto-expire & reminder.

## 5. Status Eksekusi Fase
- [x] F0: Fondasi (Migrasi, Enum, Model, Factory, Seeder Realistis, Auth & Layout Blade)
- [x] F1: Katalog & Inventori (AvailabilityService Sweep-line, CRUD Staff & Publik)
- [x] F2: Booking Core (PricingService, CreateBookingAction, Live Alpine Form, My Bookings)
- [ ] F3: Operasional & Siklus Sewa (Payment Gateway, Pickup, Return & Deposit Settlement, Walk-in, Invoice, Scheduler)
- [ ] F4: Dashboard, Kalender, Laporan & Pengaturan (Operational Dashboard, Visual Calendar, CSV Report, Settings, Staff CRUD)
- [ ] F5: Hardening & Dokumentasi (PreventLazyLoading, Rate Limiting, MIME Validation, Responsive Audit, README)
