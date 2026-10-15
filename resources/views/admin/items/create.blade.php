<x-layouts.admin title="Tambah Unit Rental Baru">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('admin.items.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mb-1">
                    &larr; Kembali ke Daftar Unit
                </a>
                <h2 class="text-xl font-bold text-slate-900">Tambah Unit Rental Baru</h2>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('admin.items.store') }}"
            enctype="multipart/form-data"
            x-data="{
                pricePerDay: {{ old('price_per_day', 0) }},
                deposit: {{ old('deposit', 0) }},
                specs: [
                    { key: 'Brand', value: '' },
                    { key: 'Kondisi', value: 'Sangat Baik' }
                ],
                previewImages: [],
                addSpec() {
                    this.specs.push({ key: '', value: '' });
                },
                removeSpec(index) {
                    this.specs.splice(index, 1);
                },
                formatRp(num) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(num || 0);
                },
                handleImages(e) {
                    this.previewImages = [];
                    const files = e.target.files;
                    if (files) {
                        for (let i = 0; i < files.length; i++) {
                            this.previewImages.push({
                                name: files[i].name,
                                url: URL.createObjectURL(files[i])
                            });
                        }
                    }
                }
            }"
            class="space-y-6"
        >
            @csrf

            <!-- Informasi Dasar -->
            <x-card class="p-6 space-y-5">
                <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Informasi Utama Unit</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Kategori Unit <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <select
                            id="category_id"
                            name="category_id"
                            required
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="">Pilih Kategori...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Status Awal <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <select
                            id="status"
                            name="status"
                            required
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            @foreach(\App\Enums\ItemStatus::cases() as $status)
                                <option value="{{ $status->value }}" {{ old('status', 'active') === $status->value ? 'selected' : '' }}>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input
                        label="Nama Unit / Barang"
                        name="name"
                        placeholder="Contoh: Sony Alpha A7 IV Body Only"
                        required
                    />

                    <x-input
                        label="Slug URL (Opsional)"
                        name="slug"
                        placeholder="otomatis-dari-nama"
                    />
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi Lengkap Unit</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        placeholder="Jelaskan fitur, kondisi, dan kelengkapan unit rental..."
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >{{ old('description') }}</textarea>
                </div>
            </x-card>

            <!-- Harga & Inventori -->
            <x-card class="p-6 space-y-5">
                <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Harga Sewa & Stok Fisik</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="price_per_day" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Tarif Sewa per Hari (Rp) <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <input
                            type="number"
                            name="price_per_day"
                            id="price_per_day"
                            x-model.number="pricePerDay"
                            min="0"
                            step="1000"
                            placeholder="Contoh: 150000"
                            required
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <span class="text-xs font-mono font-bold text-indigo-600 block mt-1" x-text="formatRp(pricePerDay)"></span>
                    </div>

                    <div>
                        <label for="deposit" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Deposit Jaminan (Rp) <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <input
                            type="number"
                            name="deposit"
                            id="deposit"
                            x-model.number="deposit"
                            min="0"
                            step="1000"
                            placeholder="Contoh: 100000"
                            required
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <span class="text-xs font-mono font-bold text-indigo-600 block mt-1" x-text="formatRp(deposit)"></span>
                    </div>

                    <x-input
                        label="Total Stok Unit Fisik"
                        name="stock"
                        type="number"
                        min="1"
                        placeholder="1"
                        value="1"
                        required
                    />
                </div>
            </x-card>

            <!-- Spesifikasi Teknis (JSON Key-Value) -->
            <x-card class="p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Spesifikasi Teknis & Kelengkapan</h3>
                        <p class="text-xs text-slate-500">Tambahkan atribut penting unit (misal: Sensor, Kelengkapan, Daya, Bobot).</p>
                    </div>
                    <button
                        type="button"
                        x-on:click="addSpec()"
                        class="min-h-[36px] px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 text-xs font-semibold hover:bg-indigo-100 transition"
                    >
                        + Tambah Atribut
                    </button>
                </div>

                <div class="space-y-2">
                    <template x-for="(spec, index) in specs" :key="index">
                        <div class="flex items-center gap-2">
                            <input
                                type="text"
                                name="specs_keys[]"
                                x-model="spec.key"
                                placeholder="Nama Atribut (misal: Mount)"
                                class="w-1/3 min-h-[40px] px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            <input
                                type="text"
                                name="specs_values[]"
                                x-model="spec.value"
                                placeholder="Nilai (misal: Sony E-mount)"
                                class="flex-1 min-h-[40px] px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            <button
                                type="button"
                                x-on:click="removeSpec(index)"
                                class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg min-h-[40px] min-w-[40px] flex items-center justify-center"
                                title="Hapus atribut"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </x-card>

            <!-- Foto Unit (Multi Upload + GD Auto-Resize) -->
            <x-card class="p-6 space-y-4">
                <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Galeri Foto Unit</h3>
                <p class="text-xs text-slate-500">
                    Pilih satu atau beberapa foto (JPG, PNG, WebP). Foto pertama akan otomatis menjadi foto utama. Gambar berukuran besar otomatis di-resize (max lebar 1200px).
                </p>

                <div class="mt-2 space-y-3">
                    <input
                        type="file"
                        name="images[]"
                        multiple
                        accept="image/*"
                        x-on:change="handleImages($event)"
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
                    />

                    <!-- Image Preview Grid -->
                    <template x-if="previewImages.length > 0">
                        <div class="pt-2">
                            <span class="text-xs font-semibold text-slate-600 block mb-2">Pratinjau Foto yang Dipilih (<span x-text="previewImages.length"></span> foto):</span>
                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                                <template x-for="(img, idx) in previewImages" :key="idx">
                                    <div class="relative group rounded-xl overflow-hidden border border-slate-200 bg-slate-50 aspect-square">
                                        <img :src="img.url" :alt="img.name" class="w-full h-full object-cover" />
                                        <div class="absolute bottom-0 inset-x-0 bg-slate-900/70 text-white text-[10px] px-1.5 py-0.5 truncate text-center" x-text="idx === 0 ? '★ Foto Utama' : 'Foto ' + (idx + 1)"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </x-card>

            <div class="flex items-center justify-end gap-3 pt-4">
                <a
                    href="{{ route('admin.items.index') }}"
                    class="inline-flex items-center justify-center min-h-[44px] px-5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition"
                >
                    Batal
                </a>
                <x-button type="submit" variant="primary" class="px-6">
                    Simpan Unit Rental
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.admin>
