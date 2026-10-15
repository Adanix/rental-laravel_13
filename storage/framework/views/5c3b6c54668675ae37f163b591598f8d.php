<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title ?? 'Sewa Unit Mudah & Cepat'); ?> - <?php echo e(\App\Models\Setting::get('store_name', 'RentalHub Nusantara')); ?></title>
    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="flex flex-col min-h-full font-sans text-slate-800 antialiased selection:bg-indigo-500 selection:text-white">
    <!-- Navigation Bar -->
    <header x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-3">
                    <a href="<?php echo e(url('/')); ?>" class="flex items-center gap-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-xl p-1">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-md shadow-indigo-200">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-lg font-bold tracking-tight text-slate-900 block leading-tight">
                                <?php echo e(\App\Models\Setting::get('store_name', 'RentalHub Nusantara')); ?>

                            </span>
                            <span class="text-xs text-slate-500 font-medium hidden sm:block">Rental Serbaguna Cepat & Terpercaya</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center gap-6">
                    <a href="<?php echo e(url('/')); ?>" class="text-sm font-semibold <?php echo e(request()->is('/') ? 'text-indigo-600' : 'text-slate-600 hover:text-indigo-600'); ?> transition">
                        Katalog Unit
                    </a>
                    
                    <?php if(auth()->guard()->check()): ?>
                        <a href="<?php echo e(route('my-bookings.index')); ?>" class="text-sm font-semibold <?php echo e(request()->routeIs('my-bookings.*') ? 'text-indigo-600' : 'text-slate-600 hover:text-indigo-600'); ?> transition">
                            Pesanan Saya
                        </a>

                        <?php if(auth()->user()->isStaff()): ?>
                            <a href="<?php echo e(route('admin.dashboard')); ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Panel Staff
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </nav>

                <!-- Right Side Actions (Auth / User) -->
                <div class="hidden md:flex items-center gap-3">
                    <?php if(auth()->guard()->check()): ?>
                        <div class="flex items-center gap-3 pl-3 border-l border-slate-200">
                            <div class="text-right">
                                <div class="text-sm font-semibold text-slate-900 leading-tight"><?php echo e(auth()->user()->name); ?></div>
                                <div class="text-xs text-slate-500"><?php echo e(auth()->user()->phone); ?></div>
                            </div>
                            <form method="POST" action="<?php echo e(route('logout')); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 rounded-xl hover:bg-rose-50 transition min-h-[44px] flex items-center justify-center" title="Keluar">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>" class="min-h-[44px] px-4 py-2.5 text-sm font-semibold text-slate-700 hover:text-indigo-600 transition flex items-center">
                            Masuk
                        </a>
                        <a href="<?php echo e(route('register')); ?>" class="min-h-[44px] px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-xs transition flex items-center">
                            Daftar Akun
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="flex md:hidden items-center gap-2">
                    <button
                        type="button"
                        x-on:click="mobileMenuOpen = !mobileMenuOpen"
                        class="min-h-[44px] min-w-[44px] p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        aria-label="Buka Menu"
                    >
                        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div
            x-show="mobileMenuOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="md:hidden border-b border-slate-200 bg-white px-4 pt-3 pb-6 space-y-2"
            style="display: none;"
        >
            <a href="<?php echo e(url('/')); ?>" class="block px-3 py-2.5 rounded-xl text-base font-medium text-slate-800 hover:bg-slate-100">
                Katalog Unit
            </a>
            <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('my-bookings.index')); ?>" class="block px-3 py-2.5 rounded-xl text-base font-medium text-slate-800 hover:bg-slate-100">
                    Pesanan Saya
                </a>
                <?php if(auth()->user()->isStaff()): ?>
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="block px-3 py-2.5 rounded-xl text-base font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                        Panel Kelola Staff & Admin
                    </a>
                <?php endif; ?>
                <div class="pt-4 mt-2 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <div class="font-semibold text-slate-900"><?php echo e(auth()->user()->name); ?></div>
                        <div class="text-xs text-slate-500"><?php echo e(auth()->user()->phone); ?></div>
                    </div>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="min-h-[44px] px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 rounded-xl">
                            Keluar
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 gap-2 pt-3 border-t border-slate-100">
                    <a href="<?php echo e(route('login')); ?>" class="min-h-[44px] flex items-center justify-center px-4 py-2 text-sm font-semibold text-slate-700 bg-slate-100 rounded-xl">
                        Masuk
                    </a>
                    <a href="<?php echo e(route('register')); ?>" class="min-h-[44px] flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-xl">
                        Daftar
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1">
        <?php echo e($slot); ?>

    </main>

    <!-- Global Toast Notifications -->
    <?php if (isset($component)) { $__componentOriginal7cfab914afdd05940201ca0b2cbc009b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.toast','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('toast'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $attributes = $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $component = $__componentOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-16 print:hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Info Toko -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <span class="font-bold text-slate-900"><?php echo e(\App\Models\Setting::get('store_name', 'RentalHub Nusantara')); ?></span>
                    </div>
                    <p class="text-sm text-slate-600 max-w-sm">
                        Layanan rental barang dan perlengkapan berkualitas. Booking transparan, cek ketersediaan otomatis, dan proses serah terima cepat.
                    </p>
                </div>

                <!-- Kontak & Alamat -->
                <div class="space-y-2 text-sm text-slate-600">
                    <h4 class="font-semibold text-slate-900 mb-3">Kontak & Lokasi</h4>
                    <p class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-slate-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span><?php echo e(\App\Models\Setting::get('store_address', 'Jl. Thamrin No. 88, Menteng, Jakarta Pusat')); ?></span>
                    </p>
                    <p class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span>WhatsApp: <?php echo e(\App\Models\Setting::get('store_phone', '081234567890')); ?></span>
                    </p>
                </div>

                <!-- Bantuan Cepat -->
                <div>
                    <h4 class="font-semibold text-slate-900 mb-3">Butuh Bantuan?</h4>
                    <?php
                        $storePhone = preg_replace('/[^0-9]/', '', \App\Models\Setting::get('store_phone', '081234567890'));
                        if (str_starts_with($storePhone, '0')) {
                            $storePhone = '62' . substr($storePhone, 1);
                        }
                    ?>
                    <a
                        href="https://wa.me/<?php echo e($storePhone); ?>?text=Halo%20Admin%2C%20saya%20ingin%20tanya%20seputar%20unit%20rental"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-2 min-h-[44px] px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm transition shadow-xs"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.044c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z" />
                        </svg>
                        Hubungi Admin WhatsApp
                    </a>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-slate-100 text-center text-xs text-slate-400">
                &copy; <?php echo e(date('Y')); ?> <?php echo e(\App\Models\Setting::get('store_name', 'RentalHub Nusantara')); ?>. Hak Cipta Dilindungi.
            </div>
        </div>
    </footer>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/components/layouts/app.blade.php ENDPATH**/ ?>