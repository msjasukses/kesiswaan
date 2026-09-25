@props(['title' => null, 'flush' => false])

<div {{ $attributes->merge(['class' => 'overflow-hidden bg-white border shadow-sm rounded-xl border-slate-200']) }}>
    @if ($title || isset($actions))
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800">{{ $title }}</h3>
            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif
    <div class="{{ $flush ? '' : 'p-5' }}">{{ $slot }}</div>
</div>
