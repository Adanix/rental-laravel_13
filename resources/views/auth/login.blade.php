<x-layouts.app title="Masuk Akun">
    <div class="max-w-md mx-auto px-4 py-12 sm:py-16">
        <div class="text-center mb-8">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Masuk ke Akun Anda</h2>
            <p class="mt-2 text-sm text-slate-600">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">
                    Daftar sekarang
                </a>
            </p>
        </div>

        <x-card class="p-6 sm:p-8">
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <x-input
                    label="Nomor WhatsApp / HP atau Email"
                    name="identifier"
                    type="text"
                    placeholder="Contoh: 081200000001 atau admin@rental.local"
                    required
                />

                <x-input
                    label="Kata Sandi"
                    name="password"
                    type="password"
                    placeholder="••••••••"
                    required
                />

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                        <span class="text-sm text-slate-600">Ingat saya</span>
                    </label>
                </div>

                <x-button type="submit" variant="primary" class="w-full">
                    Masuk Sekarang
                </x-button>
            </form>

            <!-- Quick Demo Accounts Helper -->
            <div class="mt-8 pt-6 border-t border-slate-100">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 text-center mb-3">Akun Demo (Klik untuk Isi Cepat)</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                    <button
                        type="button"
                        onclick="fillLogin('081200000001', 'password')"
                        class="p-2.5 rounded-xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/50 text-left transition"
                    >
                        <span class="font-bold text-slate-800 block">Admin</span>
                        <span class="text-slate-500 text-[11px]">081200000001</span>
                    </button>
                    <button
                        type="button"
                        onclick="fillLogin('081200000002', 'password')"
                        class="p-2.5 rounded-xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/50 text-left transition"
                    >
                        <span class="font-bold text-slate-800 block">Staff</span>
                        <span class="text-slate-500 text-[11px]">081200000002</span>
                    </button>
                    <button
                        type="button"
                        onclick="fillLogin('081200000003', 'password')"
                        class="p-2.5 rounded-xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/50 text-left transition"
                    >
                        <span class="font-bold text-slate-800 block">Pelanggan</span>
                        <span class="text-slate-500 text-[11px]">081200000003</span>
                    </button>
                </div>
            </div>
        </x-card>
    </div>

    @push('scripts')
    <script>
        function fillLogin(id, pwd) {
            document.querySelector('input[name="identifier"]').value = id;
            document.querySelector('input[name="password"]').value = pwd;
        }
    </script>
    @endpush
</x-layouts.app>
