<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'error' => null,
    'class' => '',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'error' => null,
    'class' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$inputId = $id ?? $name ?? 'input-' . uniqid();
?>

<div class="w-full">
    <?php if($label): ?>
        <label for="<?php echo e($inputId); ?>" class="block text-sm font-medium text-slate-700 mb-1.5">
            <?php echo e($label); ?>

            <?php if($required): ?>
                <span class="text-rose-500 font-bold">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <input
        id="<?php echo e($inputId); ?>"
        name="<?php echo e($name); ?>"
        type="<?php echo e($type); ?>"
        value="<?php echo e($value ?? old($name)); ?>"
        placeholder="<?php echo e($placeholder); ?>"
        <?php if($required): ?> required <?php endif; ?>
        <?php echo e($attributes->merge([
            'class' => 'w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all' . ($errors->has($name) || $error ? ' border-rose-400 ring-1 ring-rose-400' : '') . ' ' . $class
        ])); ?>

    />

    <?php if($name && $errors->has($name)): ?>
        <p class="mt-1.5 text-xs text-rose-600 font-medium"><?php echo e($errors->first($name)); ?></p>
    <?php elseif($error): ?>
        <p class="mt-1.5 text-xs text-rose-600 font-medium"><?php echo e($error); ?></p>
    <?php endif; ?>
</div>
<?php /**PATH /var/www/html/resources/views/components/input.blade.php ENDPATH**/ ?>