<x-layouts.admin title="Buat Pesanan Walk-in">
    @php
        $itemsJson = $items->map(function ($it) {
            return [
                'id' => $it->id,
                'name' => $it->name,
                'category_id' => $it->category_id,
                'category' => $it->category->name,
                'price_per_day' => $it->price_per_day,
                'deposit' => $it->deposit,
                'stock' => $it->stock,
                'image' => $it->primary_image_url,
            ];
        })->values();

        $categoriesList = $items->pluck('category.name')->unique()->values();
    @endphp

    <div
        class="max-w-4xl mx-auto space-y-6"
        x-data="{
            items: {{ Js::from($itemsJson) }},
            categories: {{ Js::from($categoriesList) }},
            selectedItemId: {{ $items->first()?->id ?? 'null' }},
            qty: 1,
            startAt: '{{ $startAt }}',
            endAt: '{{ $endAt }}',
            paymentMethod: 'cash',
            directPickup: true,
            itemModalOpen: false,
            searchQuery: '',
            selectedCategory: '',
            
            get selectedItem() {
                return this.items.find(i => i.id == this.selectedItemId) || null;
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
                    this.qty = 1;
                }
                this.itemModalOpen = false;
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
            <x-card class="p-6 space-y-5">
                <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">2. Unit Rental & Durasi</h2>

                <!-- Hidden Input for Form Submission -->
                <input type="hidden" name="item_id" :value="selectedItemId" required />

                <!-- Searchable Unit Picker Component -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-semibold text-slate-700">Unit Rental Terpilih *</label>
                        <button
                            type="button"
                            x-on:click="itemModalOpen = true; searchQuery = ''"
                            class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span x-text="selectedItem ? 'Cari / Ganti Unit Lain' : 'Cari Unit Rental'"></span>
                        </button>
                    </div>

                    <!-- Selected Item Preview Card -->
                    <template x-if="selectedItem">
                        <div class="p-4 rounded-2xl bg-indigo-50/50 border-2 border-indigo-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <img
                                    :src="selectedItem.image"
                                    :alt="selectedItem.name"
                                    class="w-16 h-16 rounded-xl object-cover shrink-0 border border-slate-200 bg-white shadow-2xs"
                                />
                                <div class="min-w-0">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wide"
                                        x-text="selectedItem.category"
                                    ></span>
                                    <h3 class="font-bold text-slate-900 text-sm sm:text-base truncate mt-0.5" x-text="selectedItem.name"></h3>
                                    <div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                        <strong class="text-indigo-700 font-mono text-xs sm:text-sm" x-text="formatRupiah(selectedItem.price_per_day) + '/hari'"></strong>
                                        <span class="text-slate-300">&bull;</span>
                                        <span>Deposit: <span class="font-semibold text-slate-700 font-mono" x-text="formatRupiah(selectedItem.deposit)"></span></span>
                                        <span class="text-slate-300">&bull;</span>
                                        <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded text-[11px]">
                                            Stok: <strong x-text="selectedItem.stock"></strong> unit
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

                    <!-- Unselected Item Placeholder Button -->
                    <template x-if="!selectedItem">
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

                <!-- Qty and Dates Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <div>
                        <label for="qty" class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Unit Disewa *</label>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                x-on:click="if (qty > 1) qty--"
                                class="w-11 h-11 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 flex items-center justify-center font-bold text-slate-600 text-lg shadow-2xs transition"
                            >-</button>
                            <input
                                type="number"
                                name="qty"
                                id="qty"
                                x-model.number="qty"
                                min="1"
                                :max="selectedItem ? selectedItem.stock : 10"
                                required
                                class="flex-1 min-h-[44px] px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center font-bold font-mono"
                            />
                            <button
                                type="button"
                                x-on:click="if (!selectedItem || qty < selectedItem.stock) qty++"
                                class="w-11 h-11 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 flex items-center justify-center font-bold text-slate-600 text-lg shadow-2xs transition"
                            >+</button>
                        </div>
                        <span class="text-[11px] text-rose-600 font-semibold mt-1 block" x-show="selectedItem && qty > selectedItem.stock">
                            ⚠️ Melebihi batas stok (<span x-text="selectedItem.stock"></span>)!
                        </span>
                    </div>

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

        <!-- Modal Cari & Pilih Unit Rental (Searchable Picker) -->
        <div
            x-show="itemModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
            style="display: none;"
            x-on:keydown.escape.window="itemModalOpen = false"
        >
            <div
                x-show="itemModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-on:click.outside="itemModalOpen = false"
                class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden flex flex-col max-h-[85vh]"
            >
                <!-- Modal Header & Search Box -->
                <div class="p-4 sm:p-6 border-b border-slate-100 space-y-3 bg-slate-50/50 shrink-0">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">Pilih Unit Rental</h3>
                            <p class="text-xs text-slate-500">Cari berdasarkan nama produk, tipe, atau kategori unit.</p>
                        </div>
                        <button
                            type="button"
                            x-on:click="itemModalOpen = false"
                            class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/50 transition"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                            placeholder="Ketik untuk mencari (mis: Sony, FX3, Mic Pro, Lensa)..."
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
                                        <strong class="text-indigo-700 font-mono" x-text="formatRupiah(item.price_per_day) + '/hari'"></strong>
                                        <span class="text-slate-300">&bull;</span>
                                        <span>Deposit: <span class="font-mono text-slate-700" x-text="formatRupiah(item.deposit)"></span></span>
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
</x-layouts.admin>
