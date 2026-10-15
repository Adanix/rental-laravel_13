<x-layouts.admin title="Buat Pesanan Walk-in">
    @php
        $itemsJson = $items->map(function ($it) {
            return [
                'id' => $it->id,
                'name' => $it->name,
                'category' => $it->category->name,
                'price_per_day' => $it->price_per_day,
                'deposit' => $it->deposit,
                'stock' => $it->stock,
            ];
        })->values();
    @endphp

    <div
        class="max-w-4xl mx-auto space-y-6"
        x-data="{
            items: {{ Js::from($itemsJson) }},
            selectedItemId: {{ $items->first()?->id ?? 'null' }},
            qty: 1,
            startAt: '{{ $startAt }}',
            endAt: '{{ $endAt }}',
            paymentMethod: 'cash',
            directPickup: true,
            
            get selectedItem() {
                return this.items.find(i => i.id == this.selectedItemId) || null;
            },
            get durationDays() {
                if (!this.startAt || !this.endAt) return 1;
                const start = new Date(this.startAt);
                const end = new Date(this.endAt);
                const diffMs = end - start;
                if (diffMs <= 0) return 1;
                const diffHours = diffMs / (1000 * 60 * 60);
                return Math.max(1, Math.ceil(diffHours / 24));
            },
            get subtotalRental() {
                if (!this.selectedItem) return 0;
                return this.durationDays * this.selectedItem.price_per_day * this.qty;
            },
            get discountAmount() {
                // If >= 7 days, 15% discount
                if (this.durationDays >= 7) {
                    return Math.round(this.subtotalRental * 0.15);
                }
                return 0;
            },
            get depositTotal() {
                if (!this.selectedItem) return 0;
                return this.selectedItem.deposit * this.qty;
            },
            get grandTotal() {
                return (this.subtotalRental - this.discountAmount) + this.depositTotal;
            },
            formatRupiah(val) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(val || 0);
            }
        }"
    >
        <!-- Top Bar -->
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.bookings.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-indigo-600 shadow-2xs transition">
                &larr;
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Pesanan Walk-in (Counter)</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Input pemesanan langsung untuk pelanggan di kasir/counter.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.bookings.walk-in.store') }}" class="space-y-6">
            @csrf

            <!-- Pelanggan Card -->
            <x-card class="p-6 space-y-4">
                <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">1. Data Pelanggan</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="customer_phone" class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp / HP *</label>
                        <input
                            type="text"
                            name="customer_phone"
                            id="customer_phone"
                            required
                            value="{{ old('customer_phone') }}"
                            placeholder="08123456789"
                            class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"
                        />
                        <span class="text-[11px] text-slate-400 mt-1 block">Jika nomor sudah terdaftar, pesanan otomatis dikaitkan ke akun pelanggan tersebut.</span>
                    </div>

                    <div>
                        <label for="customer_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                        <input
                            type="text"
                            name="customer_name"
                            id="customer_name"
                            required
                            value="{{ old('customer_name') }}"
                            placeholder="Contoh: Budi Santoso"
                            class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>
                </div>
            </x-card>

            <!-- Unit & Jadwal Card -->
            <x-card class="p-6 space-y-4">
                <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">2. Unit Rental & Durasi</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label for="item_id" class="block text-xs font-semibold text-slate-700 mb-1">Pilih Unit *</label>
                        <select
                            name="item_id"
                            id="item_id"
                            x-model.number="selectedItemId"
                            required
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            @foreach($items as $it)
                                <option value="{{ $it->id }}">
                                    {{ $it->name }} ({{ $it->category->name }}) - {{ format_rupiah($it->price_per_day) }}/hari
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="qty" class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Unit *</label>
                        <input
                            type="number"
                            name="qty"
                            id="qty"
                            x-model.number="qty"
                            min="1"
                            :max="selectedItem ? selectedItem.stock : 10"
                            required
                            class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center font-bold"
                        />
                        <span class="text-[11px] text-slate-400 mt-1 block" x-show="selectedItem">Stok total: <strong x-text="selectedItem.stock"></strong></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label for="start_at" class="block text-xs font-semibold text-slate-700 mb-1">Waktu Mulai Sewa *</label>
                        <input
                            type="datetime-local"
                            name="start_at"
                            id="start_at"
                            x-model="startAt"
                            required
                            class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>

                    <div>
                        <label for="end_at" class="block text-xs font-semibold text-slate-700 mb-1">Waktu Selesai Sewa *</label>
                        <input
                            type="datetime-local"
                            name="end_at"
                            id="end_at"
                            x-model="endAt"
                            required
                            class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>
                </div>
            </x-card>

            <!-- Kalkulasi Live & Pembayaran Card -->
            <x-card class="p-6 space-y-4">
                <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">3. Rincian Biaya & Pembayaran</h2>

                <!-- Live Price Calculation Breakdown -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2 text-xs sm:text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Durasi Sewa:</span>
                        <strong class="text-slate-900 font-mono" x-text="durationDays + ' Hari'"></strong>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Tarif Sewa:</span>
                        <span class="font-mono" x-text="formatRupiah(subtotalRental)"></span>
                    </div>
                    <div class="flex justify-between text-emerald-700" x-show="discountAmount > 0">
                        <span>Diskon Sewa Panjang (15%):</span>
                        <span class="font-bold font-mono" x-text="'- ' + formatRupiah(discountAmount)"></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Deposit Jaminan:</span>
                        <span class="font-mono" x-text="formatRupiah(depositTotal)"></span>
                    </div>
                    <div class="border-t border-slate-200 pt-2 flex justify-between font-extrabold text-base text-indigo-700">
                        <span>Total yang Harus Dibayar:</span>
                        <span class="font-mono" x-text="formatRupiah(grandTotal)"></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label for="payment_method" class="block text-xs font-semibold text-slate-700 mb-1">Metode Pembayaran di Counter *</label>
                        <select
                            name="payment_method"
                            id="payment_method"
                            x-model="paymentMethod"
                            required
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-slate-800"
                        >
                            <option value="cash">Tunai (Cash di Counter)</option>
                            <option value="qris">QRIS (Langsung Scan)</option>
                            <option value="transfer">Transfer Bank</option>
                        </select>
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="relative flex items-start gap-2 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                name="direct_pickup"
                                value="1"
                                x-model="directPickup"
                                class="w-4 h-4 mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            <div>
                                <span class="text-xs font-bold text-slate-800 block">Langsung Serahkan Unit (Direct Pickup)</span>
                                <span class="text-[11px] text-slate-500 block">Status pesanan akan langsung menjadi <em>Sedang Disewa (Picked Up)</em>.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea
                        id="notes"
                        name="notes"
                        rows="2"
                        placeholder="Contoh: Titip KTP asli sebagai jaminan identitas"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    ></textarea>
                </div>
            </x-card>

            <!-- Submit Button -->
            <div class="flex justify-end gap-3">
                <a
                    href="{{ route('admin.bookings.index') }}"
                    class="min-h-[44px] px-5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition"
                >
                    Batal
                </a>
                <x-button type="submit" variant="primary">
                    Simpan Pesanan & Proses
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.admin>
