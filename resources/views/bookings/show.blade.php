<x-layouts.app :title="'Pesanan ' . $booking->code">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-8">
        <!-- Top Bar Navigation -->
        <div class="flex items-center justify-between">
            <a href="{{ route('my-bookings.index') }}" class="text-xs sm:text-sm font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                &larr; Kembali ke Daftar Pesanan
            </a>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('my-bookings.invoice', $booking->code) }}"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 min-h-[36px] px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-2xs transition"
                >
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak Invoice
                </a>

                @php
                    $storePhone = preg_replace('/[^0-9]/', '', \App\Models\Setting::get('store_phone', '081234567890'));
                    if (str_starts_with($storePhone, '0')) {
                        $storePhone = '62' . substr($storePhone, 1);
                    }
                    $waText = urlencode("Halo Admin RentalHub, saya ingin menanyakan pesanan rental dengan kode {$booking->code} atas nama {$booking->user->name}.");
                @endphp
                <a
                    href="https://wa.me/{{ $storePhone }}?text={{ $waText }}"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-1.5 min-h-[36px] px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 shadow-2xs transition"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.044c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z" />
                    </svg>
                    WhatsApp Admin
                </a>
            </div>
        </div>

        <!-- Booking Header Card -->
        <x-card class="p-6 sm:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-100">
                <div>
                    <span class="text-xs text-slate-400 uppercase tracking-wider font-semibold block mb-1">Kode Pesanan</span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-mono tracking-tight">{{ $booking->code }}</h1>
                    <p class="text-xs text-slate-500 mt-1">Dipesan pada {{ format_date_id($booking->created_at) }}</p>
                </div>

                <div class="flex flex-col sm:items-end gap-2">
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold border {{ $booking->status->badgeClass() }}">
                        {{ $booking->status->label() }}
                    </span>
                    @if($booking->is_overdue)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-600 text-white animate-pulse">
                            ⚠️ Terlambat Pengembalian (Overdue)
                        </span>
                    @endif
                    @if($booking->status->value === 'pending' && $booking->expires_at)
                        <span class="text-xs text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                            Batas Pembayaran: <strong>{{ format_date_id($booking->expires_at) }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <!-- Visual Status Timeline Component -->
            @php
                $statusOrder = [
                    'pending' => 1,
                    'confirmed' => 2,
                    'picked_up' => 3,
                    'returned' => 4,
                ];
                $currentStep = $statusOrder[$booking->status->value] ?? 0;
                $isSpecial = in_array($booking->status->value, ['cancelled', 'rejected', 'expired']);
            @endphp

            @if(! $isSpecial)
                <div class="py-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-6">Status Perjalanan Sewa</h3>
                    <div class="grid grid-cols-4 gap-2 text-center relative">
                        <!-- Step 1 -->
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm mb-2 {{ $currentStep >= 1 ? 'bg-indigo-600 text-white ring-4 ring-indigo-100' : 'bg-slate-100 text-slate-400' }}">
                                1
                            </div>
                            <span class="text-xs font-bold {{ $currentStep >= 1 ? 'text-indigo-900' : 'text-slate-400' }}">Dibuat</span>
                            <span class="text-[11px] text-slate-400 hidden sm:block">Menunggu Bayar</span>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm mb-2 {{ $currentStep >= 2 ? 'bg-indigo-600 text-white ring-4 ring-indigo-100' : 'bg-slate-100 text-slate-400' }}">
                                2
                            </div>
                            <span class="text-xs font-bold {{ $currentStep >= 2 ? 'text-indigo-900' : 'text-slate-400' }}">Terkonfirmasi</span>
                            <span class="text-[11px] text-slate-400 hidden sm:block">Siap Diambil</span>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm mb-2 {{ $currentStep >= 3 ? 'bg-indigo-600 text-white ring-4 ring-indigo-100' : 'bg-slate-100 text-slate-400' }}">
                                3
                            </div>
                            <span class="text-xs font-bold {{ $currentStep >= 3 ? 'text-indigo-900' : 'text-slate-400' }}">Disewa</span>
                            <span class="text-[11px] text-slate-400 hidden sm:block">Sedang Digunakan</span>
                        </div>

                        <!-- Step 4 -->
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm mb-2 {{ $currentStep >= 4 ? 'bg-emerald-600 text-white ring-4 ring-emerald-100' : 'bg-slate-100 text-slate-400' }}">
                                4
                            </div>
                            <span class="text-xs font-bold {{ $currentStep >= 4 ? 'text-emerald-900' : 'text-slate-400' }}">Selesai</span>
                            <span class="text-[11px] text-slate-400 hidden sm:block">Unit Dikembalikan</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center">
                    <p class="text-sm font-semibold text-slate-700">
                        Pesanan ini berstatus <strong>{{ $booking->status->label() }}</strong>.
                    </p>
                </div>
            @endif

            <!-- Rental Period & Location Info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm">
                <div>
                    <span class="text-slate-400 block font-medium">Jadwal Pengambilan (Pickup):</span>
                    <strong class="text-slate-900 font-semibold">{{ format_date_id($booking->start_at) }}</strong>
                    @if($booking->picked_up_at)
                        <span class="text-emerald-700 block text-xs mt-0.5">Diserahkan: {{ format_date_id($booking->picked_up_at) }}</span>
                    @endif
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Jadwal Pengembalian (Return):</span>
                    <strong class="text-slate-900 font-semibold">{{ format_date_id($booking->end_at) }}</strong>
                    @if($booking->returned_at)
                        <span class="text-emerald-700 block text-xs mt-0.5">Dikembalikan: {{ format_date_id($booking->returned_at) }}</span>
                    @endif
                </div>
            </div>

            <!-- Items Table -->
            <div class="space-y-3">
                <h3 class="text-sm font-bold text-slate-900">Rincian Barang yang Disewa</h3>
                <x-table>
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-3">Nama Unit</th>
                            <th class="px-4 py-3 text-center">Qty</th>
                            <th class="px-4 py-3 text-right">Tarif / Hari</th>
                            <th class="px-4 py-3 text-right">Deposit</th>
                            <th class="px-4 py-3 text-right">Subtotal Sewa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        @foreach($booking->items as $item)
                            <tr>
                                <td class="px-4 py-3.5 font-semibold text-slate-900">
                                    {{ $item->item->name }}
                                </td>
                                <td class="px-4 py-3.5 text-center font-bold">
                                    {{ $item->qty }}
                                </td>
                                <td class="px-4 py-3.5 text-right text-slate-600">
                                    {{ format_rupiah($item->unit_price) }}
                                </td>
                                <td class="px-4 py-3.5 text-right text-slate-600">
                                    {{ format_rupiah($item->unit_deposit * $item->qty) }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-bold text-slate-900">
                                    {{ format_rupiah($item->subtotal) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-table>
            </div>

            <!-- Financial Breakdown Table -->
            <div class="border-t border-slate-100 pt-4 flex flex-col sm:items-end">
                <div class="w-full sm:w-80 space-y-2 text-xs sm:text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal Sewa ({{ $booking->duration_days }} hari):</span>
                        <span class="font-semibold text-slate-900">{{ format_rupiah($booking->subtotal_rental + $booking->discount_amount) }}</span>
                    </div>

                    @if($booking->discount_amount > 0)
                        <div class="flex justify-between text-emerald-700">
                            <span>Diskon Sewa Panjang:</span>
                            <span class="font-bold">- {{ format_rupiah($booking->discount_amount) }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between text-slate-600">
                        <span>Total Deposit Jaminan:</span>
                        <span class="font-semibold text-slate-900">{{ format_rupiah($booking->deposit_total) }}</span>
                    </div>

                    @if($booking->late_fee > 0)
                        <div class="flex justify-between text-rose-600">
                            <span>Denda Keterlambatan:</span>
                            <span class="font-bold">+ {{ format_rupiah($booking->late_fee) }}</span>
                        </div>
                    @endif

                    @if($booking->damage_fee > 0)
                        <div class="flex justify-between text-rose-600">
                            <span>Biaya Kerusakan:</span>
                            <span class="font-bold">+ {{ format_rupiah($booking->damage_fee) }}</span>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-slate-200 flex justify-between items-baseline text-base sm:text-lg font-extrabold text-indigo-700">
                        <span>Total Akhir:</span>
                        <span>{{ format_rupiah($booking->grand_total + $booking->late_fee + $booking->damage_fee) }}</span>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Payment History Card (if any payments exist) -->
        @if($booking->payments->isNotEmpty())
            <x-card class="p-6 sm:p-8 space-y-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Riwayat Pembayaran & Pengembalian Dana
                </h3>
                <div class="space-y-3">
                    @foreach($booking->payments as $payment)
                        <div class="p-3.5 rounded-xl border {{ $payment->status->value === 'verified' ? 'border-emerald-200 bg-emerald-50/30' : ($payment->status->value === 'rejected' ? 'border-rose-200 bg-rose-50/30' : 'border-slate-200 bg-white') }} flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs sm:text-sm">
                            <div>
                                <div class="flex items-center gap-2 font-semibold text-slate-900">
                                    <span>{{ $payment->type->label() }}</span>
                                    <span class="text-slate-400">&bull;</span>
                                    <span class="font-normal text-slate-600 uppercase">{{ $payment->method->label() }}</span>
                                </div>
                                <div class="text-xs text-slate-500 mt-1">
                                    Nominal: <strong class="text-slate-800">{{ format_rupiah($payment->amount) }}</strong> &bull; {{ format_date_id($payment->created_at) }}
                                </div>
                                @if($payment->notes)
                                    <div class="text-xs text-slate-600 mt-1 italic">
                                        Catatan: {{ $payment->notes }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 sm:self-center">
                                @if($payment->proof_path)
                                    <a href="{{ Storage::url($payment->proof_path) }}" target="_blank" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 underline">
                                        Lihat Bukti
                                    </a>
                                @endif
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $payment->status->badgeClass() }}">
                                    {{ $payment->status->label() }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endif

        <!-- Payment Instructions & Upload Section (for PENDING orders) -->
        @if($booking->status->value === 'pending')
            <x-card class="p-6 sm:p-8 space-y-6 border-amber-200 bg-amber-50/20">
                <div class="border-b border-amber-200 pb-3">
                    <h3 class="text-lg font-bold text-slate-900">Petunjuk Pembayaran</h3>
                    <p class="text-xs text-slate-600 mt-1">
                        Selesaikan pembayaran sejumlah <strong class="text-indigo-700 font-extrabold">{{ format_rupiah($booking->grand_total) }}</strong> sebelum batas waktu berakhir.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Transfer Bank -->
                    <div class="p-4 rounded-xl bg-white border border-slate-200 space-y-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700">
                            Transfer Bank Manual
                        </span>
                        <div>
                            <div class="text-xs text-slate-400">Bank Tujuan:</div>
                            <div class="font-bold text-slate-800 text-sm">{{ \App\Models\Setting::get('bank_name', 'BCA') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400">Nomor Rekening:</div>
                            <div class="font-mono text-base font-bold text-indigo-600 select-all">{{ \App\Models\Setting::get('bank_account_number', '8830123456') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400">Atas Nama:</div>
                            <div class="font-semibold text-slate-800 text-xs">{{ \App\Models\Setting::get('bank_account_holder', 'PT RentalHub Nusantara') }}</div>
                        </div>
                    </div>

                    <!-- QRIS Statis -->
                    <div class="p-4 rounded-xl bg-white border border-slate-200 flex flex-col items-center justify-center text-center space-y-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700">
                            QRIS (Semua E-Wallet / M-Banking)
                        </span>
                        @if($qrisUrl = \App\Models\Setting::get('qris_image'))
                            <img src="{{ $qrisUrl }}" alt="QRIS" class="w-36 h-36 object-contain border border-slate-100 rounded-lg p-1" />
                        @endif
                        <span class="text-[11px] text-slate-400">Pindai QRIS untuk membayar</span>
                    </div>
                </div>

                <!-- Form Upload Bukti Pembayaran -->
                <div class="pt-4 border-t border-amber-200">
                    <form method="POST" action="{{ route('my-bookings.payment', $booking->code) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <h4 class="text-sm font-bold text-slate-900">Sudah Melakukan Pembayaran? Unggah Bukti Transfer di Sini</h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="method" class="block text-xs font-semibold text-slate-700 mb-1">Metode yang Digunakan</label>
                                <select name="method" id="method" required class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm">
                                    <option value="transfer">Transfer Bank Manual</option>
                                    <option value="qris">QRIS</option>
                                    <option value="cash">Tunai (Di Tempat)</option>
                                </select>
                            </div>

                            <div>
                                <label for="proof" class="block text-xs font-semibold text-slate-700 mb-1">Foto / Screenshot Bukti Transfer</label>
                                <input type="file" name="proof" id="proof" accept="image/*" required class="w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-700 file:font-semibold cursor-pointer" />
                            </div>
                        </div>

                        <div>
                            <label for="payment_notes" class="block text-xs font-semibold text-slate-700 mb-1">Catatan Pembayaran (Nama Pengirim / Bank Asal)</label>
                            <input type="text" name="notes" id="payment_notes" placeholder="Contoh: Transfer dari BCA a/n Budi Pratama" class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm" />
                        </div>

                        <div class="flex justify-end">
                            <x-button type="submit" variant="primary">
                                Kirim Bukti Pembayaran
                            </x-button>
                        </div>
                    </form>
                </div>
            </x-card>
        @endif

        <!-- Customer Cancellation Button (if eligible) -->
        @can('cancel', $booking)
            <div class="flex justify-end">
                <button
                    type="button"
                    x-data
                    x-on:click="$dispatch('open-modal', 'cancel-booking')"
                    class="min-h-[44px] px-4 py-2.5 rounded-xl border border-rose-300 text-rose-700 hover:bg-rose-50 font-semibold text-sm transition"
                >
                    Batalkan Pesanan Ini
                </button>
            </div>

            <!-- Cancel Confirmation Modal -->
            <x-modal name="cancel-booking" title="Konfirmasi Pembatalan Pesanan">
                <form method="POST" action="{{ route('my-bookings.cancel', $booking->code) }}" class="space-y-4">
                    @csrf
                    <p class="text-sm text-slate-600">
                        Apakah Anda yakin ingin membatalkan pesanan dengan kode <strong>{{ $booking->code }}</strong>?
                        @if($booking->status->value === 'confirmed')
                            <br><span class="text-xs text-amber-700 bg-amber-50 p-2 rounded-lg block mt-2">
                                Catatan: Karena pesanan sudah terkonfirmasi, pembatalan akan diproses pengembalian dana (refund) sebesar {{ \App\Models\Setting::get('cancellation_refund_percent', 80) }}% sesuai kebijakan toko.
                            </span>
                        @endif
                    </p>

                    <div>
                        <label for="cancel_reason" class="block text-xs font-semibold text-slate-700 mb-1">Alasan Pembatalan</label>
                        <textarea
                            id="cancel_reason"
                            name="reason"
                            rows="2"
                            required
                            placeholder="Alasan pembatalan..."
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                        <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'cancel-booking')">
                            Kembali
                        </x-button>
                        <x-button type="submit" variant="danger">
                            Ya, Batalkan Pesanan
                        </x-button>
                    </div>
                </form>
            </x-modal>
        @endcan
    </div>
</x-layouts.app>
