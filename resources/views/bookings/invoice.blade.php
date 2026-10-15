<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $booking->code }} - {{ \App\Models\Setting::get('store_name', 'RentalHub Nusantara') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-size: 12pt;
            }
            .no-print {
                display: none !important;
            }
            .print-border {
                border-color: #000000 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 sm:p-8 font-sans antialiased">
    <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 p-8 sm:p-12 space-y-8">
        <!-- Print Toolbar -->
        <div class="no-print flex items-center justify-between pb-6 border-b border-slate-100">
            <a href="{{ route('my-bookings.show', $booking->code) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                &larr; Kembali ke Detail Pesanan
            </a>
            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition shadow-sm cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak / Simpan PDF
            </button>
        </div>

        <!-- Invoice Header -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6 pb-6 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-sm">
                        R
                    </div>
                    <span class="font-extrabold text-lg text-slate-900 tracking-tight">{{ \App\Models\Setting::get('store_name', 'RentalHub Nusantara') }}</span>
                </div>
                <p class="text-xs text-slate-500 max-w-xs leading-relaxed">
                    {{ \App\Models\Setting::get('store_address', 'Jl. Thamrin No. 88, Menteng, Jakarta Pusat') }}<br>
                    WhatsApp: {{ \App\Models\Setting::get('store_phone', '081234567890') }}
                </p>
            </div>

            <div class="text-left sm:text-right">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">FAKTUR SEWA</h1>
                <p class="font-mono text-sm font-bold text-indigo-600 mt-1">#{{ $booking->code }}</p>
                <p class="text-xs text-slate-500 mt-1">Tanggal: {{ format_date_id($booking->created_at, 'd M Y') }}</p>
                <div class="mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold border {{ $booking->status->badgeClass() }}">
                        {{ $booking->status->label() }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Customer & Rental Period Info -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs sm:text-sm">
            <div>
                <span class="text-xs uppercase font-bold text-slate-400 block mb-1">Ditagihkan Kepada:</span>
                <p class="font-bold text-slate-900 text-base">{{ $booking->user->name }}</p>
                <p class="text-slate-600">WhatsApp: {{ $booking->user->phone }}</p>
                @if($booking->user->email)
                    <p class="text-slate-600">Email: {{ $booking->user->email }}</p>
                @endif
            </div>

            <div>
                <span class="text-xs uppercase font-bold text-slate-400 block mb-1">Jadwal & Periode Sewa:</span>
                <p class="text-slate-700">Mulai: <strong>{{ format_date_id($booking->start_at) }}</strong></p>
                <p class="text-slate-700">Selesai: <strong>{{ format_date_id($booking->end_at) }}</strong></p>
                <p class="text-slate-500 mt-0.5">Durasi Total: <strong>{{ $booking->duration_days }} Hari</strong></p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto w-full">
            <table class="min-w-full text-left text-xs sm:text-sm divide-y divide-slate-200">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase text-[11px]">
                    <tr>
                        <th class="py-3 px-3">Deskripsi Barang</th>
                        <th class="py-3 px-3 text-center">Qty</th>
                        <th class="py-3 px-3 text-right">Tarif / Hari</th>
                        <th class="py-3 px-3 text-right">Deposit</th>
                        <th class="py-3 px-3 text-right">Subtotal Sewa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($booking->items as $item)
                        <tr>
                            <td class="py-3 px-3 font-semibold text-slate-900">
                                {{ $item->item->name }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                {{ $item->qty }}
                            </td>
                            <td class="py-3 px-3 text-right text-slate-600">
                                {{ format_rupiah($item->unit_price) }}
                            </td>
                            <td class="py-3 px-3 text-right text-slate-600">
                                {{ format_rupiah($item->unit_deposit * $item->qty) }}
                            </td>
                            <td class="py-3 px-3 text-right font-bold text-slate-900">
                                {{ format_rupiah($item->subtotal) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Breakdown -->
        <div class="flex justify-end pt-4 border-t border-slate-200">
            <div class="w-full sm:w-72 space-y-2 text-xs sm:text-sm">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal Biaya Sewa:</span>
                    <span class="font-semibold text-slate-900">{{ format_rupiah($booking->subtotal_rental + $booking->discount_amount) }}</span>
                </div>

                @if($booking->discount_amount > 0)
                    <div class="flex justify-between text-emerald-700">
                        <span>Diskon Sewa:</span>
                        <span class="font-bold">- {{ format_rupiah($booking->discount_amount) }}</span>
                    </div>
                @endif

                <div class="flex justify-between text-slate-600">
                    <span>Deposit Jaminan (Refundable):</span>
                    <span class="font-semibold text-slate-900">{{ format_rupiah($booking->deposit_total) }}</span>
                </div>

                @if($booking->late_fee > 0)
                    <div class="flex justify-between text-rose-600">
                        <span>Denda Telat:</span>
                        <span class="font-bold">+ {{ format_rupiah($booking->late_fee) }}</span>
                    </div>
                @endif

                @if($booking->damage_fee > 0)
                    <div class="flex justify-between text-rose-600">
                        <span>Biaya Kerusakan:</span>
                        <span class="font-bold">+ {{ format_rupiah($booking->damage_fee) }}</span>
                    </div>
                @endif

                <div class="pt-3 border-t-2 border-slate-900 flex justify-between items-baseline font-extrabold text-base sm:text-lg text-slate-900">
                    <span>Grand Total:</span>
                    <span>{{ format_rupiah($booking->grand_total + $booking->late_fee + $booking->damage_fee) }}</span>
                </div>
            </div>
        </div>

        <!-- Signature Block -->
        <div class="pt-12 grid grid-cols-2 gap-8 text-center text-xs text-slate-600">
            <div>
                <p class="mb-16">Penyewa / Pelanggan,</p>
                <p class="font-bold border-t border-slate-300 pt-1 inline-block min-w-[160px]">{{ $booking->user->name }}</p>
            </div>
            <div>
                <p class="mb-16">Staff Operasional,</p>
                <p class="font-bold border-t border-slate-300 pt-1 inline-block min-w-[160px]">{{ \App\Models\Setting::get('store_name', 'RentalHub Nusantara') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
