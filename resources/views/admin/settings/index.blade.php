<x-layouts.admin title="Pengaturan Sistem">
    <div class="max-w-4xl space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Pengaturan Sistem & Toko</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Konfigurasi parameter operasional, rekening pembayaran, dan kebijakan rental.</p>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Section 1: Toko -->
            <x-card class="p-6 space-y-4">
                <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    1. Identitas & Kontak Toko
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="store_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Toko Rental *</label>
                        <input
                            type="text"
                            name="store_name"
                            id="store_name"
                            required
                            value="{{ old('store_name', $settings['store_name']) }}"
                            class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>

                    <div>
                        <label for="store_phone" class="block text-xs font-semibold text-slate-700 mb-1">No WhatsApp Toko *</label>
                        <input
                            type="text"
                            name="store_phone"
                            id="store_phone"
                            required
                            value="{{ old('store_phone', $settings['store_phone']) }}"
                            placeholder="08123456789"
                            class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"
                        />
                        <span class="text-[11px] text-slate-400 mt-1 block">Digunakan pada tombol hubungi WhatsApp dan invoice.</span>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="store_address" class="block text-xs font-semibold text-slate-700 mb-1">Alamat Toko</label>
                        <input
                            type="text"
                            name="store_address"
                            id="store_address"
                            value="{{ old('store_address', $settings['store_address']) }}"
                            class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>
                </div>
            </x-card>

            <!-- Section 2: Rekening Bank & QRIS -->
            <x-card class="p-6 space-y-4">
                <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    2. Rekening Pembayaran & QRIS Statis
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="bank_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Bank *</label>
                        <input
                            type="text"
                            name="bank_name"
                            id="bank_name"
                            required
                            value="{{ old('bank_name', $settings['bank_name']) }}"
                            placeholder="BCA / Mandiri / BNI"
                            class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>

                    <div>
                        <label for="bank_account_number" class="block text-xs font-semibold text-slate-700 mb-1">Nomor Rekening *</label>
                        <input
                            type="text"
                            name="bank_account_number"
                            id="bank_account_number"
                            required
                            value="{{ old('bank_account_number', $settings['bank_account_number']) }}"
                            class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"
                        />
                    </div>

                    <div>
                        <label for="bank_account_holder" class="block text-xs font-semibold text-slate-700 mb-1">Atas Nama Rekening *</label>
                        <input
                            type="text"
                            name="bank_account_holder"
                            id="bank_account_holder"
                            required
                            value="{{ old('bank_account_holder', $settings['bank_account_holder']) }}"
                            class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>

                    <div class="sm:col-span-3 pt-2" x-data="{ preview: null }">
                        <label for="qris_file" class="block text-xs font-semibold text-slate-700 mb-1">Upload Gambar QRIS Statis</label>
                        <div class="flex items-center gap-4">
                            <template x-if="preview">
                                <img :src="preview" alt="Pratinjau QRIS Baru" class="w-20 h-20 object-contain border-2 border-indigo-500 rounded-lg p-1 bg-white shadow-xs">
                            </template>
                            <template x-if="!preview">
                                <div>
                                    @if(!empty($settings['qris_image']))
                                        <img src="{{ $settings['qris_image'] }}" alt="QRIS Saat ini" class="w-20 h-20 object-contain border border-slate-200 rounded-lg p-1 bg-white">
                                    @endif
                                </div>
                            </template>
                            <input
                                type="file"
                                name="qris_file"
                                id="qris_file"
                                accept="image/*"
                                x-on:change="const f = $event.target.files[0]; preview = f ? URL.createObjectURL(f) : null"
                                class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-700 file:font-semibold cursor-pointer"
                            />
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Section 3: Kebijakan Bisnis -->
            <x-card class="p-6 space-y-4">
                <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    3. Kebijakan Rental & Tarif Denda
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="expires_hours" class="block text-xs font-semibold text-slate-700 mb-1">Batas Waktu Pembayaran (Jam) *</label>
                        <input
                            type="number"
                            name="expires_hours"
                            id="expires_hours"
                            required
                            min="1"
                            max="72"
                            value="{{ old('expires_hours', $settings['expires_hours']) }}"
                            class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <span class="text-[11px] text-slate-400 mt-1 block">Pesanan pending tanpa bukti bayar akan otomatis expired setelah jam ini.</span>
                    </div>

                    <div>
                        <label for="late_fee_per_day_percent" class="block text-xs font-semibold text-slate-700 mb-1">Tarif Denda Telat per Hari (% dari Tarif Sewa) *</label>
                        <input
                            type="number"
                            name="late_fee_per_day_percent"
                            id="late_fee_per_day_percent"
                            required
                            min="0"
                            max="500"
                            value="{{ old('late_fee_per_day_percent', $settings['late_fee_per_day_percent']) }}"
                            class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <span class="text-[11px] text-slate-400 mt-1 block">Default 100% (setara tarif sewa 1 hari penuh per hari keterlambatan).</span>
                    </div>

                    <div>
                        <label for="cancellation_refund_percent" class="block text-xs font-semibold text-slate-700 mb-1">Persen Refund Pembatalan Pelanggan (%) *</label>
                        <input
                            type="number"
                            name="cancellation_refund_percent"
                            id="cancellation_refund_percent"
                            required
                            min="0"
                            max="100"
                            value="{{ old('cancellation_refund_percent', $settings['cancellation_refund_percent']) }}"
                            class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <span class="text-[11px] text-slate-400 mt-1 block">Besaran dana yang dikembalikan jika pelanggan membatalkan pesanan confirmed.</span>
                    </div>

                    <div>
                        <label for="weekly_discount_percent" class="block text-xs font-semibold text-slate-700 mb-1">Diskon Sewa Panjang &ge; 7 Hari (%) *</label>
                        <input
                            type="number"
                            name="weekly_discount_percent"
                            id="weekly_discount_percent"
                            required
                            min="0"
                            max="100"
                            value="{{ old('weekly_discount_percent', $settings['weekly_discount_percent']) }}"
                            class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <span class="text-[11px] text-slate-400 mt-1 block">Potongan otomatis untuk durasi sewa 7 hari atau lebih.</span>
                    </div>
                </div>
            </x-card>

            <!-- Save Button -->
            <div class="flex justify-end">
                <x-button type="submit" variant="primary">
                    Simpan Semua Pengaturan
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.admin>
