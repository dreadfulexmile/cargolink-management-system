@props(['active' => false, 'icon'])

@php
$classes = ($active ?? false)
    ? 'flex items-center gap-3 px-3 py-2 rounded-full text-sm font-medium bg-brand-50 dark:bg-brand-500/10 text-brand-700 dark:text-brand-300 ring-1 ring-brand-200 dark:ring-brand-400/30'
    : 'flex items-center gap-3 px-3 py-2 rounded-full text-sm font-medium text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700/60 hover:text-gray-900 dark:hover:text-slate-100';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    <x-icon :name="$icon" class="w-5 h-5 shrink-0" />
    <span>{{ $slot }}</span>
</a>
