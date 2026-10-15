<x-layouts.admin title="Blackout Jadwal Unit">
    @php
        $itemsJson = $items->map(function ($it) {
            return [
                'id' => $it->id,
                'name' => $it->name,
                'category' => $it->category->name,
                'stock' => $it->stock,
                'image' => $it->primary_image_url,
            ];
        })->values();
    @endphp

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
                            {{ $item->name }} ({{ $item->category->name }})
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
                                <div class="flex items-center gap-3">
                                    <img src="{{ $blackout->item->primary_image_url }}" alt="{{ $blackout->item->name }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200 bg-white shrink-0" />
                                    <div>
                                        <span>{{ $blackout->item->name }}</span>
                                        <span class="block text-xs text-slate-400 font-normal">{{ $blackout->item->category->name }}</span>
                                    </div>
                                </div>
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
        <x-modal name="create-blackout" title="Tambah Jadwal Blackout & Servis">
            <form
                method="POST"
                action="{{ route('admin.blackouts.store') }}"
                class="space-y-4"
                x-data="{
                    items: {{ Js::from($itemsJson) }},
                    selectedItemId: {{ $items->first()?->id ?? 'null' }},
                    searchQuery: '',
                    startAt: '{{ now()->format('Y-m-d\TH:i') }}',
                    endAt: '{{ now()->addDays(2)->format('Y-m-d\TH:i') }}',
                    pickerOpen: false,

                    get selectedItem() {
                        return this.items.find(i => i.id == this.selectedItemId) || null;
                    },

                    get filteredItems() {
                        const q = this.searchQuery.toLowerCase().trim();
                        if (!q) return this.items;
                        return this.items.filter(i =>
                            i.name.toLowerCase().includes(q) ||
                            i.category.toLowerCase().includes(q)
                        );
                    },

                    get isInvalidDateRange() {
                        if (!this.startAt || !this.endAt) return true;
                        return new Date(this.endAt) <= new Date(this.startAt);
                    }
                }"
            >
                @csrf

                <!-- Hidden Input -->
                <input type="hidden" name="item_id" :value="selectedItemId" required />

                <!-- Unit Selector with Live Search & Preview -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Pilih Unit Rental <span class="text-rose-500 font-bold">*</span>
                    </label>

                    <!-- Selected Unit Preview -->
                    <template x-if="selectedItem">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <img :src="selectedItem.image" :alt="selectedItem.name" class="w-11 h-11 rounded-lg object-cover border border-slate-200 bg-white shrink-0" />
                                <div class="min-w-0">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide" x-text="selectedItem.category"></span>
                                    <h4 class="font-bold text-slate-900 text-sm truncate" x-text="selectedItem.name"></h4>
                                    <span class="text-[11px] text-slate-500">Total Stok: <strong x-text="selectedItem.stock"></strong> unit</span>
                                </div>
                            </div>
                            <button
                                type="button"
                                x-on:click="pickerOpen = !pickerOpen"
                                class="shrink-0 px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-xs font-semibold text-slate-700 transition"
                            >
                                <span x-text="pickerOpen ? 'Tutup Pilihan' : 'Ganti Unit'"></span>
                            </button>
                        </div>
                    </template>

                    <!-- Searchable Picker Dropdown/Panel -->
                    <div
                        x-show="pickerOpen || !selectedItem"
                        class="mt-2 p-3 rounded-2xl border border-slate-200 bg-slate-50/70 space-y-2"
                    >
                        <div class="relative">
                            <input
                                type="text"
                                x-model="searchQuery"
                                placeholder="Cari nama unit atau kategori..."
                                class="w-full min-h-[40px] px-3 py-1.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            <button
                                type="button"
                                x-show="searchQuery"
                                x-on:click="searchQuery = ''"
                                class="absolute inset-y-0 right-0 pr-3 text-slate-400 hover:text-slate-600 text-xs"
                            >
                                ✕
                            </button>
                        </div>

                        <div class="max-h-48 overflow-y-auto space-y-1 pr-1">
                            <template x-for="item in filteredItems" :key="item.id">
                                <div
                                    x-on:click="selectedItemId = item.id; pickerOpen = false"
                                    :class="selectedItemId === item.id ? 'bg-indigo-50 border-indigo-400 ring-1 ring-indigo-400' : 'bg-white border-slate-200 hover:bg-slate-100'"
                                    class="p-2 rounded-xl border flex items-center justify-between gap-2 cursor-pointer transition select-none text-xs"
                                >
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <img :src="item.image" :alt="item.name" class="w-8 h-8 rounded-md object-cover border border-slate-200 bg-white shrink-0" />
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-900 truncate" x-text="item.name"></p>
                                            <span class="text-[10px] text-slate-400" x-text="item.category"></span>
                                        </div>
                                    </div>
                                    <span class="text-[11px] font-semibold text-slate-500 shrink-0" x-text="'Stok: ' + item.stock"></span>
                                </div>
                            </template>
                            <div x-show="filteredItems.length === 0" class="py-4 text-center text-xs text-slate-400">
                                Unit tidak ditemukan.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="modal_start_at" class="block text-xs font-semibold text-slate-700 mb-1">
                            Waktu Mulai Blackout <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <input
                            id="modal_start_at"
                            name="start_at"
                            type="datetime-local"
                            x-model="startAt"
                            required
                            class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>

                    <div>
                        <label for="modal_end_at" class="block text-xs font-semibold text-slate-700 mb-1">
                            Waktu Selesai Blackout <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <input
                            id="modal_end_at"
                            name="end_at"
                            type="datetime-local"
                            x-model="endAt"
                            :min="startAt"
                            required
                            class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>
                </div>

                <!-- Date Range Error Warning -->
                <div x-show="isInvalidDateRange" class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Waktu selesai blackout harus setelah waktu mulai.</span>
                </div>

                <div>
                    <label for="modal_reason" class="block text-xs font-semibold text-slate-700 mb-1">Alasan Blackout / Servis (Opsional)</label>
                    <input
                        id="modal_reason"
                        type="text"
                        name="reason"
                        placeholder="Contoh: Pemeliharaan sensor & servis berkala"
                        class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'create-blackout')">
                        Batal
                    </x-button>
                    <button
                        type="submit"
                        :disabled="isInvalidDateRange || !selectedItemId"
                        :class="(isInvalidDateRange || !selectedItemId) ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-indigo-600 hover:bg-indigo-700'"
                        class="px-4 py-2.5 rounded-xl text-white font-semibold text-sm transition shadow-sm"
                    >
                        Simpan Blackout
                    </button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layouts.admin>
