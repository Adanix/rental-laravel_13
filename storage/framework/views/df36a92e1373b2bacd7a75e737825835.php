<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Katalog Unit Rental']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Katalog Unit Rental']); ?>
    <!-- Hero Section -->
    <section class="bg-gradient-to-b from-indigo-900 via-indigo-800 to-indigo-950 text-white py-12 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 mb-4">
                    ✨ Solusi Rental Cepat & Transparan
                </span>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">
                    Sewa Unit & Peralatan Terbaik Tanpa Ribet
                </h1>
                <p class="mt-4 text-base sm:text-lg text-indigo-100 max-w-2xl leading-relaxed">
                    Kamera, audio, alat camping, hingga proyektor. Cek ketersediaan tanggal secara live, rincian biaya transparan, dan jaminan unit prima.
                </p>
            </div>

            <!-- Date & Search Form Card -->
            <div class="mt-8 bg-white text-slate-800 rounded-2xl p-4 sm:p-6 shadow-xl border border-white/20">
                <form method="GET" action="<?php echo e(route('home')); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                    <!-- Search input -->
                    <div>
                        <label for="q" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                            Cari Barang
                        </label>
                        <input
                            id="q"
                            type="text"
                            name="q"
                            value="<?php echo e(request('q')); ?>"
                            placeholder="Kamera, drone, tenda..."
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                        />
                    </div>

                    <!-- Date Start -->
                    <div>
                        <label for="start_at" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                            Mulai Sewa (Ambil)
                        </label>
                        <input
                            id="start_at"
                            type="datetime-local"
                            name="start_at"
                            value="<?php echo e(request('start_at')); ?>"
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                        />
                    </div>

                    <!-- Date End -->
                    <div>
                        <label for="end_at" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                            Selesai Sewa (Kembali)
                        </label>
                        <input
                            id="end_at"
                            type="datetime-local"
                            name="end_at"
                            value="<?php echo e(request('end_at')); ?>"
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                        />
                    </div>

                    <!-- Submit & Reset -->
                    <div class="flex items-center gap-2">
                        <button
                            type="submit"
                            class="flex-1 min-h-[44px] px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm transition shadow-sm flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Cek Unit
                        </button>

                        <?php if(request()->hasAny(['q', 'category', 'start_at', 'end_at', 'min_price', 'max_price'])): ?>
                            <a
                                href="<?php echo e(route('home')); ?>"
                                class="min-h-[44px] px-3 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 flex items-center justify-center text-sm"
                                title="Reset Filter"
                            >
                                Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Main Catalog Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
        <!-- Category Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-8 no-scrollbar">
            <a
                href="<?php echo e(route('home', request()->except('category', 'page'))); ?>"
                class="min-h-[40px] px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition <?php echo e(! request('category') ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'); ?>"
            >
                Semua Kategori
            </a>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a
                    href="<?php echo e(route('home', array_merge(request()->except('page'), ['category' => $cat->slug]))); ?>"
                    class="min-h-[40px] px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition <?php echo e(request('category') === $cat->slug ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'); ?>"
                >
                    <?php echo e($cat->name); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Filter Status Alert if Date filtered -->
        <?php if($startAt && $endAt): ?>
            <div class="mb-6 p-4 rounded-2xl bg-indigo-50 border border-indigo-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2.5 text-sm text-indigo-900 font-medium">
                    <svg class="w-5 h-5 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Menampilkan unit yang tersedia untuk periode <strong><?php echo e(format_date_id($startAt)); ?></strong> s/d <strong><?php echo e(format_date_id($endAt)); ?></strong></span>
                </div>
                <a href="<?php echo e(route('home', request()->except(['start_at', 'end_at']))); ?>" class="text-xs font-semibold text-indigo-600 hover:underline">
                    Hapus Filter Tanggal
                </a>
            </div>
        <?php endif; ?>

        <!-- Items Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="group bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col overflow-hidden">
                    <!-- Image Thumbnail -->
                    <div class="relative aspect-4/3 bg-slate-100 overflow-hidden">
                        <img
                            src="<?php echo e($item->primary_image_url); ?>"
                            alt="<?php echo e($item->name); ?>"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                            loading="lazy"
                        />
                        <div class="absolute top-3 left-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-white/90 text-slate-800 backdrop-blur-xs shadow-xs">
                                <?php echo e($item->category->name); ?>

                            </span>
                        </div>
                        <div class="absolute top-3 right-3">
                            <?php if(isset($item->calculated_available_stock)): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-bold bg-emerald-600 text-white shadow-xs">
                                    Sisa <?php echo e($item->calculated_available_stock); ?> unit
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-bold bg-slate-900/80 text-white shadow-xs">
                                    Stok: <?php echo e($item->stock); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Item Details -->
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <h3 class="font-bold text-slate-900 group-hover:text-indigo-600 transition leading-snug line-clamp-2">
                                <a href="<?php echo e(route('items.show', $item->slug)); ?>">
                                    <?php echo e($item->name); ?>

                                </a>
                            </h3>

                            <?php if($item->specs): ?>
                                <div class="mt-2.5 flex flex-wrap gap-1.5">
                                    <?php $__currentLoopData = array_slice($item->specs, 0, 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[11px] font-medium truncate max-w-[150px]">
                                            <?php echo e($k); ?>: <?php echo e($v); ?>

                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Pricing & CTA -->
                        <div class="pt-3 border-t border-slate-100">
                            <div class="flex items-baseline justify-between mb-3">
                                <div>
                                    <span class="text-xs text-slate-400 block font-medium">Tarif Sewa</span>
                                    <span class="text-lg font-extrabold text-indigo-600"><?php echo e(format_rupiah($item->price_per_day)); ?></span>
                                    <span class="text-xs text-slate-500">/hari</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-slate-400 block font-medium">Deposit</span>
                                    <span class="text-xs font-semibold text-slate-700"><?php echo e(format_rupiah($item->deposit)); ?></span>
                                </div>
                            </div>

                            <a
                                href="<?php echo e(route('items.show', array_merge(['slug' => $item->slug], request()->only(['start_at', 'end_at'])))); ?>"
                                class="w-full min-h-[44px] flex items-center justify-center px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-indigo-600 text-white text-sm font-semibold transition shadow-xs"
                            >
                                Detail & Booking &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full py-16 text-center bg-white rounded-2xl border border-slate-200">
                    <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Unit Tidak Ditemukan</h3>
                    <p class="mt-1 text-sm text-slate-500 max-w-sm mx-auto">
                        Tidak ada unit rental yang sesuai dengan kata kunci atau filter tanggal yang dipilih.
                    </p>
                    <a href="<?php echo e(route('home')); ?>" class="mt-4 inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold text-indigo-600 hover:bg-indigo-50">
                        Lihat Semua Unit
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if($items->hasPages()): ?>
            <div class="mt-10">
                <?php echo e($items->links()); ?>

            </div>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/catalog/index.blade.php ENDPATH**/ ?>