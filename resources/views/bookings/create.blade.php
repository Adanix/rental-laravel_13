<x-layouts.app title="Formulir Booking Unit">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <div class="mb-6">
            <a href="{{ route('home') }}" class="text-xs sm:text-sm font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mb-2">
                &larr; Kembali ke Katalog
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Formulir Pemesanan Rental</h1>
            <p class="text-sm text-slate-500 mt-1">Lengkapi jadwal sewa dan jumlah unit. Harga dihitung secara otomatis dan transparan.</p>
        </div>

        @php
            $itemsJson = $items->mapWithKeys(function ($it) {
                return [$it->id => [
                    'id' => $it->id,
                    'name' => $it->name,
                    'category' => $it->category->name,
                    'price_per_day' => (int) $it->price_per_day,
                    'deposit' => (int) $it->deposit,
                    'stock' => (int) $it->stock,
                    'image' => $it->primary_image_url,
                ]];
            })->all();

            $initialItemId = $selectedItem ? $selectedItem->id : ($items->first()->id ?? 0);
            $weeklyMinDays = (int) \App\Models\Setting::get('weekly_discount_min_days', 7);
            $weeklyPercent = (int) \App\Models\Setting::get('weekly_discount_percent', 15);
        @endphp

        <form
            method="POST"
            action="{{ route('bookings.store') }}"
            x-data="{
                itemsData: {{ Js::from($itemsJson) }},
                selectedItemId: {{ $initialItemId }},
                startAt: '{{ $startAt }}',
                endAt: '{{ $endAt }}',
                qty: {{ $qty }},
                weeklyMinDays: {{ $weeklyMinDays }},
                weeklyPercent: {{ $weeklyPercent }},

                get currentItem() {
                    return this.itemsData[this.selectedItemId] || null;
                },

                get maxStock() {
                    return this.currentItem ? this.currentItem.stock : 1;
                },

                get durationDays() {
                    if (!this.startAt || !this.endAt) return 1;
                    const s = new Date(this.startAt);
                    const e = new Date(this.endAt);
                    if (e <= s) return 1;
                    const diffHours = (e - s) / (1000 * 60 * 60);
                    return Math.max(1, Math.ceil(diffHours / 24));
                },

                get rawRentalSubtotal() {
                    if (!this.currentItem) return 0;
                    return this.durationDays * this.currentItem.price_per_day * this.qty;
                },

                get discountAmount() {
                    if (this.durationDays >= this.weeklyMinDays && this.weeklyPercent > 0) {
                        return Math.round(this.rawRentalSubtotal * (this.weeklyPercent / 100));
                    }
                    return 0;
                },

                get finalRentalSubtotal() {
                    return Math.max(0, this.rawRentalSubtotal - this.discountAmount);
                },

                get depositTotal() {
                    if (!this.currentItem) return 0;
                    return this.currentItem.deposit * this.qty;
                },

                get grandTotal() {
                    return this.finalRentalSubtotal + this.depositTotal;
                },

                formatRp(num) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
                }
            }"
            class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start"
        >
            @csrf

            <!-- Left: Selection & Date inputs (7 cols) -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Unit Selection Card -->
                <x-card class="p-6 space-y-4">
                    <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">1. Pilih Unit Barang</h2>

                    <div>
                        <label for="item_id" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Pilih Unit Rental <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <select
                            id="item_id"
                            name="item_id"
                            x-model.number="selectedItemId"
                            x-on:change="if (qty > maxStock) qty = maxStock;"
                            required
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">
                                    {{ $item->name }} ({{ format_rupiah($item->price_per_day) }}/hari - Stok: {{ $item->stock }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Selected Item Quick Card -->
                    <template x-if="currentItem">
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-3">
                            <img :src="currentItem.image" :alt="currentItem.name" class="w-16 h-16 rounded-lg object-cover shrink-0" />
                            <div class="flex-1 min-w-0">
                                <span class="text-xs font-semibold text-indigo-600 uppercase" x-text="currentItem.category"></span>
                                <h4 class="font-bold text-slate-900 text-sm truncate" x-text="currentItem.name"></h4>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    Tarif: <strong class="text-slate-800" x-text="formatRp(currentItem.price_per_day)"></strong>/hari
                                    &bull; Deposit: <span x-text="formatRp(currentItem.deposit)"></span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Qty Selector -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Jumlah Unit yang Disewa <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                x-on:click="if (qty > 1) qty--"
                                class="w-11 h-11 rounded-xl border border-slate-300 flex items-center justify-center font-bold text-slate-700 hover:bg-slate-100 transition"
                            >
                                -
                            </button>
                            <input
                                type="number"
                                name="qty"
                                x-model.number="qty"
                                min="1"
                                :max="maxStock"
                                class="w-16 h-11 text-center font-bold text-base rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                required
                            />
                            <button
                                type="button"
                                x-on:click="if (qty < maxStock) qty++"
                                class="w-11 h-11 rounded-xl border border-slate-300 flex items-center justify-center font-bold text-slate-700 hover:bg-slate-100 transition"
                            >
                                +
                            </button>
                            <span class="text-xs text-slate-500">
                                (Maksimal <span x-text="maxStock"></span> unit)
                            </span>
                        </div>
                    </div>
                </x-card>

                <!-- Jadwal Sewa Card -->
                <x-card class="p-6 space-y-4">
                    <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">2. Jadwal Serah-Terima Unit</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="start_at" class="block text-sm font-medium text-slate-700 mb-1.5">
                                Waktu Ambil (Pickup) <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <input
                                id="start_at"
                                type="datetime-local"
                                name="start_at"
                                x-model="startAt"
                                required
                                class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div>
                            <label for="end_at" class="block text-sm font-medium text-slate-700 mb-1.5">
                                Waktu Kembali (Return) <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <input
                                id="end_at"
                                type="datetime-local"
                                name="end_at"
                                x-model="endAt"
                                required
                                class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Catatan Tambahan (Opsional)
                        </label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="2"
                            placeholder="Contoh: Titip baterai terisi penuh, ambil jam 10 pagi"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >{{ old('notes') }}</textarea>
                    </div>
                </x-card>
            </div>

            <!-- Right: Live Pricing & Transparent Breakdown (5 cols) -->
            <div class="lg:col-span-5 sticky top-24 space-y-4">
                <x-card class="p-6 sm:p-8 space-y-5 border-indigo-200 shadow-md">
                    <h3 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3">Rincian Pembayaran</h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>Durasi Sewa:</span>
                            <span class="font-bold text-slate-900" x-text="durationDays + ' Hari'"></span>
                        </div>

                        <div class="flex justify-between text-slate-600">
                            <span>Biaya Sewa Unit:</span>
                            <span class="font-medium text-slate-800" x-text="formatRp(rawRentalSubtotal)"></span>
                        </div>

                        <!-- Long rental discount badge -->
                        <template x-if="discountAmount > 0">
                            <div class="flex justify-between text-emerald-700 font-medium p-2 rounded-lg bg-emerald-50 border border-emerald-200 text-xs">
                                <span>Diskon Sewa Mingguan (<span x-text="weeklyPercent"></span>%):</span>
                                <span class="font-bold" x-text="'- ' + formatRp(discountAmount)"></span>
                            </div>
                        </template>

                        <div class="flex justify-between text-slate-600">
                            <div>
                                <span>Deposit Jaminan:</span>
                                <span class="block text-[11px] text-slate-400">Dikembalikan saat unit kembali baik</span>
                            </div>
                            <span class="font-medium text-slate-800" x-text="formatRp(depositTotal)"></span>
                        </div>

                        <div class="pt-4 border-t border-slate-200">
                            <div class="flex justify-between items-baseline">
                                <div>
                                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 block">Total Tagihan Awal</span>
                                    <span class="text-xs text-slate-400">(Sewa + Deposit)</span>
                                </div>
                                <span class="text-2xl font-extrabold text-indigo-700" x-text="formatRp(grandTotal)"></span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button
                            type="submit"
                            class="w-full min-h-[48px] px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-base transition shadow-md flex items-center justify-center gap-2 cursor-pointer"
                        >
                            Konfirmasi & Buat Pesanan &rarr;
                        </button>
                        <p class="text-[11px] text-slate-400 text-center mt-2">
                            Dengan mengklik tombol di atas, Anda menyetujui syarat & ketentuan rental.
                        </p>
                    </div>
                </x-card>
            </div>
        </form>
    </div>
</x-layouts.app>
