<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'primary',
    'type' => 'button',
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
    'variant' => 'primary',
    'type' => 'button',
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
$variants = [
    'primary' => 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm focus:ring-indigo-500 border-transparent',
    'secondary' => 'bg-white hover:bg-slate-50 text-slate-700 border-slate-300 shadow-xs focus:ring-indigo-500',
    'success' => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm focus:ring-emerald-500 border-transparent',
    'danger' => 'bg-rose-600 hover:bg-rose-700 text-white shadow-sm focus:ring-rose-500 border-transparent',
    'outline' => 'bg-transparent hover:bg-slate-100 text-slate-700 border-slate-300 focus:ring-slate-500',
];
$style = $variants[$variant] ?? $variants['primary'];
?>

<button
    type="<?php echo e($type); ?>"
    <?php echo e($attributes->merge(['class' => "inline-flex items-center justify-center min-h-[44px] px-4 py-2.5 border text-sm font-medium rounded-xl transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer $style $class"])); ?>

>
    <?php echo e($slot); ?>

</button>
<?php /**PATH /var/www/html/resources/views/components/button.blade.php ENDPATH**/ ?>