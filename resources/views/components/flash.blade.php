@props(['type' => 'success'])

@php
    $styles = [
        'success' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'error' => 'bg-red-50 text-red-800 border-red-200',
        'info' => 'bg-sky-50 text-sky-800 border-sky-200',
    ][$type] ?? 'bg-slate-50 text-slate-800 border-slate-200';
@endphp

<div x-data="{ show: true }" x-show="show" x-transition
     {{ $attributes->merge(['class' => "flex items-start gap-3 p-4 mb-5 text-sm border rounded-lg $styles"]) }}>
    <div class="flex-1">{{ $slot }}</div>
    <button type="button" @click="show = false" class="text-current opacity-50 hover:opacity-100">&times;</button>
</div>
