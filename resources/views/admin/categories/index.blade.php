<x-layouts.admin title="Kategori Unit">
    <div x-data="{
        editModalOpen: false,
        editCategory: { id: null, name: '', slug: '', is_active: true }
    }">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Kategori Unit Rental</h2>
                <p class="text-sm text-slate-500">Kelola kelompok kategori barang dan ketersediaan publik.</p>
            </div>
            <div>
                <x-button
                    type="button"
                    variant="primary"
                    x-on:click="$dispatch('open-modal', 'create-category')"
                >
                    <svg class="w-5 h-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Kategori
                </x-button>
            </div>
        </div>

        <x-card>
            <x-table>
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-3.5">Nama Kategori</th>
                        <th class="px-6 py-3.5">Slug URL</th>
                        <th class="px-6 py-3.5">Jumlah Unit</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($categories as $category)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 text-slate-900 font-semibold">
                                {{ $category->name }}
                            </td>
                            <td class="px-6 py-4 text-slate-500 font-mono text-xs">
                                {{ $category->slug }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                    {{ $category->items_count }} Unit
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($category->is_active)
                                    <x-badge variant="success">Aktif</x-badge>
                                @else
                                    <x-badge variant="default">Nonaktif</x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button
                                    type="button"
                                    x-on:click="
                                        editCategory = {
                                            id: {{ $category->id }},
                                            name: '{{ addslashes($category->name) }}',
                                            slug: '{{ $category->slug }}',
                                            is_active: {{ $category->is_active ? 'true' : 'false' }}
                                        };
                                        $dispatch('open-modal', 'edit-category');
                                    "
                                    class="p-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition inline-flex items-center min-h-[36px]"
                                    title="Edit Kategori"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>

                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus kategori ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="p-2 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition inline-flex items-center min-h-[36px]"
                                        title="Hapus Kategori"
                                        @if($category->items_count > 0) disabled @endif
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
                                Belum ada kategori yang ditambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>

        <!-- Create Category Modal -->
        <x-modal name="create-category" title="Tambah Kategori Baru">
            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
                @csrf
                <x-input
                    label="Nama Kategori"
                    name="name"
                    placeholder="Contoh: Kamera & Lensa"
                    required
                />

                <x-input
                    label="Slug URL (Opsional)"
                    name="slug"
                    placeholder="otomatis-dari-nama"
                />

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="create_is_active" name="is_active" value="1" checked class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                    <label for="create_is_active" class="text-sm font-medium text-slate-700 cursor-pointer">
                        Aktifkan kategori ini di katalog publik
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'create-category')">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary">
                        Simpan Kategori
                    </x-button>
                </div>
            </form>
        </x-modal>

        <!-- Edit Category Modal -->
        <x-modal name="edit-category" title="Edit Kategori">
            <form :action="'{{ url('admin/categories') }}/' + editCategory.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <x-input
                    label="Nama Kategori"
                    name="name"
                    x-model="editCategory.name"
                    required
                />

                <x-input
                    label="Slug URL"
                    name="slug"
                    x-model="editCategory.slug"
                />

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1" :checked="editCategory.is_active" class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                    <label for="edit_is_active" class="text-sm font-medium text-slate-700 cursor-pointer">
                        Aktifkan kategori ini di katalog publik
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'edit-category')">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary">
                        Perbarui Kategori
                    </x-button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layouts.admin>
