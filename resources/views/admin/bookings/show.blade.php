<x-layouts.admin :title="'Detail Pesanan ' . $booking->code">
    <div class="space-y-6 max-w-5xl">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.bookings.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-indigo-600 shadow-2xs transition">
                    &larr;
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">{{ $booking->code }}</h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $booking->status->badgeClass() }}">
                            {{ $booking->status->label() }}
                        </span>
                        @if($booking->is_overdue)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-600 text-white animate-pulse">
                                ⚠️ OVERDUE
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Dibuat pada {{ format_date_id($booking->created_at) }}</p>
                </div>
            </div>

            <!-- Quick Action Buttons for Staff -->
            <div class="flex flex-wrap items-center gap-2">
                @if($booking->status->value === 'confirmed')
                    <button
                        type="button"
                        x-data
                        x-on:click="$dispatch('open-modal', 'pickup-modal')"
                        class="min-h-[44px] px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs sm:text-sm font-semibold hover:bg-indigo-700 shadow-sm transition flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Serah-Terima Unit (Pickup)
                    </button>
                @elseif($booking->status->value === 'picked_up')
                    <button
                        type="button"
                        x-data
                        x-on:click="$dispatch('open-modal', 'return-modal')"
                        class="min-h-[44px] px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs sm:text-sm font-semibold hover:bg-emerald-700 shadow-sm transition flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Pengembalian Unit (Return)
                    </button>
                @endif

                <a
                    href="{{ route('my-bookings.invoice', $booking->code) }}"
                    target="_blank"
                    class="inline-flex items-center min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-2xs transition"
                >
                    <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Invoice
                </a>
            </div>
        </div>

        <!-- Customer & Schedule Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Customer Info -->
            <x-card class="p-5 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Informasi Pelanggan</h3>
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-base font-bold text-slate-900">{{ $booking->user->name }}</div>
                        <div class="text-xs text-slate-500 font-mono mt-0.5">{{ $booking->user->phone }}</div>
                        @if($booking->user->email)
                            <div class="text-xs text-slate-400">{{ $booking->user->email }}</div>
                        @endif
                    </div>
                    @php
                        $cPhone = preg_replace('/[^0-9]/', '', $booking->user->phone);
                        if (str_starts_with($cPhone, '0')) {
                            $cPhone = '62' . substr($cPhone, 1);
                        }
                        $waMsg = urlencode("Halo {$booking->user->name}, kami dari RentalHub terkait pesanan Anda {$booking->code}.");
                    @endphp
                    <a
                        href="https://wa.me/{{ $cPhone }}?text={{ $waMsg }}"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 min-h-[36px] px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold border border-emerald-200 transition"
                    >
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.044c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z" />
                        </svg>
                        Chat WA
                    </a>
                </div>
            </x-card>

            <!-- Schedule & Handover Info -->
            <x-card class="p-5 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Jadwal & Serah-Terima</h3>
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="text-slate-400 block">Jadwal Ambil:</span>
                        <strong class="text-slate-900 block mt-0.5">{{ format_date_id($booking->start_at) }}</strong>
                        @if($booking->picked_up_at)
                            <span class="text-emerald-700 block mt-1 font-semibold">✓ Diambil: {{ format_date_id($booking->picked_up_at) }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-400 block">Jadwal Kembali:</span>
                        <strong class="text-slate-900 block mt-0.5">{{ format_date_id($booking->end_at) }}</strong>
                        @if($booking->returned_at)
                            <span class="text-emerald-700 block mt-1 font-semibold">✓ Kembali: {{ format_date_id($booking->returned_at) }}</span>
                        @endif
                    </div>
                </div>

                @if($booking->handover_checklist)
                    <div class="mt-3 pt-3 border-t border-slate-100 text-xs space-y-2">
                        @if(isset($booking->handover_checklist['pickup']))
                            <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                                <span class="font-bold text-slate-700 block">Kondisi Serah-Terima Awal:</span>
                                <p class="text-slate-600 mt-0.5">{{ $booking->handover_checklist['pickup']['condition'] ?? '-' }}</p>
                                <span class="text-[11px] text-slate-400 block mt-1">Petugas: {{ $booking->handover_checklist['pickup']['staff_name'] ?? '-' }}</span>
                            </div>
                        @endif

                        @if(isset($booking->handover_checklist['return']))
                            <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                                <span class="font-bold text-slate-700 block">Kondisi Pengembalian Akhir:</span>
                                <p class="text-slate-600 mt-0.5">{{ $booking->handover_checklist['return']['condition'] ?? '-' }}</p>
                                <span class="text-[11px] text-slate-400 block mt-1">Petugas: {{ $booking->handover_checklist['return']['staff_name'] ?? '-' }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Rented Items & Costs -->
        <x-card class="p-6 space-y-6">
            <h3 class="text-sm font-bold text-slate-900">Rincian Unit Rental</h3>
            <x-table>
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-4 py-3">Nama Unit</th>
                        <th class="px-4 py-3 text-center">Qty</th>
                        <th class="px-4 py-3 text-right">Tarif / Hari</th>
                        <th class="px-4 py-3 text-right">Deposit</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    @foreach($booking->items as $item)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                {{ $item->item->name }}
                            </td>
                            <td class="px-4 py-3 text-center font-bold">
                                {{ $item->qty }}
                            </td>
                            <td class="px-4 py-3 text-right text-slate-600">
                                {{ format_rupiah($item->unit_price) }}
                            </td>
                            <td class="px-4 py-3 text-right text-slate-600">
                                {{ format_rupiah($item->unit_deposit * $item->qty) }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-900">
                                {{ format_rupiah($item->subtotal) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-table>

            <!-- Financial Summary -->
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
                        <div class="flex justify-between text-rose-600 font-semibold">
                            <span>Denda Keterlambatan:</span>
                            <span>+ {{ format_rupiah($booking->late_fee) }}</span>
                        </div>
                    @endif

                    @if($booking->damage_fee > 0)
                        <div class="flex justify-between text-rose-600 font-semibold">
                            <span>Biaya Kerusakan:</span>
                            <span>+ {{ format_rupiah($booking->damage_fee) }}</span>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-slate-200 flex justify-between items-baseline text-base sm:text-lg font-extrabold text-indigo-700">
                        <span>Total Akhir:</span>
                        <span>{{ format_rupiah($booking->grand_total + $booking->late_fee + $booking->damage_fee) }}</span>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Payments & Verification Section -->
        <x-card class="p-6 space-y-4">
            <h3 class="text-sm font-bold text-slate-900">Pembayaran & Bukti Transfer</h3>

            @if($booking->payments->isEmpty())
                <p class="text-xs text-slate-500 py-3 italic">Belum ada catatan pembayaran atau bukti transfer yang diunggah.</p>
            @else
                <div class="space-y-3">
                    @foreach($booking->payments as $payment)
                        <div class="p-4 rounded-xl border {{ $payment->status->value === 'verified' ? 'border-emerald-200 bg-emerald-50/20' : ($payment->status->value === 'rejected' ? 'border-rose-200 bg-rose-50/20' : 'border-amber-200 bg-amber-50/20') }} flex flex-col md:flex-row md:items-center md:justify-between gap-4 text-xs sm:text-sm">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 font-bold text-slate-900">
                                    <span>{{ $payment->type->label() }}</span>
                                    <span class="text-slate-400">&bull;</span>
                                    <span class="font-normal text-slate-600 uppercase">{{ $payment->method->label() }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold border {{ $payment->status->badgeClass() }}">
                                        {{ $payment->status->label() }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-600">
                                    Nominal: <strong class="text-slate-900 font-mono text-sm">{{ format_rupiah($payment->amount) }}</strong> &bull; {{ format_date_id($payment->created_at) }}
                                </div>
                                @if($payment->notes)
                                    <div class="text-xs text-slate-700 bg-white/70 p-2 rounded-lg border border-slate-200 mt-1">
                                        {{ $payment->notes }}
                                    </div>
                                @endif
                                @if($payment->verified_by)
                                    <div class="text-[11px] text-slate-500">
                                        Diverifikasi oleh: <strong>{{ $payment->verifier->name ?? 'Staff' }}</strong> pada {{ format_date_id($payment->verified_at) }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 self-start md:self-center">
                                @if($payment->proof_path)
                                    <a
                                        href="{{ Storage::url($payment->proof_path) }}"
                                        target="_blank"
                                        class="min-h-[36px] px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 shadow-2xs"
                                    >
                                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Buka Bukti
                                    </a>
                                @endif

                                <!-- Verification Action Buttons -->
                                @if($payment->status->value === 'pending')
                                    @if($payment->type->value === 'rental_and_deposit')
                                        <!-- Verify Modal trigger -->
                                        <form method="POST" action="{{ route('admin.bookings.payments.verify', [$booking->id, $payment->id]) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                onclick="return confirm('Verifikasi pembayaran ini dan konfirmasi pesanan?')"
                                                class="min-h-[36px] px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-2xs transition"
                                            >
                                                Verifikasi Sah
                                            </button>
                                        </form>

                                        <!-- Reject Modal trigger -->
                                        <button
                                            type="button"
                                            x-data
                                            x-on:click="$dispatch('open-modal', 'reject-payment-{{ $payment->id }}')"
                                            class="min-h-[36px] px-3 py-1.5 rounded-lg border border-rose-300 text-rose-700 hover:bg-rose-50 text-xs font-bold transition"
                                        >
                                            Tolak
                                        </button>

                                        <!-- Reject Modal -->
                                        <x-modal :name="'reject-payment-' . $payment->id" title="Tolak Bukti Pembayaran">
                                            <form method="POST" action="{{ route('admin.bookings.payments.reject', [$booking->id, $payment->id]) }}" class="space-y-4">
                                                @csrf
                                                <p class="text-xs text-slate-600">
                                                    Jika pembayaran ditolak, status pesanan otomatis akan diubah menjadi <strong>Ditolak (Rejected)</strong> sehingga stok unit langsung dibebaskan.
                                                </p>
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Penolakan</label>
                                                    <textarea name="reason" rows="2" required placeholder="Contoh: Bukti transfer tidak terbaca / nominal tidak sesuai" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs"></textarea>
                                                </div>
                                                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                                                    <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'reject-payment-{{ $payment->id }}')">Batal</x-button>
                                                    <x-button type="submit" variant="danger">Tolak Pembayaran</x-button>
                                                </div>
                                            </form>
                                        </x-modal>
                                    @else
                                        <!-- Settlement of refund or additional fee -->
                                        <form method="POST" action="{{ route('admin.bookings.payments.settle', [$booking->id, $payment->id]) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                onclick="return confirm('Tandai transaksi ini telah diselesaikan (sudah ditransfer / diserahkan)?')"
                                                class="min-h-[36px] px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 shadow-2xs transition"
                                            >
                                                Tandai Selesai
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <!-- Status Audit Log Timeline -->
        <x-card class="p-6 space-y-4">
            <h3 class="text-sm font-bold text-slate-900">Riwayat Perubahan Status (Audit Log)</h3>
            <div class="space-y-3">
                @foreach($booking->statusLogs as $log)
                    <div class="flex items-start gap-3 text-xs sm:text-sm pb-3 border-b border-slate-100 last:border-0 last:pb-0">
                        <div class="w-2 h-2 rounded-full bg-indigo-600 mt-2 shrink-0"></div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-800">
                                    {{ $log->from_status ? $log->from_status->label() : 'Dibuat' }} &rarr; {{ $log->to_status->label() }}
                                </span>
                                <span class="text-[11px] text-slate-400">&bull; {{ format_date_id($log->created_at) }}</span>
                            </div>
                            @if($log->reason)
                                <p class="text-xs text-slate-600 mt-0.5">{{ $log->reason }}</p>
                            @endif
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                Oleh: <strong>{{ $log->actor->name ?? 'Sistem / Pelanggan' }}</strong>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>

    <!-- Modal Pickup (Serah-Terima Awal) -->
    <x-modal name="pickup-modal" title="Form Serah-Terima Unit (Pickup)">
        <form method="POST" action="{{ route('admin.bookings.pickup', $booking->id) }}" class="space-y-4">
            @csrf
            <p class="text-xs text-slate-600">
                Pastikan identitas penyewa cocok dan semua kelengkapan unit telah diperiksa bersama pelanggan sebelum diserahkan.
            </p>

            <div>
                <label for="pickup_notes" class="block text-xs font-semibold text-slate-700 mb-1">Catatan Kondisi Unit & Kelengkapan Awal</label>
                <textarea
                    id="pickup_notes"
                    name="condition_notes"
                    rows="3"
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Contoh: Bodi mulus tanpa lecet, baterai terpasang 2 unit, lensa bersih, tas unit lengkap."
                >Kondisi unit baik, berfungsi normal, kelengkapan unit lengkap sesuai standar.</textarea>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'pickup-modal')">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary">
                    Konfirmasi Serah-Terima
                </x-button>
            </div>
        </form>
    </x-modal>

    <!-- Modal Return (Pengembalian Unit & Settlement) -->
    @php
        $now = now();
        $isOverdue = $now->gt($booking->end_at);
        $lateHours = $isOverdue ? $now->diffInHours($booking->end_at) : 0;
        $lateDays = $lateHours > 0 ? (int) ceil($lateHours / 24) : 0;
        $dailySum = $booking->items->sum(fn ($i) => $i->unit_price * $i->qty);
        $lateFeePercent = (int) \App\Models\Setting::get('late_fee_per_day_percent', 100);
        $estimatedLateFee = (int) round($lateDays * $dailySum * ($lateFeePercent / 100));
    @endphp
    <x-modal name="return-modal" title="Form Pengembalian Unit (Return)">
        <form
            method="POST"
            action="{{ route('admin.bookings.return', $booking->id) }}"
            class="space-y-4"
            x-data="{
                damageFee: 0,
                depositTotal: {{ $booking->deposit_total }},
                lateFee: {{ $estimatedLateFee }},
                get totalDeductions() {
                    return Number(this.lateFee) + Number(this.damageFee || 0);
                },
                get refundAmount() {
                    return Math.max(0, this.depositTotal - this.totalDeductions);
                },
                get additionalAmount() {
                    return Math.max(0, this.totalDeductions - this.depositTotal);
                }
            }"
        >
            @csrf
            <p class="text-xs text-slate-600">
                Periksa kondisi unit yang dikembalikan. Sistem secara otomatis menghitung denda jika unit dikembalikan terlambat.
            </p>

            @if($lateDays > 0)
                <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                    <strong>Peringatan Keterlambatan:</strong> Terlambat sekitar {{ $lateDays }} hari.
                    <br>Denda keterlambatan terhitung: <strong class="font-mono">Rp {{ number_format($estimatedLateFee, 0, ',', '.') }}</strong>
                </div>
            @endif

            <div>
                <label for="return_notes" class="block text-xs font-semibold text-slate-700 mb-1">Catatan Kondisi Unit Saat Kembali</label>
                <textarea
                    id="return_notes"
                    name="condition_notes"
                    rows="2"
                    required
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Contoh: Unit bersih, semua fungsi bekerja dengan baik tanpa kerusakan."
                >Unit telah diperiksa, fungsi normal dan lengkap.</textarea>
            </div>

            <div>
                <label for="damage_fee" class="block text-xs font-semibold text-slate-700 mb-1">Biaya Kerusakan / Kehilangan (Rp)</label>
                <input
                    type="number"
                    id="damage_fee"
                    name="damage_fee"
                    x-model.number="damageFee"
                    min="0"
                    step="1000"
                    class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="0"
                />
            </div>

            <!-- Settlement Live Summary -->
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-1.5 font-mono">
                <div class="flex justify-between">
                    <span class="text-slate-500">Deposit Jaminan:</span>
                    <span class="font-bold text-slate-800">Rp {{ number_format($booking->deposit_total, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-rose-600">
                    <span>Total Denda & Kerusakan:</span>
                    <span class="font-bold" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(totalDeductions)"></span>
                </div>
                <div class="border-t border-slate-200 pt-1.5 flex justify-between font-bold text-sm">
                    <span x-text="refundAmount > 0 ? 'Sisa Deposit yang Dikembalikan:' : (additionalAmount > 0 ? 'Kurang Bayar yang Ditagihkan:' : 'Deposit Pas:')"></span>
                    <span :class="refundAmount > 0 ? 'text-emerald-700' : 'text-rose-700'" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(refundAmount > 0 ? refundAmount : additionalAmount)"></span>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'return-modal')">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary">
                    Simpan & Selesaikan Pengembalian
                </x-button>
            </div>
        </form>
    </x-modal>
</x-layouts.admin>
