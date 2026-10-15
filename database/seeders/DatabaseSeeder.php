<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\ItemStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\UserRole;
use App\Models\Blackout;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\BookingStatusLog;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Settings
        $settings = [
            'store_name' => 'RentalHub Nusantara',
            'store_phone' => '081234567890',
            'store_address' => 'Jl. Thamrin No. 88, Menteng, Jakarta Pusat',
            'bank_name' => 'BCA',
            'bank_account_number' => '8830123456',
            'bank_account_holder' => 'PT RentalHub Nusantara',
            'qris_image' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=RENTALHUB-QRIS-STATIC-DEMO',
            'payment_expiry_hours' => 2,
            'late_fee_percent_per_day' => 100,
            'cancellation_refund_percent' => 80,
            'weekly_discount_percent' => 15,
            'weekly_discount_min_days' => 7,
        ];

        foreach ($settings as $key => $val) {
            Setting::set($key, $val);
        }

        // 2. Users (Admin, Staff, 2 Customers)
        $admin = User::firstOrCreate(['phone' => '081200000001'], [
            'name' => 'Administrator Rental',
            'email' => 'admin@rental.local',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
        ]);

        $staff = User::firstOrCreate(['phone' => '081200000002'], [
            'name' => 'Staff Operasional',
            'email' => 'staff@rental.local',
            'password' => Hash::make('password'),
            'role' => UserRole::STAFF,
        ]);

        $customer1 = User::firstOrCreate(['phone' => '081200000003'], [
            'name' => 'Budi Pratama',
            'email' => 'budi@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::CUSTOMER,
        ]);

        $customer2 = User::firstOrCreate(['phone' => '081200000004'], [
            'name' => 'Siti Rahma',
            'email' => 'siti@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::CUSTOMER,
        ]);

        // 3. Categories
        $categoriesData = [
            ['name' => 'Kamera & Lensa', 'slug' => 'kamera-lensa'],
            ['name' => 'Audio & Sound System', 'slug' => 'audio-sound-system'],
            ['name' => 'Alat Camping & Outdoor', 'slug' => 'alat-camping-outdoor'],
            ['name' => 'Elektronik & Proyektor', 'slug' => 'elektronik-proyektor'],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[$c['slug']] = Category::firstOrCreate(['slug' => $c['slug']], [
                'name' => $c['name'],
                'is_active' => true,
            ]);
        }

        // 4. Items (15 units)
        $itemsData = [
            // Kamera & Lensa
            [
                'category' => 'kamera-lensa',
                'name' => 'Sony Alpha A7 IV Body Only',
                'slug' => 'sony-alpha-a7-iv',
                'description' => 'Kamera mirrorless full-frame 33MP hybrid dengan kemampuan video 4K 60p 10-bit, autofokus super cepat, dan stabilisasi gambar 5-axis.',
                'price_per_day' => 350000,
                'deposit' => 300000,
                'stock' => 2,
                'specs' => ['Sensor' => '33MP Full-Frame Exmor R', 'Video' => '4K 60p 10-bit 4:2:2', 'Mount' => 'Sony E-mount', 'Kelengkapan' => 'Body, 2 Baterai FZ100, Charger, Tas'],
                'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=800&auto=format&fit=crop&q=80',
            ],
            [
                'category' => 'kamera-lensa',
                'name' => 'Canon EOS R6 Mark II Body',
                'slug' => 'canon-eos-r6-mark-ii',
                'description' => 'Kamera serbaguna berkecepatan tinggi hingga 40 fps, sensor 24.2MP full-frame, dan video 6K oversampled tanpa batas rekam.',
                'price_per_day' => 380000,
                'deposit' => 300000,
                'stock' => 1,
                'specs' => ['Sensor' => '24.2MP Full-Frame CMOS', 'Burst' => 'Up to 40 fps', 'Mount' => 'Canon RF', 'Kelengkapan' => 'Body, 2 Baterai LP-E6NH, Dual Charger, Strap, Tas'],
                'image' => 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=800&auto=format&fit=crop&q=80',
            ],
            [
                'category' => 'kamera-lensa',
                'name' => 'Fujifilm X-T5 Mirrorless Body',
                'slug' => 'fujifilm-x-t5',
                'description' => 'Desain retro klasik dengan sensor 40.2MP X-Trans CMOS 5 HR, film simulation legendaris, dan bobot ringkas untuk street photography.',
                'price_per_day' => 250000,
                'deposit' => 200000,
                'stock' => 2,
                'specs' => ['Sensor' => '40.2MP APS-C X-Trans', 'Stabilisasi' => '7-stop IBIS', 'Kelengkapan' => 'Body, 2 Baterai NP-W235, Tas Kamera'],
                'image' => 'https://images.unsplash.com/photo-1512790182412-b19e6d62bc39?w=800&auto=format&fit=crop&q=80',
            ],
            [
                'category' => 'kamera-lensa',
                'name' => 'Lensa Sony FE 24-70mm f/2.8 GM II',
                'slug' => 'sony-fe-24-70mm-gm-ii',
                'description' => 'Lensa zoom standar G Master generasi kedua yang lebih tajam, lebih ringan 22%, dan autofokus linear XD quad.',
                'price_per_day' => 180000,
                'deposit' => 150000,
                'stock' => 3,
                'specs' => ['Focal Length' => '24-70mm', 'Aperture' => 'f/2.8 constant', 'Filter Thread' => '82mm', 'Kelengkapan' => 'Lensa, Hood, Tutup Depan/Belakang, Pouch'],
                'image' => 'https://images.unsplash.com/photo-1617005082133-548c4dd27f35?w=800&auto=format&fit=crop&q=80',
            ],
            [
                'category' => 'kamera-lensa',
                'name' => 'Stabilizer Gimbal DJI RS 3 Pro',
                'slug' => 'dji-rs-3-pro-combo',
                'description' => 'Gimbal profesional berbahan serat karbon dengan motor focus, transmitter image RavenEye, dan payload hingga 4.5kg.',
                'price_per_day' => 175000,
                'deposit' => 150000,
                'stock' => 2,
                'specs' => ['Payload' => '4.5 kg', 'Bahan' => 'Carbon Fiber', 'Kelengkapan' => 'Gimbal, Focus Motor, Briefcase Handle, Hardcase'],
                'image' => 'https://images.unsplash.com/photo-1589872583859-9b489953b6d9?w=800&auto=format&fit=crop&q=80',
            ],

            // Audio & Sound
            [
                'category' => 'audio-sound-system',
                'name' => 'Mic Wireless Hollyland Lark Max Duo',
                'slug' => 'hollyland-lark-max-duo',
                'description' => 'Sistem mikrofon wireless 2 transmitter dengan teknologi MaxTimbre Mic, On-board recording 8GB, dan Environmental Noise Cancellation.',
                'price_per_day' => 100000,
                'deposit' => 100000,
                'stock' => 4,
                'specs' => ['Jarak' => 'Hingga 250m', 'Internal Memory' => '8GB (14 Jam)', 'Kelengkapan' => '2 TX, 1 RX, Charging Case, Kabel Audio, Deadcat'],
                'image' => 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?w=800&auto=format&fit=crop&q=80',
            ],
            [
                'category' => 'audio-sound-system',
                'name' => 'Speaker Portable JBL EON ONE Compact',
                'slug' => 'jbl-eon-one-compact',
                'description' => 'Speaker PA portabel all-in-one bertenaga baterai (tahan hingga 12 jam) dengan mixer 4 kanal dan efek reverb & ducking terintegrasi.',
                'price_per_day' => 200000,
                'deposit' => 200000,
                'stock' => 2,
                'specs' => ['Output' => '112 dB peak SPL', 'Baterai' => 'Tahan 12 jam', 'Konektivitas' => 'Bluetooth + XLR/TRS', 'Kelengkapan' => 'Unit Speaker, Kabel Power, Kabel Aux'],
                'image' => 'https://images.unsplash.com/photo-1545454675-3531b543be5d?w=800&auto=format&fit=crop&q=80',
            ],
            [
                'category' => 'audio-sound-system',
                'name' => 'Mixer Audio Yamaha MG10XU 10-Channel',
                'slug' => 'yamaha-mg10xu-mixer',
                'description' => 'Mixer analog 10 kanal dengan pre-amp D-PRE diskrit kelas-A, prosesor efek SPX digital 24 program, dan audio interface USB 2.0.',
                'price_per_day' => 120000,
                'deposit' => 100000,
                'stock' => 2,
                'specs' => ['Kanal' => '10 Channel (4 Mono + 3 Stereo)', 'Efek' => 'SPX 24 program', 'USB Audio' => '2-in / 2-out 24-bit/192kHz', 'Kelengkapan' => 'Mixer, Adaptor Power Original, Tas'],
                'image' => 'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?w=800&auto=format&fit=crop&q=80',
            ],

            // Alat Camping & Outdoor
            [
                'category' => 'alat-camping-outdoor',
                'name' => 'Tenda Dome Naturehike Cloud Up 3 Pro',
                'slug' => 'naturehike-cloud-up-3',
                'description' => 'Tenda ultralight kapasitas 3 orang dengan frame aluminium 7001 dan material silikon nilon 20D tahan hujan badai (PU 4000mm).',
                'price_per_day' => 65000,
                'deposit' => 50000,
                'stock' => 5,
                'specs' => ['Kapasitas' => '3 Orang Dewasa', 'Waterproof' => 'PU 4000mm', 'Berat' => '2.4 kg', 'Kelengkapan' => 'Tenda Luar/Dalam, Frame Aluminium, Pasak, Footprint'],
                'image' => 'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?w=800&auto=format&fit=crop&q=80',
            ],
            [
                'category' => 'alat-camping-outdoor',
                'name' => 'Carrier Tas Gunung Eiger Rhinos 60L',
                'slug' => 'eiger-rhinos-60l',
                'description' => 'Tas ransel carrier kapasitas besar 60 liter dengan teknologi Tropic Air Flow Backsystem yang kokoh dan nyaman untuk pendakian panjang.',
                'price_per_day' => 45000,
                'deposit' => 50000,
                'stock' => 4,
                'specs' => ['Kapasitas' => '60 Liter', 'Bahan' => 'Nylon Cordura', 'Fitur' => 'Rain Cover Include, Backsystem Ergonomis'],
                'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800&auto=format&fit=crop&q=80',
            ],
            [
                'category' => 'alat-camping-outdoor',
                'name' => 'Sleeping Bag Bulu Angsa Consina Goose Down',
                'slug' => 'consina-sleeping-bag-down',
                'description' => 'Kantong tidur hangat isi goose down asli untuk suhu dingin ekstrem 0 hingga 5 derajat Celcius di puncak gunung.',
                'price_per_day' => 25000,
                'deposit' => 25000,
                'stock' => 6,
                'specs' => ['Limit Suhu' => '0°C s/d 5°C', 'Berat' => '800 gram', 'Bahan' => 'Goose Down + Ripstop Nylon'],
                'image' => 'https://images.unsplash.com/photo-1510312305653-8ed496efae75?w=800&auto=format&fit=crop&q=80',
            ],
            [
                'category' => 'alat-camping-outdoor',
                'name' => 'Kompor Portable Camping Kovea + Nesting DS-308',
                'slug' => 'kompor-portable-nesting-set',
                'description' => 'Paket masak gunung komplit: kompor ultralight lipat piezo ignition plus nesting cookware 3 panci teflon anti lengket.',
                'price_per_day' => 30000,
                'deposit' => 25000,
                'stock' => 5,
                'specs' => ['Bahan Nesting' => 'Anodized Aluminium', 'Kompor' => 'Windproof Piezo Auto', 'Kelengkapan' => '3 Panci, 1 Wajan, Kompor, Tas Jaring'],
                'image' => 'https://images.unsplash.com/photo-1478131143081-80f7f84ca84d?w=800&auto=format&fit=crop&q=80',
            ],

            // Elektronik & Proyektor
            [
                'category' => 'elektronik-proyektor',
                'name' => 'Proyektor Epson EB-E500 3300 Lumens',
                'slug' => 'epson-eb-e500-proyektor',
                'description' => 'Proyektor 3LCD resolusi XGA dengan kecerahan tinggi 3.300 ANSI lumens, sangat jelas untuk presentasi siang hari, meeting, dan nobar.',
                'price_per_day' => 150000,
                'deposit' => 150000,
                'stock' => 3,
                'specs' => ['Kecerahan' => '3300 ANSI Lumens', 'Resolusi' => 'XGA (1024 x 768)', 'Input' => 'HDMI, VGA, USB', 'Kelengkapan' => 'Proyektor, Kabel HDMI 5m, Kabel Power, Remote, Tas'],
                'image' => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?w=800&auto=format&fit=crop&q=80',
            ],
            [
                'category' => 'elektronik-proyektor',
                'name' => 'Screen Layar Proyektor Tripod 70 Inch (178x178 cm)',
                'slug' => 'screen-tripod-70-inch',
                'description' => 'Layar proyektor portabel format matte white dengan kaki tripod besi kokoh, mudah dilipat dan dibawa ke lokasi acara.',
                'price_per_day' => 50000,
                'deposit' => 50000,
                'stock' => 3,
                'specs' => ['Ukuran' => '70 x 70 Inch (178 x 178 cm)', 'Model' => 'Tripod Stand Portable', 'Warna Kain' => 'Matte White High Contrast'],
                'image' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800&auto=format&fit=crop&q=80',
            ],
            [
                'category' => 'elektronik-proyektor',
                'name' => 'Drone DJI Mini 4 Pro Fly More Combo Plus',
                'slug' => 'dji-mini-4-pro-combo',
                'description' => 'Drone lipat di bawah 249g dengan sensor omnidirectional obstacle sensing, video 4K/60fps HDR vertikal asli, dan 3 baterai cerdas.',
                'price_per_day' => 300000,
                'deposit' => 300000,
                'stock' => 2,
                'specs' => ['Sensor' => '1/1.3-inch CMOS 48MP', 'Jangkauan' => 'O4 FHD hingga 20 km', 'Waktu Terbang' => 'Up to 34 min/baterai', 'Kelengkapan' => 'Drone, RC-2 Screen Controller, 3 Baterai, Hub, Tas'],
                'image' => 'https://images.unsplash.com/photo-1508614589041-895b88991e3e?w=800&auto=format&fit=crop&q=80',
            ],
        ];

        $createdItems = [];
        foreach ($itemsData as $it) {
            $cat = $categories[$it['category']];
            $item = Item::firstOrCreate(['slug' => $it['slug']], [
                'category_id' => $cat->id,
                'name' => $it['name'],
                'description' => $it['description'],
                'price_per_day' => $it['price_per_day'],
                'deposit' => $it['deposit'],
                'stock' => $it['stock'],
                'specs' => $it['specs'],
                'status' => ItemStatus::ACTIVE,
            ]);

            ItemImage::firstOrCreate(['item_id' => $item->id, 'path' => $it['image']], [
                'sort_order' => 0,
                'is_primary' => true,
            ]);

            $createdItems[$it['slug']] = $item;
        }

        // 5. Sample Blackout (untuk servis unit Drone DJI Mini 4 Pro)
        $droneItem = $createdItems['dji-mini-4-pro-combo'] ?? null;
        if ($droneItem) {
            Blackout::firstOrCreate([
                'item_id' => $droneItem->id,
                'start_at' => Carbon::now()->addDays(20)->startOfDay(),
                'end_at' => Carbon::now()->addDays(22)->endOfDay(),
            ], [
                'reason' => 'Kalibrasi kompas & pemeliharaan gimbal berkala',
            ]);
        }

        // 6. Sample Bookings in diverse statuses

        // Booking 1: PENDING (Budi booked Sony A7 IV, starts in 2 days)
        $a7Item = $createdItems['sony-alpha-a7-iv'];
        $b1 = Booking::firstOrCreate(['code' => 'RNT-' . date('Ymd') . '-0001'], [
            'user_id' => $customer1->id,
            'start_at' => Carbon::now()->addDays(2)->setHour(10)->setMinute(0),
            'end_at' => Carbon::now()->addDays(4)->setHour(10)->setMinute(0),
            'status' => BookingStatus::PENDING,
            'subtotal_rental' => $a7Item->price_per_day * 2, // 2 days
            'deposit_total' => $a7Item->deposit,
            'discount_amount' => 0,
            'late_fee' => 0,
            'damage_fee' => 0,
            'grand_total' => ($a7Item->price_per_day * 2) + $a7Item->deposit,
            'expires_at' => Carbon::now()->addHours(2),
        ]);
        BookingItem::firstOrCreate(['booking_id' => $b1->id, 'item_id' => $a7Item->id], [
            'qty' => 1,
            'unit_price' => $a7Item->price_per_day,
            'unit_deposit' => $a7Item->deposit,
            'subtotal' => $a7Item->price_per_day * 2,
        ]);
        BookingStatusLog::firstOrCreate(['booking_id' => $b1->id, 'to_status' => BookingStatus::PENDING->value], [
            'from_status' => null,
            'actor_id' => $customer1->id,
            'reason' => 'Pesanan baru dibuat oleh pelanggan',
            'created_at' => now(),
        ]);

        // Booking 2: CONFIRMED (Siti booked Epson Projector + Screen, starts tomorrow, paid & verified)
        $epsonItem = $createdItems['epson-eb-e500-proyektor'];
        $screenItem = $createdItems['screen-tripod-70-inch'];
        $days2 = 1;
        $subtotal2 = ($epsonItem->price_per_day * $days2) + ($screenItem->price_per_day * $days2);
        $deposit2 = $epsonItem->deposit + $screenItem->deposit;
        $b2 = Booking::firstOrCreate(['code' => 'RNT-' . date('Ymd') . '-0002'], [
            'user_id' => $customer2->id,
            'start_at' => Carbon::now()->addDay()->setHour(9)->setMinute(0),
            'end_at' => Carbon::now()->addDays(2)->setHour(9)->setMinute(0),
            'status' => BookingStatus::CONFIRMED,
            'subtotal_rental' => $subtotal2,
            'deposit_total' => $deposit2,
            'discount_amount' => 0,
            'late_fee' => 0,
            'damage_fee' => 0,
            'grand_total' => $subtotal2 + $deposit2,
            'expires_at' => null,
        ]);
        BookingItem::firstOrCreate(['booking_id' => $b2->id, 'item_id' => $epsonItem->id], [
            'qty' => 1,
            'unit_price' => $epsonItem->price_per_day,
            'unit_deposit' => $epsonItem->deposit,
            'subtotal' => $epsonItem->price_per_day * $days2,
        ]);
        BookingItem::firstOrCreate(['booking_id' => $b2->id, 'item_id' => $screenItem->id], [
            'qty' => 1,
            'unit_price' => $screenItem->price_per_day,
            'unit_deposit' => $screenItem->deposit,
            'subtotal' => $screenItem->price_per_day * $days2,
        ]);
        Payment::firstOrCreate(['booking_id' => $b2->id, 'type' => PaymentType::RENTAL_AND_DEPOSIT->value], [
            'method' => PaymentMethod::TRANSFER,
            'amount' => $subtotal2 + $deposit2,
            'proof_path' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=600&auto=format&fit=crop&q=80',
            'status' => PaymentStatus::VERIFIED,
            'verified_by' => $staff->id,
            'verified_at' => Carbon::now()->subHours(2),
            'notes' => 'Pembayaran lunas via BCA a/n Siti Rahma',
        ]);
        BookingStatusLog::firstOrCreate(['booking_id' => $b2->id, 'to_status' => BookingStatus::CONFIRMED->value], [
            'from_status' => BookingStatus::PENDING->value,
            'actor_id' => $staff->id,
            'reason' => 'Pembayaran diverifikasi oleh staff',
            'created_at' => Carbon::now()->subHours(2),
        ]);

        // Booking 3: PICKED_UP (Budi currently renting Hollyland Lark Max, within time)
        $micItem = $createdItems['hollyland-lark-max-duo'];
        $days3 = 3;
        $subtotal3 = $micItem->price_per_day * $days3;
        $deposit3 = $micItem->deposit;
        $b3 = Booking::firstOrCreate(['code' => 'RNT-' . date('Ymd') . '-0003'], [
            'user_id' => $customer1->id,
            'start_at' => Carbon::now()->subDay()->setHour(14)->setMinute(0),
            'end_at' => Carbon::now()->addDays(2)->setHour(14)->setMinute(0),
            'picked_up_at' => Carbon::now()->subDay()->setHour(14)->setMinute(15),
            'status' => BookingStatus::PICKED_UP,
            'subtotal_rental' => $subtotal3,
            'deposit_total' => $deposit3,
            'discount_amount' => 0,
            'late_fee' => 0,
            'damage_fee' => 0,
            'grand_total' => $subtotal3 + $deposit3,
            'handover_checklist' => [
                'pickup' => ['kondisi' => 'Mulus, lengkap dengan charging box & 2 kabel audio', 'cek_baterai' => '100% full'],
            ],
            'expires_at' => null,
        ]);
        BookingItem::firstOrCreate(['booking_id' => $b3->id, 'item_id' => $micItem->id], [
            'qty' => 1,
            'unit_price' => $micItem->price_per_day,
            'unit_deposit' => $micItem->deposit,
            'subtotal' => $subtotal3,
        ]);
        Payment::firstOrCreate(['booking_id' => $b3->id, 'type' => PaymentType::RENTAL_AND_DEPOSIT->value], [
            'method' => PaymentMethod::CASH,
            'amount' => $subtotal3 + $deposit3,
            'status' => PaymentStatus::VERIFIED,
            'verified_by' => $staff->id,
            'verified_at' => Carbon::now()->subDay()->setHour(14)->setMinute(10),
            'notes' => 'Bayar tunai di kasir saat serah-terima',
        ]);
        BookingStatusLog::firstOrCreate(['booking_id' => $b3->id, 'to_status' => BookingStatus::PICKED_UP->value], [
            'from_status' => BookingStatus::CONFIRMED->value,
            'actor_id' => $staff->id,
            'reason' => 'Unit diambil oleh pelanggan di counter',
            'created_at' => Carbon::now()->subDay()->setHour(14)->setMinute(15),
        ]);

        // Booking 4: OVERDUE (Siti picked up Drone DJI Mini 4 Pro, end_at was yesterday! Overdue scope active)
        $droneIt = $createdItems['dji-mini-4-pro-combo'];
        $days4 = 2;
        $subtotal4 = $droneIt->price_per_day * $days4;
        $deposit4 = $droneIt->deposit;
        $b4 = Booking::firstOrCreate(['code' => 'RNT-' . date('Ymd') . '-0004'], [
            'user_id' => $customer2->id,
            'start_at' => Carbon::now()->subDays(3)->setHour(10)->setMinute(0),
            'end_at' => Carbon::now()->subDay()->setHour(10)->setMinute(0), // Overdue!
            'picked_up_at' => Carbon::now()->subDays(3)->setHour(10)->setMinute(30),
            'status' => BookingStatus::PICKED_UP,
            'subtotal_rental' => $subtotal4,
            'deposit_total' => $deposit4,
            'discount_amount' => 0,
            'late_fee' => 0,
            'damage_fee' => 0,
            'grand_total' => $subtotal4 + $deposit4,
            'handover_checklist' => [
                'pickup' => ['kondisi' => 'Unit prima, 3 baterai, tas combo lengkap'],
            ],
            'expires_at' => null,
        ]);
        BookingItem::firstOrCreate(['booking_id' => $b4->id, 'item_id' => $droneIt->id], [
            'qty' => 1,
            'unit_price' => $droneIt->price_per_day,
            'unit_deposit' => $droneIt->deposit,
            'subtotal' => $subtotal4,
        ]);
        Payment::firstOrCreate(['booking_id' => $b4->id, 'type' => PaymentType::RENTAL_AND_DEPOSIT->value], [
            'method' => PaymentMethod::TRANSFER,
            'amount' => $subtotal4 + $deposit4,
            'status' => PaymentStatus::VERIFIED,
            'verified_by' => $staff->id,
            'verified_at' => Carbon::now()->subDays(3)->setHour(9)->setMinute(45),
            'notes' => 'Transfer lunas',
        ]);
        BookingStatusLog::firstOrCreate(['booking_id' => $b4->id, 'to_status' => BookingStatus::PICKED_UP->value], [
            'from_status' => BookingStatus::CONFIRMED->value,
            'actor_id' => $staff->id,
            'reason' => 'Unit diserahkan',
            'created_at' => Carbon::now()->subDays(3)->setHour(10)->setMinute(30),
        ]);

        // Booking 5: RETURNED (Budi completed renting Tenda Naturehike, deposit returned)
        $tendaItem = $createdItems['naturehike-cloud-up-3'];
        $days5 = 2;
        $subtotal5 = $tendaItem->price_per_day * $days5;
        $deposit5 = $tendaItem->deposit;
        $b5 = Booking::firstOrCreate(['code' => 'RNT-' . date('Ymd') . '-0005'], [
            'user_id' => $customer1->id,
            'start_at' => Carbon::now()->subDays(5)->setHour(8)->setMinute(0),
            'end_at' => Carbon::now()->subDays(3)->setHour(8)->setMinute(0),
            'picked_up_at' => Carbon::now()->subDays(5)->setHour(8)->setMinute(15),
            'returned_at' => Carbon::now()->subDays(3)->setHour(7)->setMinute(45),
            'status' => BookingStatus::RETURNED,
            'subtotal_rental' => $subtotal5,
            'deposit_total' => $deposit5,
            'discount_amount' => 0,
            'late_fee' => 0,
            'damage_fee' => 0,
            'grand_total' => $subtotal5 + $deposit5,
            'handover_checklist' => [
                'pickup' => ['kondisi' => 'Lengkap, bersih'],
                'return' => ['kondisi' => 'Kembali lengkap tanpa robek / basah', 'catatan' => 'Sangat rapi'],
            ],
            'expires_at' => null,
        ]);
        BookingItem::firstOrCreate(['booking_id' => $b5->id, 'item_id' => $tendaItem->id], [
            'qty' => 1,
            'unit_price' => $tendaItem->price_per_day,
            'unit_deposit' => $tendaItem->deposit,
            'subtotal' => $subtotal5,
        ]);
        Payment::firstOrCreate(['booking_id' => $b5->id, 'type' => PaymentType::RENTAL_AND_DEPOSIT->value], [
            'method' => PaymentMethod::QRIS,
            'amount' => $subtotal5 + $deposit5,
            'status' => PaymentStatus::VERIFIED,
            'verified_by' => $staff->id,
            'verified_at' => Carbon::now()->subDays(5)->setHour(8)->setMinute(0),
            'notes' => 'QRIS verified',
        ]);
        Payment::firstOrCreate(['booking_id' => $b5->id, 'type' => PaymentType::REFUND->value], [
            'method' => PaymentMethod::TRANSFER,
            'amount' => $deposit5,
            'status' => PaymentStatus::VERIFIED,
            'verified_by' => $staff->id,
            'verified_at' => Carbon::now()->subDays(3)->setHour(8)->setMinute(0),
            'notes' => 'Pengembalian deposit penuh via transfer',
        ]);
        BookingStatusLog::firstOrCreate(['booking_id' => $b5->id, 'to_status' => BookingStatus::RETURNED->value], [
            'from_status' => BookingStatus::PICKED_UP->value,
            'actor_id' => $staff->id,
            'reason' => 'Unit dikembalikan tepat waktu dalam kondisi baik',
            'created_at' => Carbon::now()->subDays(3)->setHour(7)->setMinute(45),
        ]);

        // Booking 6: CANCELLED (Siti cancelled Fujifilm X-T5 before start)
        $fujiItem = $createdItems['fujifilm-x-t5'];
        $b6 = Booking::firstOrCreate(['code' => 'RNT-' . date('Ymd') . '-0006'], [
            'user_id' => $customer2->id,
            'start_at' => Carbon::now()->addDays(5)->setHour(10)->setMinute(0),
            'end_at' => Carbon::now()->addDays(7)->setHour(10)->setMinute(0),
            'status' => BookingStatus::CANCELLED,
            'subtotal_rental' => $fujiItem->price_per_day * 2,
            'deposit_total' => $fujiItem->deposit,
            'discount_amount' => 0,
            'late_fee' => 0,
            'damage_fee' => 0,
            'grand_total' => ($fujiItem->price_per_day * 2) + $fujiItem->deposit,
            'expires_at' => null,
        ]);
        BookingItem::firstOrCreate(['booking_id' => $b6->id, 'item_id' => $fujiItem->id], [
            'qty' => 1,
            'unit_price' => $fujiItem->price_per_day,
            'unit_deposit' => $fujiItem->deposit,
            'subtotal' => $fujiItem->price_per_day * 2,
        ]);
        BookingStatusLog::firstOrCreate(['booking_id' => $b6->id, 'to_status' => BookingStatus::CANCELLED->value], [
            'from_status' => BookingStatus::PENDING->value,
            'actor_id' => $customer2->id,
            'reason' => 'Dibatalkan oleh pelanggan: acara diundur',
            'created_at' => Carbon::now()->subHours(1),
        ]);
    }
}
