<x-layouts.app title="Daftar Akun Baru">
    <div class="max-w-md mx-auto px-4 py-12 sm:py-16">
        <div class="text-center mb-8">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Daftar Akun Baru</h2>
            <p class="mt-2 text-sm text-slate-600">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">
                    Masuk di sini
                </a>
            </p>
        </div>

        <x-card class="p-6 sm:p-8">
            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <x-input
                    label="Nama Lengkap"
                    name="name"
                    type="text"
                    placeholder="Nama lengkap Anda"
                    required
                />

                <x-input
                    label="Nomor WhatsApp / HP"
                    name="phone"
                    type="tel"
                    placeholder="Contoh: 081234567890"
                    required
                />

                <x-input
                    label="Alamat Email (Opsional)"
                    name="email"
                    type="email"
                    placeholder="nama@email.com"
                />

                <x-input
                    label="Kata Sandi (Min. 6 Karakter)"
                    name="password"
                    type="password"
                    placeholder="••••••••"
                    required
                />

                <x-input
                    label="Konfirmasi Kata Sandi"
                    name="password_confirmation"
                    type="password"
                    placeholder="••••••••"
                    required
                />

                <div class="pt-2">
                    <x-button type="submit" variant="primary" class="w-full">
                        Daftar Sekarang
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
