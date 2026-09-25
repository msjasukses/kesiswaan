@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'required' => false, 'help' => null])

<div>
    @if ($label)
        <label for="{{ $name }}" class="block mb-1 text-sm font-medium text-slate-700">
            {{ $label }}@if ($required)<span class="text-red-500"> *</span>@endif
        </label>
    @endif

    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" value="{{ $value }}"
        {{ $attributes->merge(['class' => 'w-full text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500']) }}
        @if ($required) required @endif>

    @if ($help)
        <p class="mt-1 text-xs text-slate-400">{{ $help }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
