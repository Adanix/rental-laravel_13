<x-layouts.admin title="Laporan Pendapatan">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Laporan & Analisis Pendapatan</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Periode: {{ $startDate->format('d/m/Y') }} s/d {{ $endDate->format('d/m/Y') }}</p>
            </div>

            <!-- Export CSV Button -->
            <a
                href="{{ route('admin.reports.export', ['start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString()]) }}"
                class="inline-flex items-center gap-2 min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 shadow-2xs transition"
            >
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export CSV Laporan
            </a>
        </div>

        <!-- Date Range Filter Card -->
        <x-card class="p-4 sm:p-5">
            <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                <div>
                    <label for="start_date" class="block text-xs font-semibold text-slate-700 mb-1">Dari Tanggal</label>
                    <input
                        type="date"
                        name="start_date"
                        id="start_date"
                        value="{{ $startDate->toDateString() }}"
                        class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <div>
                    <label for="end_date" class="block text-xs font-semibold text-slate-700 mb-1">Sampai Tanggal</label>
                    <input
                        type="date"
                        name="end_date"
                        id="end_date"
                        value="{{ $endDate->toDateString() }}"
                        class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <x-button type="submit" variant="primary" class="w-full">
                        Terapkan Filter
                    </x-button>
                </div>
            </form>
        </x-card>

        <!-- Financial Summary KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Pendapatan Bersih -->
            <x-card class="p-5 border-emerald-200 bg-emerald-50/20">
                <div class="text-xs font-semibold text-emerald-800 uppercase tracking-wider">Pendapatan Bersih</div>
                <div class="mt-2 text-2xl sm:text-3xl font-extrabold text-emerald-700 font-mono">
                    {{ format_rupiah($netRevenue) }}
                </div>
                <div class="text-[11px] text-emerald-600 mt-1">Sewa + Denda - Diskon</div>
            </x-card>

            <!-- Omset Sewa Kotor -->
            <x-card class="p-5">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Subtotal Sewa Kotor</div>
                <div class="mt-2 text-2xl font-bold text-slate-900 font-mono">
                    {{ format_rupiah($totalRental) }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1">Dari {{ $totalBookingsCount }} pesanan</div>
            </x-card>

            <!-- Diskon -->
            <x-card class="p-5">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Potongan Diskon</div>
                <div class="mt-2 text-2xl font-bold text-slate-700 font-mono">
                    {{ format_rupiah($totalDiscount) }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1">Diskon sewa panjang</div>
            </x-card>

            <!-- Total Denda & Kerusakan -->
            <x-card class="p-5">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Denda & Kerusakan</div>
                <div class="mt-2 text-2xl font-bold text-slate-900 font-mono">
                    {{ format_rupiah($totalLateFee + $totalDamageFee) }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1">Telat: {{ format_rupiah($totalLateFee) }} | Rusak: {{ format_rupiah($totalDamageFee) }}</div>
            </x-card>
        </div>

        <!-- Top Rented Items & Recent Bookings Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Rented Items -->
            <x-card class="p-6 space-y-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    Peringkat Unit Terlaris
                </h3>

                @if($topItems->isEmpty())
                    <p class="text-xs text-slate-400 italic py-4 text-center">Belum ada data unit yang disewa pada periode ini.</p>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($topItems as $index => $item)
                            <div class="py-3 flex items-center justify-between text-xs sm:text-sm">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs {{ $index === 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $index + 1 }}
                                    </span>
                                    <div>
                                        <div class="font-semibold text-slate-900">{{ $item->item->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $item->item->category->name }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-slate-800">{{ $item->total_qty }}x disewa</div>
                                    <div class="text-[11px] font-mono text-slate-500">{{ format_rupiah($item->total_revenue) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>

            <!-- Recent Completed Rentals in Period -->
            <x-card class="p-6 space-y-4">
                <h3 class="text-sm font-bold text-slate-900">Pesanan Terbaru dalam Periode</h3>

                @if($recentBookings->isEmpty())
                    <p class="text-xs text-slate-400 italic py-4 text-center">Tidak ada transaksi dalam periode ini.</p>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($recentBookings as $b)
                            <div class="py-3 flex items-center justify-between text-xs">
                                <div>
                                    <a href="{{ route('admin.bookings.show', $b->id) }}" class="font-mono font-bold text-indigo-600 hover:underline">
                                        {{ $b->code }}
                                    </a>
                                    <span class="text-slate-800 font-semibold block">{{ $b->user->name }}</span>
                                    <span class="text-slate-400 block text-[11px]">{{ $b->start_at->format('d/m/Y') }} &bull; {{ $b->duration_days }} hari</span>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $b->status->badgeClass() }}">
                                        {{ $b->status->label() }}
                                    </span>
                                    <div class="font-mono font-bold text-slate-900 mt-1">
                                        {{ format_rupiah($b->grand_total + $b->late_fee + $b->damage_fee) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-layouts.admin>
