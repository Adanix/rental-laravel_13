<x-layouts.admin title="Kalender Ketersediaan & Jadwal">
    <div class="space-y-6">
        <!-- Header & Controls -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Kalender Ketersediaan & Jadwal</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Matriks jadwal sewa unit dan blackout selama 14 hari.</p>
            </div>

            <!-- Date Navigator -->
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('admin.calendar', ['start_date' => $startDate->copy()->subDays(14)->toDateString(), 'category_id' => request('category_id')]) }}"
                    class="min-h-[38px] px-3 py-1.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition"
                >
                    &larr; 2 Minggu Lalu
                </a>
                <a
                    href="{{ route('admin.calendar', ['start_date' => today()->startOfWeek()->toDateString(), 'category_id' => request('category_id')]) }}"
                    class="min-h-[38px] px-3 py-1.5 rounded-xl border border-indigo-200 bg-indigo-50 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition"
                >
                    Minggu Ini
                </a>
                <a
                    href="{{ route('admin.calendar', ['start_date' => $startDate->copy()->addDays(14)->toDateString(), 'category_id' => request('category_id')]) }}"
                    class="min-h-[38px] px-3 py-1.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition"
                >
                    2 Minggu Depan &rarr;
                </a>
            </div>
        </div>

        <!-- Filter & Legend Bar -->
        <x-card class="p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <form method="GET" action="{{ route('admin.calendar') }}" class="flex items-center gap-3">
                <input type="hidden" name="start_date" value="{{ $startDate->toDateString() }}">
                <select
                    name="category_id"
                    onchange="this.form.submit()"
                    class="min-h-[40px] px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">Semua Kategori Unit</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </form>

            <!-- Legend -->
            <div class="flex flex-wrap items-center gap-3 text-xs">
                <span class="flex items-center gap-1.5 text-slate-600">
                    <span class="w-3 h-3 rounded bg-blue-500 inline-block"></span> Terkonfirmasi
                </span>
                <span class="flex items-center gap-1.5 text-slate-600">
                    <span class="w-3 h-3 rounded bg-indigo-600 inline-block"></span> Sedang Disewa
                </span>
                <span class="flex items-center gap-1.5 text-slate-600">
                    <span class="w-3 h-3 rounded bg-amber-400 inline-block"></span> Menunggu Bayar
                </span>
                <span class="flex items-center gap-1.5 text-slate-600">
                    <span class="w-3 h-3 rounded bg-rose-500 inline-block"></span> Blackout / Servis
                </span>
                <span class="flex items-center gap-1.5 text-slate-600">
                    <span class="w-3 h-3 rounded bg-emerald-100 border border-emerald-300 inline-block"></span> Tersedia
                </span>
            </div>
        </x-card>

        <!-- Matrix Calendar Table -->
        <x-card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[900px]">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600">
                            <th class="p-3 sticky left-0 bg-slate-50 z-10 w-48 shadow-[1px_0_0_0_#e2e8f0]">
                                Unit Rental
                            </th>
                            @foreach($dates as $d)
                                @php
                                    $isToday = $d->isToday();
                                    $isWeekend = $d->isWeekend();
                                @endphp
                                <th class="p-2 text-center border-l border-slate-200 min-w-[70px] {{ $isToday ? 'bg-indigo-50/70 text-indigo-700 font-extrabold' : ($isWeekend ? 'bg-slate-100/50' : '') }}">
                                    <div class="uppercase text-[10px] text-slate-400">{{ $d->translatedFormat('D') }}</div>
                                    <div class="text-xs">{{ $d->format('d/m') }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach($items as $item)
                            <tr class="hover:bg-slate-50/40">
                                <td class="p-3 font-semibold text-slate-900 sticky left-0 bg-white z-10 shadow-[1px_0_0_0_#e2e8f0]">
                                    <div class="truncate max-w-[180px]">{{ $item->name }}</div>
                                    <div class="text-[11px] text-slate-400 font-normal">Stok: {{ $item->stock }} &bull; {{ $item->category->name }}</div>
                                </td>

                                @foreach($dates as $d)
                                    @php
                                        $dStart = $d->copy()->startOfDay();
                                        $dEnd = $d->copy()->endOfDay();

                                        // Check blackouts
                                        $hasBlackout = $item->blackouts->contains(function ($bo) use ($dStart, $dEnd) {
                                            return $bo->start_at <= $dEnd && $bo->end_at >= $dStart;
                                        });

                                        // Find bookings on this day
                                        $dayBookings = $item->bookingItems->filter(function ($bi) use ($dStart, $dEnd) {
                                            $b = $bi->booking;
                                            return $b && $b->start_at <= $dEnd && $b->end_at >= $dStart;
                                        });

                                        $bookedQty = $dayBookings->sum('qty');
                                        $remainingStock = max(0, $item->stock - $bookedQty);
                                        $isToday = $d->isToday();
                                    @endphp

                                    <td class="p-1.5 border-l border-slate-100 text-center align-top {{ $isToday ? 'bg-indigo-50/20' : '' }}">
                                        @if($hasBlackout)
                                            <div class="p-1 rounded bg-rose-100 text-rose-800 text-[10px] font-bold" title="Blackout / Servis">
                                                Blokir
                                            </div>
                                        @elseif($dayBookings->isNotEmpty())
                                            <div class="space-y-1">
                                                @foreach($dayBookings as $bi)
                                                    @php
                                                        $b = $bi->booking;
                                                        $bgClass = match($b->status->value) {
                                                            'confirmed' => 'bg-blue-500 text-white',
                                                            'picked_up' => 'bg-indigo-600 text-white',
                                                            default => 'bg-amber-400 text-amber-950',
                                                        };
                                                    @endphp
                                                    <a
                                                        href="{{ route('admin.bookings.show', $b->id) }}"
                                                        class="block p-0.5 rounded text-[10px] font-bold truncate {{ $bgClass }} hover:opacity-90 transition"
                                                        title="{{ $b->code }} - {{ $b->user->name }} ({{ $bi->qty }}x)"
                                                    >
                                                        {{ $bi->qty }}x
                                                    </a>
                                                @endforeach
                                                @if($remainingStock > 0)
                                                    <span class="text-[9px] text-slate-400 block font-mono">sisa {{ $remainingStock }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <div class="h-6 flex items-center justify-center text-emerald-500 text-[10px] font-mono">
                                                ✓ {{ $item->stock }}
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-layouts.admin>
