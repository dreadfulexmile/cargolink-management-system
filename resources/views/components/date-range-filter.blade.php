@props(['dateFrom', 'error' => null])
<div>
    <div class="flex flex-wrap items-center gap-2">
        <input type="date" wire:model.live="dateFrom"
            class="rounded-md text-sm border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
        <span class="text-sm text-gray-400 dark:text-slate-500">to</span>
        <input type="date" wire:model.live="dateTo"
            @if ($dateFrom)
                min="{{ $dateFrom }}"
                max="{{ \Carbon\Carbon::parse($dateFrom)->addDays(365)->format('Y-m-d') }}"
            @endif
            class="rounded-md text-sm border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
    </div>
    @if ($error)
        <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">{{ $error }}</p>
    @endif
</div>
