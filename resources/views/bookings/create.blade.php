<x-layouts.app title="Formulir Booking Unit">
    @php
        $itemsJson = $items->map(function ($it) {
            return [
                'id' => $it->id,
                'name' => $it->name,
                'category_id' => $it->category_id,
                'category' => $it->category->name,
                'price_per_day' => (int) $it->price_per_day,
                'deposit' => (int) $it->deposit,
                'stock' => (int) $it->stock,
                'image' => $it->primary_image_url,
            ];
        })->values();

        $categoriesList = $items->pluck('category.name')->unique()->values();
        $initialItemId = $selectedItem ? $selectedItem->id : ($items->first()->id ?? 0);
        $weeklyMinDays = (int) \App\Models\Setting::get('weekly_discount_min_days', 7);
        $weeklyPercent = (int) \App\Models\Setting::get('weekly_discount_percent', 15);
        $minDate = now()->format('Y-m-d\TH:i');
    @endphp

    <div
        class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12"
        x-data="{
            items: {{ Js::from($itemsJson) }},
            categories: {{ Js::from($categoriesList) }},
            selectedItemId: {{ $initialItemId }},
            startAt: '{{ $startAt }}',
            endAt: '{{ $endAt }}',
            qty: {{ $qty }},
            weeklyMinDays: {{ $weeklyMinDays }},
            weeklyPercent: {{ $weeklyPercent }},
            itemModalOpen: false,
            searchQuery: '',
            selectedCategory: '',

            get currentItem() {
                return this.items.find(i => i.id == this.selectedItemId) || null;
            },

            get maxStock() {
                return this.currentItem ? this.currentItem.stock : 1;
            },

            get filteredItems() {
                return this.items.filter(item => {
                    const query = this.searchQuery.toLowerCase().trim();
                    const matchSearch = !query ||
                        item.name.toLowerCase().includes(query) ||
                        item.category.toLowerCase().includes(query);
                    const matchCat = !this.selectedCategory || item.category === this.selectedCategory;
                    return matchSearch && matchCat;
                });
            },

            selectItem(item) {
                this.selectedItemId = item.id;
                if (this.qty > item.stock) {
                    this.qty = item.stock;
                }
                this.itemModalOpen = false;
            },

            get isInvalidDateRange() {
                if (!this.startAt || !this.endAt) return true;
                return new Date(this.endAt) <= new Date(this.startAt);
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
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(num || 0);
            }
        }"
    >
        <div class="mb-6">
            <a href="{{ route('home') }}" class="text-xs sm:text-sm font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mb-2">
                &larr; Kembali ke Katalog
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Formulir Pemesanan Rental</h1>
            <p class="text-sm text-slate-500 mt-1">Lengkapi jadwal sewa dan jumlah unit. Harga dihitung secara otomatis dan transparan.</p>
        </div>

        <form
            method="POST"
            action="{{ route('bookings.store') }}"
            class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start"
        >
            @csrf

            <!-- Left: Selection & Date inputs (7 cols) -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Unit Selection Card -->
                <x-card class="p-6 space-y-4">
                    <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">1. Pilih Unit Barang</h2>

                    <!-- Hidden input submitted with the form -->
                    <input type="hidden" name="item_id" :value="selectedItemId" required />

                    <!-- Searchable Picker Trigger & Preview -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-slate-700">Unit Rental Terpilih <span class="text-rose-500 font-bold">*</span></label>
                            <button
                                type="button"
                                x-on:click="itemModalOpen = true; searchQuery = ''"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <span x-text="currentItem ? 'Cari / Ganti Unit Lain' : 'Cari Unit Rental'"></span>
                            </button>
                        </div>

                        <!-- Selected Item Preview Card -->
                        <template x-if="currentItem">
                            <div class="p-4 rounded-2xl bg-indigo-50/50 border-2 border-indigo-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <img
                                        :src="currentItem.image"
                                        :alt="currentItem.name"
                                        class="w-16 h-16 rounded-xl object-cover shrink-0 border border-slate-200 bg-white shadow-2xs"
                                    />
                                    <div class="min-w-0">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wide"
                                            x-text="currentItem.category"
                                        ></span>
                                        <h3 class="font-bold text-slate-900 text-sm sm:text-base truncate mt-0.5" x-text="currentItem.name"></h3>
                                        <div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                            <strong class="text-indigo-700 font-mono text-xs sm:text-sm" x-text="formatRp(currentItem.price_per_day) + '/hari'"></strong>
                                            <span class="text-slate-300">&bull;</span>
                                            <span>Deposit: <span class="font-semibold text-slate-700 font-mono" x-text="formatRp(currentItem.deposit)"></span></span>
                                            <span class="text-slate-300">&bull;</span>
                                            <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded text-[11px]">
                                                Stok: <strong x-text="currentItem.stock"></strong> unit
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    x-on:click="itemModalOpen = true; searchQuery = ''"
                                    class="shrink-0 min-h-[44px] px-4 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 text-xs sm:text-sm font-semibold flex items-center justify-center gap-1.5 shadow-2xs transition"
                                >
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Ganti Unit
                                </button>
                            </div>
                        </template>

                        <!-- Unselected Placeholder -->
                        <template x-if="!currentItem">
                            <button
                                type="button"
                                x-on:click="itemModalOpen = true; searchQuery = ''"
                                class="w-full min-h-[72px] p-4 rounded-2xl border-2 border-dashed border-indigo-300 hover:border-indigo-500 bg-indigo-50/20 hover:bg-indigo-50/50 text-indigo-700 font-semibold text-sm flex items-center justify-center gap-2 transition cursor-pointer"
                            >
                                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Klik di sini untuk Cari & Pilih Unit Rental...
                            </button>
                        </template>
                    </div>

                    <!-- Qty Selector -->
                    <div class="pt-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Jumlah Unit yang Disewa <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                x-on:click="if (qty > 1) qty--"
                                :disabled="qty <= 1"
                                :class="qty <= 1 ? 'opacity-40 cursor-not-allowed bg-slate-50' : 'hover:bg-slate-100'"
                                class="w-11 h-11 rounded-xl border border-slate-300 flex items-center justify-center font-bold text-slate-700 transition"
                            >
                                -
                            </button>
                            <input
                                type="number"
                                name="qty"
                                x-model.number="qty"
                                min="1"
                                :max="maxStock"
                                class="w-20 min-h-[44px] text-center font-bold font-mono text-base rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                required
                            />
                            <button
                                type="button"
                                x-on:click="if (qty < maxStock) qty++"
                                :disabled="qty >= maxStock"
                                :class="qty >= maxStock ? 'opacity-40 cursor-not-allowed bg-slate-50' : 'hover:bg-slate-100'"
                                class="w-11 h-11 rounded-xl border border-slate-300 flex items-center justify-center font-bold text-slate-700 transition"
                            >
                                +
                            </button>
                            <span class="text-xs text-slate-500">
                                (Maksimal <strong x-text="maxStock"></strong> unit tersedia)
                            </span>
                        </div>
                        <span class="text-xs text-rose-600 font-semibold mt-1 block" x-show="qty > maxStock">
                            ⚠️ Melebihi total stok yang tersedia (<span x-text="maxStock"></span> unit)!
                        </span>
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
                                :min="startAt"
                                required
                                class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                    </div>

                    <!-- Date Warning if Invalid -->
                    <div x-show="isInvalidDateRange" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Waktu selesai sewa (kembali) harus lebih lambat dari waktu mulai sewa (ambil).</span>
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
                            :disabled="isInvalidDateRange || !currentItem || qty < 1 || qty > maxStock"
                            :class="(isInvalidDateRange || !currentItem || qty < 1 || qty > maxStock) ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-indigo-600 hover:bg-indigo-700 shadow-md cursor-pointer'"
                            class="w-full min-h-[48px] px-6 py-3 rounded-xl text-white font-bold text-base transition flex items-center justify-center gap-2"
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

        <!-- Searchable Modal for Unit Selection -->
        <div
            x-show="itemModalOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
            x-on:keydown.escape.window="itemModalOpen = false"
        >
            <div
                x-show="itemModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                x-on:click.outside="itemModalOpen = false"
                class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden"
            >
                <!-- Modal Header -->
                <div class="p-5 border-b border-slate-100 space-y-3 bg-slate-50/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Pilih Unit Rental</h3>
                            <p class="text-xs text-slate-500">Cari berdasarkan nama atau filter berdasarkan kategori.</p>
                        </div>
                        <button
                            type="button"
                            x-on:click="itemModalOpen = false"
                            class="w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-400 hover:text-slate-600 flex items-center justify-center transition shadow-2xs"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            x-model="searchQuery"
                            placeholder="Ketik untuk mencari (mis: Sony, FX3, Mic, Lensa)..."
                            class="w-full min-h-[44px] pl-10 pr-10 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                        />
                        <button
                            type="button"
                            x-show="searchQuery"
                            x-on:click="searchQuery = ''"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 text-sm"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Category Filter Chips -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                        <button
                            type="button"
                            x-on:click="selectedCategory = ''"
                            :class="!selectedCategory ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100'"
                            class="px-3 py-1.5 rounded-full font-semibold shrink-0 transition"
                        >
                            Semua (<span x-text="items.length"></span>)
                        </button>
                        <template x-for="cat in categories" :key="cat">
                            <button
                                type="button"
                                x-on:click="selectedCategory = (selectedCategory === cat ? '' : cat)"
                                :class="selectedCategory === cat ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100'"
                                class="px-3 py-1.5 rounded-full font-semibold shrink-0 transition"
                                x-text="cat"
                            ></button>
                        </template>
                    </div>
                </div>

                <!-- Items List Scroll Area -->
                <div class="flex-1 overflow-y-auto p-4 space-y-2">
                    <template x-for="item in filteredItems" :key="item.id">
                        <div
                            x-on:click="selectItem(item)"
                            :class="selectedItemId === item.id ? 'border-indigo-600 bg-indigo-50/60 ring-2 ring-indigo-500/20' : 'border-slate-200 hover:border-indigo-300 hover:bg-slate-50'"
                            class="p-3 sm:p-3.5 rounded-2xl border flex items-center justify-between gap-3 cursor-pointer transition select-none"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <img
                                    :src="item.image"
                                    :alt="item.name"
                                    class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl object-cover shrink-0 border border-slate-200 bg-white"
                                />
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide" x-text="item.category"></span>
                                        <span
                                            x-show="selectedItemId === item.id"
                                            class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-indigo-600 text-white"
                                        >
                                            ✓ Terpilih
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-slate-900 text-sm truncate" x-text="item.name"></h4>
                                    <div class="text-xs text-slate-500 flex flex-wrap items-center gap-x-2 gap-y-0.5 mt-0.5">
                                        <strong class="text-indigo-700 font-mono" x-text="formatRp(item.price_per_day) + '/hari'"></strong>
                                        <span class="text-slate-300">&bull;</span>
                                        <span>Deposit: <span class="font-mono text-slate-700" x-text="formatRp(item.deposit)"></span></span>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <span
                                    :class="item.stock > 1 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold border"
                                    x-text="'Stok: ' + item.stock"
                                ></span>
                            </div>
                        </div>
                    </template>

                    <!-- Empty Search State -->
                    <div x-show="filteredItems.length === 0" class="py-12 text-center space-y-2">
                        <svg class="w-12 h-12 text-slate-300 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <p class="text-sm font-semibold text-slate-700">Tidak ada unit yang cocok</p>
                        <p class="text-xs text-slate-400">Coba gunakan kata kunci lain atau pilih kategori Semua.</p>
                        <button
                            type="button"
                            x-on:click="searchQuery = ''; selectedCategory = ''"
                            class="text-xs font-semibold text-indigo-600 hover:underline pt-2"
                        >
                            Reset Filter
                        </button>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 px-5">
                    <span>Menampilkan <strong class="text-slate-800" x-text="filteredItems.length"></strong> dari <span x-text="items.length"></span> unit</span>
                    <button
                        type="button"
                        x-on:click="itemModalOpen = false"
                        class="px-4 py-1.5 rounded-xl border border-slate-300 bg-white font-semibold text-slate-700 hover:bg-slate-100 transition shadow-2xs"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
