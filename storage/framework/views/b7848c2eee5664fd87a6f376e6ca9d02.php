<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => $item->name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->name)]); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-xs sm:text-sm text-slate-500 mb-6 sm:mb-8 overflow-x-auto whitespace-nowrap">
            <a href="<?php echo e(route('home')); ?>" class="hover:text-indigo-600 transition">Katalog</a>
            <span>/</span>
            <a href="<?php echo e(route('home', ['category' => $item->category->slug])); ?>" class="hover:text-indigo-600 transition">
                <?php echo e($item->category->name); ?>

            </a>
            <span>/</span>
            <span class="text-slate-900 font-semibold truncate"><?php echo e($item->name); ?></span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            <!-- Left Column: Gallery & Description & Specs (7 cols) -->
            <div class="lg:col-span-7 space-y-8">
                <!-- Photo Gallery (Alpine.js) -->
                <?php
                    $imagesList = $item->images->isNotEmpty()
                        ? $item->images->map(fn($img) => $img->url)->all()
                        : [$item->primary_image_url];
                ?>
                <div
                    x-data="{
                        images: <?php echo e(Js::from($imagesList)); ?>,
                        activeIndex: 0
                    }"
                    class="space-y-3"
                >
                    <!-- Main Image Display -->
                    <div class="relative aspect-4/3 sm:aspect-16/10 rounded-2xl overflow-hidden bg-slate-100 border border-slate-200 shadow-xs">
                        <img
                            :src="images[activeIndex]"
                            alt="<?php echo e($item->name); ?>"
                            class="w-full h-full object-cover transition-all duration-300"
                        />
                    </div>

                    <!-- Thumbnails Row -->
                    <?php if(count($imagesList) > 1): ?>
                        <div class="flex items-center gap-2.5 overflow-x-auto pb-2">
                            <template x-for="(img, idx) in images" :key="idx">
                                <button
                                    type="button"
                                    x-on:click="activeIndex = idx"
                                    :class="activeIndex === idx ? 'ring-2 ring-indigo-600 ring-offset-2 border-indigo-600' : 'opacity-70 hover:opacity-100 border-slate-200'"
                                    class="w-20 h-16 rounded-xl overflow-hidden border shrink-0 transition cursor-pointer"
                                >
                                    <img :src="img" class="w-full h-full object-cover" />
                                </button>
                            </template>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'p-6 sm:p-8 space-y-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'p-6 sm:p-8 space-y-4']); ?>
                    <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3">Deskripsi Unit</h2>
                    <div class="text-sm sm:text-base text-slate-700 leading-relaxed whitespace-pre-line">
                        <?php echo e($item->description ?: 'Tidak ada deskripsi detail untuk unit ini.'); ?>

                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>

                <!-- Technical Specs -->
                <?php if($item->specs): ?>
                    <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'p-6 sm:p-8 space-y-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'p-6 sm:p-8 space-y-4']); ?>
                        <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3">Spesifikasi & Kelengkapan</h2>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            <?php $__currentLoopData = $item->specs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-0.5"><?php echo e($key); ?></dt>
                                    <dd class="font-medium text-slate-800"><?php echo e($val); ?></dd>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </dl>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Right Column: Pricing & Live Booking Widget (5 cols) -->
            <div class="lg:col-span-5 sticky top-24">
                <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'p-6 sm:p-8 space-y-6 shadow-md border-indigo-100']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'p-6 sm:p-8 space-y-6 shadow-md border-indigo-100']); ?>
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                <?php echo e($item->category->name); ?>

                            </span>
                            <span class="text-xs font-medium text-slate-500">
                                Total Stok: <strong class="text-slate-800"><?php echo e($item->stock); ?></strong> unit
                            </span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight leading-snug">
                            <?php echo e($item->name); ?>

                        </h1>
                    </div>

                    <!-- Pricing Display -->
                    <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-100 space-y-2">
                        <div class="flex items-baseline justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-indigo-900">Tarif Sewa</span>
                            <div>
                                <span class="text-2xl sm:text-3xl font-extrabold text-indigo-600"><?php echo e(format_rupiah($item->price_per_day)); ?></span>
                                <span class="text-xs font-medium text-slate-500">/hari</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-600 pt-2 border-t border-indigo-100/80">
                            <span>Deposit Jaminan (Dikembalikan):</span>
                            <span class="font-bold text-slate-800"><?php echo e(format_rupiah($item->deposit)); ?></span>
                        </div>
                    </div>

                    <!-- Live Date Availability & Quick Calculator Widget (Alpine.js) -->
                    <div
                        x-data="{
                            startAt: '<?php echo e(request('start_at', now()->addDay()->format('Y-m-d\T09:00'))); ?>',
                            endAt: '<?php echo e(request('end_at', now()->addDays(2)->format('Y-m-d\T09:00'))); ?>',
                            qty: 1,
                            maxStock: <?php echo e($item->stock); ?>,
                            unitPrice: <?php echo e($item->price_per_day); ?>,
                            unitDeposit: <?php echo e($item->deposit); ?>,
                            checking: false,
                            checked: false,
                            isAvailable: true,
                            availableStock: <?php echo e($item->stock); ?>,

                            get durationDays() {
                                if (!this.startAt || !this.endAt) return 1;
                                const s = new Date(this.startAt);
                                const e = new Date(this.endAt);
                                if (e <= s) return 1;
                                const diffHours = (e - s) / (1000 * 60 * 60);
                                return Math.max(1, Math.ceil(diffHours / 24));
                            },

                            get rentalSubtotal() {
                                return this.durationDays * this.unitPrice * this.qty;
                            },

                            get depositTotal() {
                                return this.unitDeposit * this.qty;
                            },

                            get grandTotal() {
                                return this.rentalSubtotal + this.depositTotal;
                            },

                            formatRp(num) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
                            },

                            async checkLiveAvailability() {
                                if (!this.startAt || !this.endAt) return;
                                const s = new Date(this.startAt);
                                const e = new Date(this.endAt);
                                if (e <= s) return;

                                this.checking = true;
                                try {
                                    const res = await fetch('<?php echo e(route('items.check-availability', $item)); ?>', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                                        },
                                        body: JSON.stringify({
                                            start_at: this.startAt,
                                            end_at: this.endAt
                                        })
                                    });
                                    const data = await res.json();
                                    this.availableStock = data.stock_available;
                                    this.isAvailable = data.stock_available >= this.qty;
                                    this.checked = true;
                                } catch (err) {
                                    console.error(err);
                                } finally {
                                    this.checking = false;
                                }
                            }
                        }"
                        x-init="checkLiveAvailability()"
                        class="space-y-4"
                    >
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Pilih Jadwal & Cek Ketersediaan Live
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Mulai Sewa (Ambil)</label>
                                <input
                                    type="datetime-local"
                                    x-model="startAt"
                                    x-on:change="checkLiveAvailability()"
                                    class="w-full min-h-[44px] px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Selesai Sewa (Kembali)</label>
                                <input
                                    type="datetime-local"
                                    x-model="endAt"
                                    x-on:change="checkLiveAvailability()"
                                    class="w-full min-h-[44px] px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                />
                            </div>
                        </div>

                        <!-- Qty Selector -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Unit (Qty)</label>
                            <div class="flex items-center gap-3">
                                <button
                                    type="button"
                                    x-on:click="if (qty > 1) { qty--; checkLiveAvailability(); }"
                                    class="w-10 h-10 rounded-xl border border-slate-300 flex items-center justify-center font-bold text-slate-700 hover:bg-slate-100"
                                >
                                    -
                                </button>
                                <span x-text="qty" class="w-8 text-center font-bold text-base text-slate-900"></span>
                                <button
                                    type="button"
                                    x-on:click="if (qty < maxStock) { qty++; checkLiveAvailability(); }"
                                    class="w-10 h-10 rounded-xl border border-slate-300 flex items-center justify-center font-bold text-slate-700 hover:bg-slate-100"
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        <!-- Live Availability Status Banner -->
                        <div x-show="checked" class="transition-all" style="display: none;">
                            <template x-if="isAvailable">
                                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-2.5 text-xs font-semibold">
                                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Tersedia untuk jadwal ini! (Tersisa <strong x-text="availableStock"></strong> unit)</span>
                                </div>
                            </template>
                            <template x-if="!isAvailable">
                                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-2.5 text-xs font-semibold">
                                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Maaf, unit penuh / tidak tersedia pada jadwal yang dipilih.</span>
                                </div>
                            </template>
                        </div>

                        <!-- Live Price Breakdown -->
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-2">
                            <div class="flex justify-between text-slate-600">
                                <span>Durasi Sewa:</span>
                                <span class="font-bold text-slate-900" x-text="durationDays + ' Hari'"></span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Subtotal Sewa (<span x-text="durationDays"></span> hari x <span x-text="qty"></span> unit):</span>
                                <span class="font-bold text-slate-900" x-text="formatRp(rentalSubtotal)"></span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Deposit Jaminan (<span x-text="qty"></span> unit):</span>
                                <span class="font-bold text-slate-900" x-text="formatRp(depositTotal)"></span>
                            </div>
                            <div class="flex justify-between text-sm font-extrabold text-indigo-700 pt-2 border-t border-slate-200">
                                <span>Estimasi Total Awal:</span>
                                <span x-text="formatRp(grandTotal)"></span>
                            </div>
                        </div>

                        <!-- Booking Action CTA Button -->
                        <form method="GET" action="<?php echo e(route('bookings.create')); ?>">
                            <input type="hidden" name="item_id" value="<?php echo e($item->id); ?>" />
                            <input type="hidden" name="start_at" :value="startAt" />
                            <input type="hidden" name="end_at" :value="endAt" />
                            <input type="hidden" name="qty" :value="qty" />

                            <button
                                type="submit"
                                :disabled="!isAvailable || checking"
                                class="w-full min-h-[48px] px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white text-base font-bold transition shadow-md flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <span x-show="!checking">Booking Unit Sekarang &rarr;</span>
                                <span x-show="checking" style="display: none;">Memeriksa Ketersediaan...</span>
                            </button>
                        </form>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
            </div>
        </div>

        <!-- Related Items Section -->
        <?php if($relatedItems->isNotEmpty()): ?>
            <div class="mt-16 pt-12 border-t border-slate-200">
                <h2 class="text-xl font-bold text-slate-900 mb-6">Unit Terkait Lainnya</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php $__currentLoopData = $relatedItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition overflow-hidden flex flex-col">
                            <div class="aspect-4/3 bg-slate-100 overflow-hidden">
                                <img src="<?php echo e($rel->primary_image_url); ?>" alt="<?php echo e($rel->name); ?>" class="w-full h-full object-cover" loading="lazy" />
                            </div>
                            <div class="p-4 flex-1 flex flex-col justify-between">
                                <div>
                                    <span class="text-[11px] font-semibold text-indigo-600 uppercase"><?php echo e($rel->category->name); ?></span>
                                    <h4 class="font-bold text-slate-900 text-sm mt-1 line-clamp-1">
                                        <a href="<?php echo e(route('items.show', $rel->slug)); ?>" class="hover:underline">
                                            <?php echo e($rel->name); ?>

                                        </a>
                                    </h4>
                                </div>
                                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
                                    <span class="font-extrabold text-indigo-600 text-sm"><?php echo e(format_rupiah($rel->price_per_day)); ?><span class="text-xs text-slate-400 font-normal">/hr</span></span>
                                    <a href="<?php echo e(route('items.show', $rel->slug)); ?>" class="text-xs font-semibold text-slate-700 hover:text-indigo-600">
                                        Detail &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
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
<?php /**PATH /var/www/html/resources/views/catalog/show.blade.php ENDPATH**/ ?>