<x-layouts.app title="Katalog Unit Rental">
    <!-- Hero Section -->
    <section class="bg-gradient-to-b from-indigo-900 via-indigo-800 to-indigo-950 text-white py-12 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 mb-4">
                    ✨ Solusi Rental Cepat & Transparan
                </span>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">
                    Sewa Unit & Peralatan Terbaik Tanpa Ribet
                </h1>
                <p class="mt-4 text-base sm:text-lg text-indigo-100 max-w-2xl leading-relaxed">
                    Kamera, audio, alat camping, hingga proyektor. Cek ketersediaan tanggal secara live, rincian biaya transparan, dan jaminan unit prima.
                </p>
            </div>

            <!-- Date & Search Form Card -->
            <div class="mt-8 bg-white text-slate-800 rounded-2xl p-4 sm:p-6 shadow-xl border border-white/20">
                <form
                    method="GET"
                    action="{{ route('home') }}"
                    x-data="{
                        startAt: '{{ request('start_at') }}',
                        endAt: '{{ request('end_at') }}',
                        get isInvalidRange() {
                            return this.startAt && this.endAt && new Date(this.endAt) <= new Date(this.startAt);
                        }
                    }"
                    class="space-y-3"
                >
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                        <!-- Search input -->
                        <div>
                            <label for="q" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                                Cari Barang
                            </label>
                            <input
                                id="q"
                                type="text"
                                name="q"
                                value="{{ request('q') }}"
                                placeholder="Kamera, drone, tenda..."
                                class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            />
                        </div>

                        <!-- Date Start -->
                        <div>
                            <label for="start_at" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                                Mulai Sewa (Ambil)
                            </label>
                            <input
                                id="start_at"
                                type="datetime-local"
                                name="start_at"
                                x-model="startAt"
                                class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            />
                        </div>

                        <!-- Date End -->
                        <div>
                            <label for="end_at" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                                Selesai Sewa (Kembali)
                            </label>
                            <input
                                id="end_at"
                                type="datetime-local"
                                name="end_at"
                                x-model="endAt"
                                :min="startAt"
                                class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            />
                        </div>

                        <!-- Submit & Reset -->
                        <div class="flex items-center gap-2">
                            <button
                                type="submit"
                                :disabled="isInvalidRange"
                                :class="isInvalidRange ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-indigo-600 hover:bg-indigo-700'"
                                class="flex-1 min-h-[44px] px-4 py-2.5 rounded-xl text-white font-semibold text-sm transition shadow-sm flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Cek Unit
                            </button>

                            @if(request()->hasAny(['q', 'category', 'start_at', 'end_at', 'min_price', 'max_price']))
                                <a
                                    href="{{ route('home') }}"
                                    class="min-h-[44px] px-3 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 flex items-center justify-center text-sm"
                                    title="Reset Filter"
                                >
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Date Warning if invalid -->
                    <div x-show="isInvalidRange" class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Waktu selesai sewa harus setelah waktu mulai sewa.</span>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Main Catalog Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
        <!-- Category Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-8 no-scrollbar">
            <a
                href="{{ route('home', request()->except('category', 'page')) }}"
                class="min-h-[40px] px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition {{ ! request('category') ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}"
            >
                Semua Kategori
            </a>
            @foreach($categories as $cat)
                <a
                    href="{{ route('home', array_merge(request()->except('page'), ['category' => $cat->slug])) }}"
                    class="min-h-[40px] px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition {{ request('category') === $cat->slug ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}"
                >
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>

        <!-- Filter Status Alert if Date filtered -->
        @if($startAt && $endAt)
            <div class="mb-6 p-4 rounded-2xl bg-indigo-50 border border-indigo-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2.5 text-sm text-indigo-900 font-medium">
                    <svg class="w-5 h-5 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Menampilkan unit yang tersedia untuk periode <strong>{{ format_date_id($startAt) }}</strong> s/d <strong>{{ format_date_id($endAt) }}</strong></span>
                </div>
                <a href="{{ route('home', request()->except(['start_at', 'end_at'])) }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                    Hapus Filter Tanggal
                </a>
            </div>
        @endif

        <!-- Items Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($items as $item)
                <div class="group bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col overflow-hidden">
                    <!-- Image Thumbnail -->
                    <div class="relative aspect-4/3 bg-slate-100 overflow-hidden">
                        <img
                            src="{{ $item->primary_image_url }}"
                            alt="{{ $item->name }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                            loading="lazy"
                        />
                        <div class="absolute top-3 left-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-white/90 text-slate-800 backdrop-blur-xs shadow-xs">
                                {{ $item->category->name }}
                            </span>
                        </div>
                        <div class="absolute top-3 right-3">
                            @if(isset($item->calculated_available_stock))
                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-bold bg-emerald-600 text-white shadow-xs">
                                    Sisa {{ $item->calculated_available_stock }} unit
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-bold bg-slate-900/80 text-white shadow-xs">
                                    Stok: {{ $item->stock }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Item Details -->
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <h3 class="font-bold text-slate-900 group-hover:text-indigo-600 transition leading-snug line-clamp-2">
                                <a href="{{ route('items.show', $item->slug) }}">
                                    {{ $item->name }}
                                </a>
                            </h3>

                            @if($item->specs)
                                <div class="mt-2.5 flex flex-wrap gap-1.5">
                                    @foreach(array_slice($item->specs, 0, 2) as $k => $v)
                                        <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[11px] font-medium truncate max-w-[150px]">
                                            {{ $k }}: {{ $v }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Pricing & CTA -->
                        <div class="pt-3 border-t border-slate-100">
                            <div class="flex items-baseline justify-between mb-3">
                                <div>
                                    <span class="text-xs text-slate-400 block font-medium">Tarif Sewa</span>
                                    <span class="text-lg font-extrabold text-indigo-600">{{ format_rupiah($item->price_per_day) }}</span>
                                    <span class="text-xs text-slate-500">/hari</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-slate-400 block font-medium">Deposit</span>
                                    <span class="text-xs font-semibold text-slate-700">{{ format_rupiah($item->deposit) }}</span>
                                </div>
                            </div>

                            <a
                                href="{{ route('items.show', array_merge(['slug' => $item->slug], request()->only(['start_at', 'end_at']))) }}"
                                class="w-full min-h-[44px] flex items-center justify-center px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-indigo-600 text-white text-sm font-semibold transition shadow-xs"
                            >
                                Detail & Booking &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-white rounded-2xl border border-slate-200">
                    <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Unit Tidak Ditemukan</h3>
                    <p class="mt-1 text-sm text-slate-500 max-w-sm mx-auto">
                        Tidak ada unit rental yang sesuai dengan kata kunci atau filter tanggal yang dipilih.
                    </p>
                    <a href="{{ route('home') }}" class="mt-4 inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold text-indigo-600 hover:bg-indigo-50">
                        Lihat Semua Unit
                    </a>
                </div>
            @endforelse
        </div>

        @if($items->hasPages())
            <div class="mt-10">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
