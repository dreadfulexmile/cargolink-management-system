@props(['label', 'icon' => null, 'color' => 'indigo', 'highlight' => false])

@php
$colors = [
    'indigo' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400',
    'amber' => 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400',
    'emerald' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400',
    'rose' => 'bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400',
    'sky' => 'bg-sky-100 text-sky-600 dark:bg-sky-500/20 dark:text-sky-400',
    'violet' => 'bg-violet-100 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400',
    'teal' => 'bg-teal-100 text-teal-600 dark:bg-teal-500/20 dark:text-teal-400',
    'fuchsia' => 'bg-fuchsia-100 text-fuchsia-600 dark:bg-fuchsia-500/20 dark:text-fuchsia-400',
][$color] ?? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400';
@endphp

<div {{ $attributes->merge(['class' => ($highlight ? 'bg-amber-50 dark:bg-amber-900/20 ring-1 ring-amber-300 dark:ring-amber-700' : 'bg-white dark:bg-slate-800') . ' rounded-2xl shadow-sm p-5 flex flex-col min-h-[136px]']) }}>
    <div class="flex items-center gap-3 mb-3">
        @if ($icon)
            <div class="flex items-center justify-center w-10 h-10 rounded-xl {{ $colors }} shrink-0">
                <x-icon :name="$icon" class="w-5 h-5" />
            </div>
        @endif
        <div class="text-sm text-gray-500 dark:text-slate-400">{{ $label }}</div>
    </div>

    <div class="text-2xl font-semibold text-gray-900 dark:text-slate-100 truncate">
        {{ $slot }}
    </div>

    <div class="mt-auto">
        @isset($footer)
            <div class="mt-1 text-xs text-gray-400 dark:text-slate-500 truncate">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
