<x-layouts.admin title="Dashboard Operasional">
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Dashboard Operasional</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Ringkasan aktivitas hari ini: {{ format_date_id(now()) }}.</p>
            </div>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('admin.bookings.walk-in') }}"
                    class="inline-flex items-center gap-1.5 min-h-[44px] px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs sm:text-sm font-semibold hover:bg-indigo-700 shadow-sm transition"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Pesanan Walk-in
                </a>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Pickup Hari Ini -->
            <a href="{{ route('admin.bookings.index', ['status' => 'confirmed']) }}" class="p-5 rounded-2xl bg-white border border-slate-200 shadow-2xs hover:border-indigo-300 hover:shadow-sm transition block group">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider group-hover:text-indigo-600 transition">Jadwal Pickup</div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-3xl font-extrabold text-slate-900 font-mono">{{ $todayPickupsCount }}</span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">Hari ini</span>
                </div>
            </a>

            <!-- Return Hari Ini -->
            <a href="{{ route('admin.bookings.index', ['status' => 'picked_up']) }}" class="p-5 rounded-2xl bg-white border border-slate-200 shadow-2xs hover:border-indigo-300 hover:shadow-sm transition block group">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider group-hover:text-indigo-600 transition">Jadwal Return</div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-3xl font-extrabold text-slate-900 font-mono">{{ $todayReturnsCount }}</span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">Hari ini</span>
                </div>
            </a>

            <!-- Sedang Disewa -->
            <a href="{{ route('admin.bookings.index', ['status' => 'picked_up']) }}" class="p-5 rounded-2xl bg-white border border-slate-200 shadow-2xs hover:border-indigo-300 hover:shadow-sm transition block group">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider group-hover:text-indigo-600 transition">Sedang Disewa</div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-3xl font-extrabold text-slate-900 font-mono">{{ $activeRentalsCount }}</span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">Aktif</span>
                </div>
            </a>

            <!-- Overdue -->
            <a href="{{ route('admin.bookings.index', ['overdue' => 1]) }}" class="p-5 rounded-2xl bg-white border {{ $overdueCount > 0 ? 'border-rose-300 bg-rose-50/20' : 'border-slate-200' }} shadow-2xs hover:border-rose-400 hover:shadow-sm transition block group">
                <div class="text-xs font-semibold {{ $overdueCount > 0 ? 'text-rose-600' : 'text-slate-400' }} uppercase tracking-wider">Terlambat (Overdue)</div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-3xl font-extrabold {{ $overdueCount > 0 ? 'text-rose-600' : 'text-slate-900' }} font-mono">{{ $overdueCount }}</span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-700">Perhatian</span>
                </div>
            </a>

            <!-- Pending Verifikasi Bayar -->
            <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" class="p-5 rounded-2xl bg-white border border-slate-200 shadow-2xs hover:border-amber-300 hover:shadow-sm transition block group">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider group-hover:text-amber-600 transition">Verifikasi Bayar</div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-3xl font-extrabold text-slate-900 font-mono">{{ $pendingPaymentsCount }}</span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">Menunggu</span>
                </div>
            </a>
        </div>

        <!-- Overdue & Pending Payment Alerts Section -->
        @if($overdueCount > 0 || $pendingPaymentsCount > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Overdue Bookings Alert List -->
                @if($overdueCount > 0)
                    <x-card class="p-5 border-rose-200 bg-rose-50/10 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-rose-700 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Unit Terlambat Kembali (Overdue)
                            </h3>
                            <a href="{{ route('admin.bookings.index', ['overdue' => 1]) }}" class="text-xs font-semibold text-rose-600 hover:underline">Lihat Semua</a>
                        </div>
                        <div class="divide-y divide-rose-100">
                            @foreach($overdueBookings as $b)
                                <div class="py-2.5 flex items-center justify-between text-xs">
                                    <div>
                                        <a href="{{ route('admin.bookings.show', $b->id) }}" class="font-mono font-bold text-rose-700 hover:underline">
                                            {{ $b->code }}
                                        </a>
                                        <span class="text-slate-600 block">{{ $b->user->name }} ({{ $b->user->phone }})</span>
                                        <span class="text-slate-400 block text-[11px]">Batas: {{ $b->end_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <a href="{{ route('admin.bookings.show', $b->id) }}" class="px-2.5 py-1 rounded-lg bg-rose-600 text-white font-semibold text-[11px] hover:bg-rose-700">
                                        Proses Kembali
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </x-card>
                @endif

                <!-- Pending Payments Alert List -->
                @if($pendingPaymentsCount > 0)
                    <x-card class="p-5 border-amber-200 bg-amber-50/10 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-amber-800 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Bukti Pembayaran Menunggu Verifikasi
                            </h3>
                            <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" class="text-xs font-semibold text-amber-700 hover:underline">Lihat Semua</a>
                        </div>
                        <div class="divide-y divide-amber-100">
                            @foreach($pendingPayments as $p)
                                <div class="py-2.5 flex items-center justify-between text-xs">
                                    <div>
                                        <a href="{{ route('admin.bookings.show', $p->booking->id) }}" class="font-mono font-bold text-indigo-600 hover:underline">
                                            {{ $p->booking->code }}
                                        </a>
                                        <span class="text-slate-600 block">{{ $p->booking->user->name }} &bull; {{ format_rupiah($p->amount) }}</span>
                                        <span class="text-slate-400 block text-[11px]">{{ $p->method->label() }} &bull; {{ format_date_id($p->created_at) }}</span>
                                    </div>
                                    <a href="{{ route('admin.bookings.show', $p->booking->id) }}" class="px-2.5 py-1 rounded-lg bg-emerald-600 text-white font-semibold text-[11px] hover:bg-emerald-700">
                                        Verifikasi
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </x-card>
                @endif
            </div>
        @endif

        <!-- Today's Operations Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Today Pickup Schedule -->
            <x-card class="p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Jadwal Pengambilan (Pickup) Hari Ini
                    </h3>
                    <span class="text-xs font-bold text-slate-400">{{ $todayPickupsCount }} Unit</span>
                </div>

                @if($todayPickups->isEmpty())
                    <p class="text-xs text-slate-400 italic py-4 text-center">Tidak ada jadwal pickup hari ini.</p>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($todayPickups as $b)
                            <div class="py-3 flex items-center justify-between text-xs">
                                <div>
                                    <a href="{{ route('admin.bookings.show', $b->id) }}" class="font-mono font-bold text-indigo-600 hover:underline">
                                        {{ $b->code }}
                                    </a>
                                    <span class="text-slate-800 font-semibold block">{{ $b->user->name }}</span>
                                    <span class="text-slate-500 block text-[11px]">Jam Ambil: {{ $b->start_at->format('H:i') }} WIB</span>
                                </div>
                                <a href="{{ route('admin.bookings.show', $b->id) }}" class="px-3 py-1.5 rounded-lg border border-indigo-200 text-indigo-700 font-semibold hover:bg-indigo-50">
                                    Serah-Terima
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>

            <!-- Today Return Schedule -->
            <x-card class="p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Jadwal Pengembalian (Return) Hari Ini
                    </h3>
                    <span class="text-xs font-bold text-slate-400">{{ $todayReturnsCount }} Unit</span>
                </div>

                @if($todayReturns->isEmpty())
                    <p class="text-xs text-slate-400 italic py-4 text-center">Tidak ada jadwal pengembalian hari ini.</p>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($todayReturns as $b)
                            <div class="py-3 flex items-center justify-between text-xs">
                                <div>
                                    <a href="{{ route('admin.bookings.show', $b->id) }}" class="font-mono font-bold text-indigo-600 hover:underline">
                                        {{ $b->code }}
                                    </a>
                                    <span class="text-slate-800 font-semibold block">{{ $b->user->name }}</span>
                                    <span class="text-slate-500 block text-[11px]">Batas Kembali: {{ $b->end_at->format('H:i') }} WIB</span>
                                </div>
                                <a href="{{ route('admin.bookings.show', $b->id) }}" class="px-3 py-1.5 rounded-lg border border-emerald-200 text-emerald-700 font-semibold hover:bg-emerald-50">
                                    Proses Kembali
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-layouts.admin>
