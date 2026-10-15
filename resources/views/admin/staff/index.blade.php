<x-layouts.admin title="Manajemen Staff">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Tim Operasional & Admin</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola akun staff dan hak akses administrator sistem.</p>
            </div>
            <button
                type="button"
                x-data
                x-on:click="$dispatch('open-modal', 'create-staff-modal')"
                class="inline-flex items-center gap-2 min-h-[44px] px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-xs sm:text-sm font-semibold hover:bg-indigo-700 shadow-sm transition"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Akun Staff
            </button>
        </div>

        <!-- Staff Table Card -->
        <x-card class="overflow-hidden">
            <x-table>
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-4 py-3.5">Nama & Kontak</th>
                        <th class="px-4 py-3.5">Email</th>
                        <th class="px-4 py-3.5 text-center">Peran (Role)</th>
                        <th class="px-4 py-3.5 text-center">Bergabung</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    @foreach($staffMembers as $staff)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900">{{ $staff->name }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $staff->phone }}</div>
                            </td>
                            <td class="px-4 py-3.5 text-slate-600">
                                {{ $staff->email ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $staff->isAdmin() ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $staff->isAdmin() ? 'Administrator' : 'Staff Operasional' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center text-xs text-slate-400">
                                {{ $staff->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        type="button"
                                        x-data
                                        x-on:click="$dispatch('open-modal', 'edit-staff-{{ $staff->id }}')"
                                        class="min-h-[34px] px-3 py-1 rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                    >
                                        Edit
                                    </button>

                                    @if($staff->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.staff.destroy', $staff->id) }}" onsubmit="return confirm('Hapus akun staff ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="min-h-[34px] px-3 py-1 rounded-lg border border-rose-300 text-rose-700 hover:bg-rose-50 text-xs font-semibold"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                <!-- Edit Staff Modal -->
                                <x-modal :name="'edit-staff-' . $staff->id" title="Edit Akun Staff">
                                    <form method="POST" action="{{ route('admin.staff.update', $staff->id) }}" class="space-y-4 text-left">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                                            <input type="text" name="name" value="{{ $staff->name }}" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp / HP *</label>
                                            <input type="text" name="phone" value="{{ $staff->phone }}" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
                                            <input type="email" name="email" value="{{ $staff->email }}" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Peran (Role) *</label>
                                            <select name="role" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                                                <option value="staff" @selected($staff->role->value === 'staff')>Staff Operasional</option>
                                                <option value="admin" @selected($staff->role->value === 'admin')>Administrator</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Ganti Password (Kosongkan jika tidak diubah)</label>
                                            <input type="password" name="password" minlength="6" placeholder="Minimal 6 karakter" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                                        </div>
                                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                                            <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'edit-staff-{{ $staff->id }}')">Batal</x-button>
                                            <x-button type="submit" variant="primary">Simpan Perubahan</x-button>
                                        </div>
                                    </form>
                                </x-modal>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-table>
        </x-card>
    </div>

    <!-- Create Staff Modal -->
    <x-modal name="create-staff-modal" title="Tambah Akun Tim / Staff Baru">
        <form method="POST" action="{{ route('admin.staff.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="create_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                <input type="text" name="name" id="create_name" required placeholder="Contoh: Rian Anggara" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
            </div>

            <div>
                <label for="create_phone" class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp / HP *</label>
                <input type="text" name="phone" id="create_phone" required placeholder="08123456789" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono">
            </div>

            <div>
                <label for="create_email" class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email (Opsional)</label>
                <input type="email" name="email" id="create_email" placeholder="staff@rentalhub.id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
            </div>

            <div>
                <label for="create_role" class="block text-xs font-semibold text-slate-700 mb-1">Peran Akun *</label>
                <select name="role" id="create_role" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    <option value="staff">Staff Operasional</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>

            <div>
                <label for="create_password" class="block text-xs font-semibold text-slate-700 mb-1">Password Masuk *</label>
                <input type="password" name="password" id="create_password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'create-staff-modal')">Batal</x-button>
                <x-button type="submit" variant="primary">Buat Akun</x-button>
            </div>
        </form>
    </x-modal>
</x-layouts.admin>
