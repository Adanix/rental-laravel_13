<x-layouts.admin title="Daftar Unit Rental">
    <div class="space-y-6">
        <!-- Header & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Daftar Unit Rental</h2>
                <p class="text-sm text-slate-500">Kelola inventori, stok, spesifikasi, dan harga sewa unit.</p>
            </div>
            <div>
                <a
                    href="{{ route('admin.items.create') }}"
                    class="inline-flex items-center justify-center min-h-[44px] px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm transition shadow-xs"
                >
                    <svg class="w-5 h-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Unit Baru
                </a>
            </div>
        </div>

        <!-- Filter Card -->
        <x-card class="p-4 sm:p-5">
            <form method="GET" action="{{ route('admin.items.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <x-input
                    name="q"
                    placeholder="Cari nama unit..."
                    value="{{ request('q') }}"
                />

                <select
                    name="category_id"
                    class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <select
                    name="status"
                    class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">Semua Status</option>
                    @foreach(\App\Enums\ItemStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>

                <div class="flex items-center gap-2">
                    <x-button type="submit" variant="primary" class="w-full">
                        Filter
                    </x-button>
                    @if(request()->hasAny(['q', 'category_id', 'status']))
                        <a href="{{ route('admin.items.index') }}" class="min-h-[44px] px-3 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 flex items-center justify-center text-sm">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Table Card -->
        <x-card>
            <x-table>
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-3.5">Unit / Barang</th>
                        <th class="px-6 py-3.5">Kategori</th>
                        <th class="px-6 py-3.5">Harga / Hari</th>
                        <th class="px-6 py-3.5">Deposit</th>
                        <th class="px-6 py-3.5 text-center">Stok</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img
                                        src="{{ $item->primary_image_url }}"
                                        alt="{{ $item->name }}"
                                        class="w-12 h-12 rounded-xl object-cover border border-slate-200 shrink-0"
                                        loading="lazy"
                                    />
                                    <div>
                                        <a href="{{ route('admin.items.edit', $item) }}" class="text-slate-900 font-semibold hover:text-indigo-600 transition block">
                                            {{ $item->name }}
                                        </a>
                                        <span class="text-xs text-slate-400 font-mono">{{ $item->slug }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 text-sm">
                                {{ $item->category->name }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-900">
                                {{ format_rupiah($item->price_per_day) }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ format_rupiah($item->deposit) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 font-bold text-slate-800 text-xs">
                                    {{ $item->stock }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $item->status->badgeClass() }}">
                                    {{ $item->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a
                                    href="{{ route('admin.items.edit', $item) }}"
                                    class="p-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition inline-flex items-center min-h-[36px]"
                                    title="Edit Unit"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>

                                <form method="POST" action="{{ route('admin.items.destroy', $item) }}" class="inline" onsubmit="return confirm('Hapus unit {{ addslashes($item->name) }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="p-2 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition inline-flex items-center min-h-[36px]"
                                        title="Hapus Unit"
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
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                Tidak ada data unit yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>

            @if($items->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $items->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.admin>
