@props([
    'class' => '',
])

<div {{ $attributes->merge(['class' => "bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden $class"]) }}>
    {{ $slot }}
</div>
