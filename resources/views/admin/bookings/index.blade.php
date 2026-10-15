<x-layouts.admin title="Manajemen Pesanan">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Pesanan</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola seluruh transaksi sewa, pembayaran, dan serah-terima unit.</p>
            </div>
            <a
                href="{{ route('admin.bookings.walk-in') }}"
                class="inline-flex items-center justify-center gap-2 min-h-[44px] px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-700 shadow-sm transition"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat Pesanan Walk-in
            </a>
        </div>

        <!-- Filter Card -->
        <x-card class="p-4 sm:p-5">
            <form method="GET" action="{{ route('admin.bookings.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="search" class="block text-xs font-semibold text-slate-700 mb-1">Cari Kode / Pelanggan</label>
                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ request('search') }}"
                        placeholder="Contoh: RNT- atau Budi"
                        class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <div>
                    <label for="status" class="block text-xs font-semibold text-slate-700 mb-1">Status Pesanan</label>
                    <select
                        name="status"
                        id="status"
                        class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">Semua Status</option>
                        @foreach(\App\Enums\BookingStatus::cases() as $st)
                            <option value="{{ $st->value }}" @selected(request('status') === $st->value)>{{ $st->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="date" class="block text-xs font-semibold text-slate-700 mb-1">Filter Tanggal</label>
                    <input
                        type="date"
                        name="date"
                        id="date"
                        value="{{ request('date') }}"
                        class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <x-button type="submit" variant="primary" class="flex-1">
                        Filter
                    </x-button>
                    @if(request()->hasAny(['search', 'status', 'date', 'overdue']))
                        <a
                            href="{{ route('admin.bookings.index') }}"
                            class="inline-flex items-center justify-center min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-slate-500 mr-1">Filter Cepat:</span>
                <a
                    href="{{ route('admin.bookings.index', ['status' => 'pending']) }}"
                    class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ request('status') === 'pending' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                >
                    Menunggu Verifikasi Bayar
                </a>
                <a
                    href="{{ route('admin.bookings.index', ['status' => 'confirmed']) }}"
                    class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ request('status') === 'confirmed' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                >
                    Siap Diambil (Confirmed)
                </a>
                <a
                    href="{{ route('admin.bookings.index', ['status' => 'picked_up']) }}"
                    class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ request('status') === 'picked_up' && !request('overdue') ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                >
                    Sedang Disewa
                </a>
                <a
                    href="{{ route('admin.bookings.index', ['overdue' => 1]) }}"
                    class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ request('overdue') ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}"
                >
                    ⚠️ Terlambat (Overdue)
                </a>
            </div>
        </x-card>

        <!-- Bookings Table -->
        <x-card class="overflow-hidden">
            @if($bookings->isEmpty())
                <div class="p-12 text-center">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="text-sm font-semibold text-slate-700">Tidak ada data pesanan rental yang ditemukan.</p>
                    <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau filter status.</p>
                </div>
            @else
                <x-table>
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-3.5">Kode / Tanggal</th>
                            <th class="px-4 py-3.5">Pelanggan</th>
                            <th class="px-4 py-3.5">Unit Sewa</th>
                            <th class="px-4 py-3.5">Jadwal Rental</th>
                            <th class="px-4 py-3.5 text-right">Total Biaya</th>
                            <th class="px-4 py-3.5 text-center">Status</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        @foreach($bookings as $booking)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3.5">
                                    <a href="{{ route('admin.bookings.show', $booking->id) }}" class="font-mono font-bold text-indigo-600 hover:text-indigo-800">
                                        {{ $booking->code }}
                                    </a>
                                    <span class="block text-[11px] text-slate-400 mt-0.5">
                                        {{ $booking->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-semibold text-slate-900">{{ $booking->user->name }}</div>
                                    <div class="flex items-center gap-1 text-[11px] text-slate-500">
                                        <span>{{ $booking->user->phone }}</span>
                                        @php
                                            $cPhone = preg_replace('/[^0-9]/', '', $booking->user->phone);
                                            if (str_starts_with($cPhone, '0')) {
                                                $cPhone = '62' . substr($cPhone, 1);
                                            }
                                        @endphp
                                        <a href="https://wa.me/{{ $cPhone }}" target="_blank" class="text-emerald-600 hover:underline">
                                            (WA)
                                        </a>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    @foreach($booking->items as $bItem)
                                        <div class="font-medium text-slate-800">
                                            {{ $bItem->item->name }} <span class="text-xs text-slate-500">({{ $bItem->qty }}x)</span>
                                        </div>
                                    @endforeach
                                </td>
                                <td class="px-4 py-3.5 text-xs text-slate-600">
                                    <div><strong class="text-slate-800">Ambil:</strong> {{ $booking->start_at->format('d/m/Y H:i') }}</div>
                                    <div><strong class="text-slate-800">Kembali:</strong> {{ $booking->end_at->format('d/m/Y H:i') }}</div>
                                    <div class="text-[11px] text-slate-400">Durasi: {{ $booking->duration_days }} hari</div>
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono">
                                    <div class="font-bold text-slate-900">
                                        {{ format_rupiah($booking->grand_total + $booking->late_fee + $booking->damage_fee) }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        Deposit: {{ format_rupiah($booking->deposit_total) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $booking->status->badgeClass() }}">
                                        {{ $booking->status->label() }}
                                    </span>
                                    @if($booking->is_overdue)
                                        <span class="block mt-1 text-[10px] font-bold text-rose-600 animate-pulse">
                                            ⚠️ OVERDUE
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <a
                                        href="{{ route('admin.bookings.show', $booking->id) }}"
                                        class="inline-flex items-center justify-center min-h-[36px] px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-indigo-600 shadow-2xs transition"
                                    >
                                        Kelola
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-table>
            @endif

            @if($bookings->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $bookings->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.admin>
