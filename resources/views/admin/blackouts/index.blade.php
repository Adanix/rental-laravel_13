<x-layouts.admin title="Blackout Jadwal Unit">
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Blackout Jadwal Servis & Blokir Manual</h2>
                <p class="text-sm text-slate-500">Unit tidak akan tersedia untuk dibooking pada periode blackout.</p>
            </div>
            <div>
                <x-button
                    type="button"
                    variant="primary"
                    x-on:click="$dispatch('open-modal', 'create-blackout')"
                >
                    <svg class="w-5 h-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Blackout
                </x-button>
            </div>
        </div>

        <x-card class="p-4 sm:p-5">
            <form method="GET" action="{{ route('admin.blackouts.index') }}" class="flex flex-col sm:flex-row gap-3">
                <select
                    name="item_id"
                    class="w-full sm:w-72 min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">Semua Unit</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>

                <x-button type="submit" variant="primary">
                    Filter Unit
                </x-button>

                @if(request()->filled('item_id'))
                    <a href="{{ route('admin.blackouts.index') }}" class="min-h-[44px] px-3 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 flex items-center justify-center text-sm">
                        Reset
                    </a>
                @endif
            </form>
        </x-card>

        <x-card>
            <x-table>
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-3.5">Unit Rental</th>
                        <th class="px-6 py-3.5">Mulai Blackout</th>
                        <th class="px-6 py-3.5">Selesai Blackout</th>
                        <th class="px-6 py-3.5">Alasan / Catatan</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($blackouts as $blackout)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-900">
                                {{ $blackout->item->name }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ format_date_id($blackout->start_at) }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ format_date_id($blackout->end_at) }}
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                {{ $blackout->reason ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form method="POST" action="{{ route('admin.blackouts.destroy', $blackout) }}" class="inline" onsubmit="return confirm('Hapus blackout ini? Unit akan kembali tersedia.');">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="p-2 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition inline-flex items-center min-h-[36px]"
                                        title="Hapus Blackout"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                Belum ada jadwal blackout yang didaftarkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>

            @if($blackouts->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $blackouts->links() }}
                </div>
            @endif
        </x-card>

        <!-- Create Blackout Modal -->
        <x-modal name="create-blackout" title="Tambah Jadwal Blackout">
            <form method="POST" action="{{ route('admin.blackouts.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="modal_item_id" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Pilih Unit Rental <span class="text-rose-500 font-bold">*</span>
                    </label>
                    <select
                        id="modal_item_id"
                        name="item_id"
                        required
                        class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">Pilih Unit...</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->name }} (Stok: {{ $item->stock }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input
                        label="Waktu Mulai"
                        name="start_at"
                        type="datetime-local"
                        required
                    />

                    <x-input
                        label="Waktu Selesai"
                        name="end_at"
                        type="datetime-local"
                        required
                    />
                </div>

                <x-input
                    label="Alasan Blackout / Servis"
                    name="reason"
                    placeholder="Contoh: Pemeliharaan sensor & servis berkala"
                />

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'create-blackout')">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary">
                        Simpan Blackout
                    </x-button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layouts.admin>
