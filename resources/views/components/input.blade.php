@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'error' => null,
    'class' => '',
])

@php
$inputId = $id ?? $name ?? 'input-' . uniqid();
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-slate-700 mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-rose-500 font-bold">*</span>
            @endif
        </label>
    @endif

    <input
        id="{{ $inputId }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ $value ?? old($name) }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        {{ $attributes->merge([
            'class' => 'w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all' . ($errors->has($name) || $error ? ' border-rose-400 ring-1 ring-rose-400' : '') . ' ' . $class
        ]) }}
    />

    @if($name && $errors->has($name))
        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $errors->first($name) }}</p>
    @elseif($error)
        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $error }}</p>
    @endif
</div>
