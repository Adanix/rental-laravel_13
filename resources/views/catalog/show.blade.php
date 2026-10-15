<x-layouts.app :title="$item->name">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-xs sm:text-sm text-slate-500 mb-6 sm:mb-8 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('home') }}" class="hover:text-indigo-600 transition">Katalog</a>
            <span>/</span>
            <a href="{{ route('home', ['category' => $item->category->slug]) }}" class="hover:text-indigo-600 transition">
                {{ $item->category->name }}
            </a>
            <span>/</span>
            <span class="text-slate-900 font-semibold truncate">{{ $item->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            <!-- Left Column: Gallery & Description & Specs (7 cols) -->
            <div class="lg:col-span-7 space-y-8">
                <!-- Photo Gallery (Alpine.js) -->
                @php
                    $imagesList = $item->images->isNotEmpty()
                        ? $item->images->map(fn($img) => $img->url)->all()
                        : [$item->primary_image_url];
                @endphp
                <div
                    x-data="{
                        images: {{ Js::from($imagesList) }},
                        activeIndex: 0
                    }"
                    class="space-y-3"
                >
                    <!-- Main Image Display -->
                    <div class="relative aspect-4/3 sm:aspect-16/10 rounded-2xl overflow-hidden bg-slate-100 border border-slate-200 shadow-xs">
                        <img
                            :src="images[activeIndex]"
                            alt="{{ $item->name }}"
                            class="w-full h-full object-cover transition-all duration-300"
                        />
                    </div>

                    <!-- Thumbnails Row -->
                    @if(count($imagesList) > 1)
                        <div class="flex items-center gap-2.5 overflow-x-auto pb-2">
                            <template x-for="(img, idx) in images" :key="idx">
                                <button
                                    type="button"
                                    x-on:click="activeIndex = idx"
                                    :class="activeIndex === idx ? 'ring-2 ring-indigo-600 ring-offset-2 border-indigo-600' : 'opacity-70 hover:opacity-100 border-slate-200'"
                                    class="w-20 h-16 rounded-xl overflow-hidden border shrink-0 transition cursor-pointer"
                                >
                                    <img :src="img" class="w-full h-full object-cover" />
                                </button>
                            </template>
                        </div>
                    @endif
                </div>

                <!-- Description -->
                <x-card class="p-6 sm:p-8 space-y-4">
                    <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3">Deskripsi Unit</h2>
                    <div class="text-sm sm:text-base text-slate-700 leading-relaxed whitespace-pre-line">
                        {{ $item->description ?: 'Tidak ada deskripsi detail untuk unit ini.' }}
                    </div>
                </x-card>

                <!-- Technical Specs -->
                @if($item->specs)
                    <x-card class="p-6 sm:p-8 space-y-4">
                        <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3">Spesifikasi & Kelengkapan</h2>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            @foreach($item->specs as $key => $val)
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-0.5">{{ $key }}</dt>
                                    <dd class="font-medium text-slate-800">{{ $val }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </x-card>
                @endif
            </div>

            <!-- Right Column: Pricing & Live Booking Widget (5 cols) -->
            <div class="lg:col-span-5 sticky top-24">
                <x-card class="p-6 sm:p-8 space-y-6 shadow-md border-indigo-100">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                {{ $item->category->name }}
                            </span>
                            <span class="text-xs font-medium text-slate-500">
                                Total Stok: <strong class="text-slate-800">{{ $item->stock }}</strong> unit
                            </span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight leading-snug">
                            {{ $item->name }}
                        </h1>
                    </div>

                    <!-- Pricing Display -->
                    <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-100 space-y-2">
                        <div class="flex items-baseline justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-indigo-900">Tarif Sewa</span>
                            <div>
                                <span class="text-2xl sm:text-3xl font-extrabold text-indigo-600">{{ format_rupiah($item->price_per_day) }}</span>
                                <span class="text-xs font-medium text-slate-500">/hari</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-600 pt-2 border-t border-indigo-100/80">
                            <span>Deposit Jaminan (Dikembalikan):</span>
                            <span class="font-bold text-slate-800">{{ format_rupiah($item->deposit) }}</span>
                        </div>
                    </div>

                    <!-- Live Date Availability & Quick Calculator Widget (Alpine.js) -->
                    <div
                        x-data="{
                            startAt: '{{ request('start_at', now()->addDay()->format('Y-m-d\T09:00')) }}',
                            endAt: '{{ request('end_at', now()->addDays(2)->format('Y-m-d\T09:00')) }}',
                            qty: 1,
                            maxStock: {{ $item->stock }},
                            unitPrice: {{ $item->price_per_day }},
                            unitDeposit: {{ $item->deposit }},
                            checking: false,
                            checked: false,
                            isAvailable: true,
                            availableStock: {{ $item->stock }},

                            get isInvalidRange() {
                                if (!this.startAt || !this.endAt) return true;
                                return new Date(this.endAt) <= new Date(this.startAt);
                            },

                            get durationDays() {
                                if (this.isInvalidRange) return 1;
                                const s = new Date(this.startAt);
                                const e = new Date(this.endAt);
                                const diffHours = (e - s) / (1000 * 60 * 60);
                                return Math.max(1, Math.ceil(diffHours / 24));
                            },

                            get rentalSubtotal() {
                                return this.durationDays * this.unitPrice * this.qty;
                            },

                            get depositTotal() {
                                return this.unitDeposit * this.qty;
                            },

                            get grandTotal() {
                                return this.rentalSubtotal + this.depositTotal;
                            },

                            formatRp(num) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
                            },

                            async checkLiveAvailability() {
                                if (this.isInvalidRange) {
                                    this.checked = false;
                                    this.isAvailable = false;
                                    return;
                                }

                                this.checking = true;
                                try {
                                    const res = await fetch('{{ route('items.check-availability', $item) }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: JSON.stringify({
                                            start_at: this.startAt,
                                            end_at: this.endAt
                                        })
                                    });
                                    const data = await res.json();
                                    this.availableStock = data.stock_available;
                                    this.isAvailable = data.stock_available >= this.qty;
                                    this.checked = true;
                                } catch (err) {
                                    console.error(err);
                                } finally {
                                    this.checking = false;
                                }
                            }
                        }"
                        x-init="checkLiveAvailability()"
                        class="space-y-4"
                    >
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Pilih Jadwal & Cek Ketersediaan Live
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Mulai Sewa (Ambil)</label>
                                <input
                                    type="datetime-local"
                                    x-model="startAt"
                                    x-on:change="checkLiveAvailability()"
                                    class="w-full min-h-[44px] px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Selesai Sewa (Kembali)</label>
                                <input
                                    type="datetime-local"
                                    x-model="endAt"
                                    :min="startAt"
                                    x-on:change="checkLiveAvailability()"
                                    class="w-full min-h-[44px] px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                />
                            </div>
                        </div>

                        <!-- Date Range Warning -->
                        <div x-show="isInvalidRange" class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Waktu selesai sewa harus setelah waktu mulai sewa.</span>
                        </div>

                        <!-- Qty Selector -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Unit (Qty)</label>
                            <div class="flex items-center gap-3">
                                <button
                                    type="button"
                                    x-on:click="if (qty > 1) { qty--; checkLiveAvailability(); }"
                                    :disabled="qty <= 1"
                                    :class="qty <= 1 ? 'opacity-40 cursor-not-allowed bg-slate-50' : 'hover:bg-slate-100'"
                                    class="w-10 h-10 rounded-xl border border-slate-300 flex items-center justify-center font-bold text-slate-700 transition"
                                >
                                    -
                                </button>
                                <span x-text="qty" class="w-8 text-center font-bold text-base text-slate-900 font-mono"></span>
                                <button
                                    type="button"
                                    x-on:click="if (qty < maxStock) { qty++; checkLiveAvailability(); }"
                                    :disabled="qty >= maxStock"
                                    :class="qty >= maxStock ? 'opacity-40 cursor-not-allowed bg-slate-50' : 'hover:bg-slate-100'"
                                    class="w-10 h-10 rounded-xl border border-slate-300 flex items-center justify-center font-bold text-slate-700 transition"
                                >
                                    +
                                </button>
                                <span class="text-xs text-slate-500">
                                    (Maksimal <strong x-text="maxStock"></strong> unit)
                                </span>
                            </div>
                        </div>

                        <!-- Live Availability Status Banner -->
                        <div x-show="checked && !isInvalidRange" class="transition-all" style="display: none;">
                            <template x-if="isAvailable">
                                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-2.5 text-xs font-semibold">
                                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Tersedia untuk jadwal ini! (Tersisa <strong x-text="availableStock"></strong> unit)</span>
                                </div>
                            </template>
                            <template x-if="!isAvailable">
                                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-2.5 text-xs font-semibold">
                                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Maaf, unit penuh / tidak tersedia pada jadwal yang dipilih.</span>
                                </div>
                            </template>
                        </div>

                        <!-- Live Price Breakdown -->
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-2">
                            <div class="flex justify-between text-slate-600">
                                <span>Durasi Sewa:</span>
                                <span class="font-bold text-slate-900" x-text="durationDays + ' Hari'"></span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Subtotal Sewa (<span x-text="durationDays"></span> hari x <span x-text="qty"></span> unit):</span>
                                <span class="font-bold text-slate-900" x-text="formatRp(rentalSubtotal)"></span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Deposit Jaminan (<span x-text="qty"></span> unit):</span>
                                <span class="font-bold text-slate-900" x-text="formatRp(depositTotal)"></span>
                            </div>
                            <div class="flex justify-between text-sm font-extrabold text-indigo-700 pt-2 border-t border-slate-200">
                                <span>Estimasi Total Awal:</span>
                                <span x-text="formatRp(grandTotal)"></span>
                            </div>
                        </div>

                        <!-- Booking Action CTA Button -->
                        <form method="GET" action="{{ route('bookings.create') }}">
                            <input type="hidden" name="item_id" value="{{ $item->id }}" />
                            <input type="hidden" name="start_at" :value="startAt" />
                            <input type="hidden" name="end_at" :value="endAt" />
                            <input type="hidden" name="qty" :value="qty" />

                            <button
                                type="submit"
                                :disabled="!isAvailable || checking || isInvalidRange"
                                class="w-full min-h-[48px] px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white text-base font-bold transition shadow-md flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <span x-show="!checking">Booking Unit Sekarang &rarr;</span>
                                <span x-show="checking" style="display: none;">Memeriksa Ketersediaan...</span>
                            </button>
                        </form>
                    </div>
                </x-card>
            </div>
        </div>

        <!-- Related Items Section -->
        @if($relatedItems->isNotEmpty())
            <div class="mt-16 pt-12 border-t border-slate-200">
                <h2 class="text-xl font-bold text-slate-900 mb-6">Unit Terkait Lainnya</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedItems as $rel)
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition overflow-hidden flex flex-col">
                            <div class="aspect-4/3 bg-slate-100 overflow-hidden">
                                <img src="{{ $rel->primary_image_url }}" alt="{{ $rel->name }}" class="w-full h-full object-cover" loading="lazy" />
                            </div>
                            <div class="p-4 flex-1 flex flex-col justify-between">
                                <div>
                                    <span class="text-[11px] font-semibold text-indigo-600 uppercase">{{ $rel->category->name }}</span>
                                    <h4 class="font-bold text-slate-900 text-sm mt-1 line-clamp-1">
                                        <a href="{{ route('items.show', $rel->slug) }}" class="hover:underline">
                                            {{ $rel->name }}
                                        </a>
                                    </h4>
                                </div>
                                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
                                    <span class="font-extrabold text-indigo-600 text-sm">{{ format_rupiah($rel->price_per_day) }}<span class="text-xs text-slate-400 font-normal">/hr</span></span>
                                    <a href="{{ route('items.show', $rel->slug) }}" class="text-xs font-semibold text-slate-700 hover:text-indigo-600">
                                        Detail &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
