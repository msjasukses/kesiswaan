@props(['color' => 'slate'])

@php
    $map = [
        'slate' => 'bg-slate-100 text-slate-700',
        'green' => 'bg-emerald-100 text-emerald-700',
        'red' => 'bg-red-100 text-red-700',
        'yellow' => 'bg-amber-100 text-amber-700',
        'blue' => 'bg-sky-100 text-sky-700',
        'indigo' => 'bg-indigo-100 text-indigo-700',
        'purple' => 'bg-purple-100 text-purple-700',
    ];
    $cls = $map[$color] ?? $map['slate'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full $cls"]) }}>{{ $slot }}</span>
