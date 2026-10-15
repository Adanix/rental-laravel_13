<x-layouts.app title="Pesanan Saya">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Pesanan Saya</h1>
                <p class="text-sm text-slate-500 mt-1">Pantau status pemesanan, jadwal serah-terima, dan faktur rental Anda.</p>
            </div>
            <div>
                <a
                    href="{{ route('home') }}"
                    class="inline-flex items-center justify-center min-h-[44px] px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm transition shadow-xs"
                >
                    + Sewa Unit Baru
                </a>
            </div>
        </div>

        <!-- Filter Status Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 no-scrollbar">
            <a
                href="{{ route('my-bookings.index') }}"
                class="min-h-[38px] px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ ! request('status') ? 'bg-indigo-600 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}"
            >
                Semua
            </a>
            @foreach(\App\Enums\BookingStatus::cases() as $st)
                <a
                    href="{{ route('my-bookings.index', ['status' => $st->value]) }}"
                    class="min-h-[38px] px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ request('status') === $st->value ? 'bg-indigo-600 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}"
                >
                    {{ $st->label() }}
                </a>
            @endforeach
        </div>

        <!-- Bookings List -->
        <div class="space-y-4">
            @forelse($bookings as $booking)
                <x-card class="p-5 sm:p-6 hover:border-indigo-200 transition">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-sm font-bold text-slate-900 bg-slate-100 px-2.5 py-1 rounded-lg">
                                {{ $booking->code }}
                            </span>
                            <span class="text-xs text-slate-400">
                                Dibuat {{ format_date_id($booking->created_at) }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($booking->is_overdue)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-600 text-white shadow-xs animate-pulse">
                                    Terlambat / Overdue
                                </span>
                            @endif
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $booking->status->badgeClass() }}">
                                {{ $booking->status->label() }}
                            </span>
                        </div>
                    </div>

                    <!-- Items Preview -->
                    <div class="py-4 space-y-3">
                        @foreach($booking->items as $bItem)
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img
                                        src="{{ $bItem->item->primary_image_url }}"
                                        alt="{{ $bItem->item->name }}"
                                        class="w-12 h-12 rounded-xl object-cover shrink-0 border border-slate-200"
                                    />
                                    <div class="min-w-0">
                                        <h4 class="font-semibold text-slate-900 text-sm truncate">{{ $bItem->item->name }}</h4>
                                        <p class="text-xs text-slate-500">
                                            {{ $bItem->qty }} unit &bull; {{ format_rupiah($bItem->unit_price) }}/hari
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-bold text-slate-800 text-sm">{{ format_rupiah($bItem->subtotal) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Footer Details & Link -->
                    <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="text-xs text-slate-600 space-y-1">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Periode Sewa: <strong>{{ format_date_id($booking->start_at) }}</strong> s/d <strong>{{ format_date_id($booking->end_at) }}</strong> ({{ $booking->duration_days }} hari)</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-4">
                            <div class="text-right">
                                <span class="text-[11px] text-slate-400 block font-medium">Total Biaya</span>
                                <span class="text-base font-extrabold text-indigo-700">{{ format_rupiah($booking->grand_total) }}</span>
                            </div>

                            <a
                                href="{{ route('my-bookings.show', $booking->code) }}"
                                class="min-h-[40px] px-4 py-2 rounded-xl bg-slate-900 hover:bg-indigo-600 text-white text-xs font-bold transition flex items-center gap-1"
                            >
                                Detail Pesanan &rarr;
                            </a>
                        </div>
                    </div>
                </x-card>
            @empty
                <div class="text-center py-16 bg-white rounded-2xl border border-slate-200">
                    <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Belum Ada Pesanan</h3>
                    <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                        Anda belum memiliki riwayat pemesanan rental.
                    </p>
                    <a href="{{ route('home') }}" class="mt-4 inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700">
                        Cari Unit Rental Sekarang
                    </a>
                </div>
            @endforelse
        </div>

        @if($bookings->hasPages())
            <div class="mt-8">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
