@props([
    'variant' => 'primary',
    'type' => 'button',
    'class' => '',
])

@php
$variants = [
    'primary' => 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm focus:ring-indigo-500 border-transparent',
    'secondary' => 'bg-white hover:bg-slate-50 text-slate-700 border-slate-300 shadow-xs focus:ring-indigo-500',
    'success' => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm focus:ring-emerald-500 border-transparent',
    'danger' => 'bg-rose-600 hover:bg-rose-700 text-white shadow-sm focus:ring-rose-500 border-transparent',
    'outline' => 'bg-transparent hover:bg-slate-100 text-slate-700 border-slate-300 focus:ring-slate-500',
];
$style = $variants[$variant] ?? $variants['primary'];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => "inline-flex items-center justify-center min-h-[44px] px-4 py-2.5 border text-sm font-medium rounded-xl transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer $style $class"]) }}
>
    {{ $slot }}
</button>
