@props(['excelUrl', 'wordUrl', 'pdfUrl' => null])

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" type="button"
        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Export
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>
    <div x-show="open" x-cloak @click.outside="open = false" x-transition
        class="absolute right-0 z-20 mt-2 bg-white border rounded-lg shadow-lg w-48 border-slate-200 py-1">
        <a href="{{ $excelUrl }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm2.5 4l1.5 2.5L9.5 7H11l-2.25 3.5L11 14H9.5L8 11.5 6.5 14H5l2.25-3.5L5 7h1.5z"/></svg>
            Export Excel (.xlsx)
        </a>
        <a href="{{ $wordUrl }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
            <svg class="w-4 h-4 text-sky-600" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm1.5 4h1.3l.9 4 .95-4h1.2l.95 4 .9-4h1.3l-1.5 7h-1.3l-.95-3.8L8.3 14H7L5.5 7z"/></svg>
            Export Word (.docx)
        </a>
        @if ($pdfUrl)
            <a href="{{ $pdfUrl }}" target="_blank" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
                <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm2 4h2.2c1.1 0 1.8.7 1.8 1.7S9.3 10.4 8.2 10.4H7V13H6V7zm5 0h1.8c1.4 0 2.2 1.1 2.2 3s-.8 3-2.2 3H11V7zm-4 1v1.5h1.1c.5 0 .8-.3.8-.75S8.6 8 8.1 8H7zm5 0v4h.7c.8 0 1.2-.7 1.2-2s-.4-2-1.2-2H12z"/></svg>
                Export PDF (.pdf)
            </a>
        @endif
    </div>
</div>
