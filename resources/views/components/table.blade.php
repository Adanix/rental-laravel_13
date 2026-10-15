@props([
    'class' => '',
])

<div class="overflow-x-auto w-full rounded-xl border border-slate-200">
    <table {{ $attributes->merge(['class' => "min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700 $class"]) }}>
        {{ $slot }}
    </table>
</div>
