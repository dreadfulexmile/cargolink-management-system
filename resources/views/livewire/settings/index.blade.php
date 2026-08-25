<div>
    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">{{ __('Settings') }}</h2>

        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-3xl p-6 space-y-5">
            <div class="flex gap-1 border-b border-gray-100 dark:border-slate-700 -mt-1">
                <button type="button" wire:click="selectTab('users')"
                    class="px-3 py-2 text-sm font-medium border-b-2 -mb-px {{ $tab === 'users' ? 'border-brand-600 dark:border-brand-400 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-200' }}">
                    Users
                </button>
                <button type="button" wire:click="selectTab('numbering')"
                    class="px-3 py-2 text-sm font-medium border-b-2 -mb-px {{ $tab === 'numbering' ? 'border-brand-600 dark:border-brand-400 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-200' }}">
                    Numbering
                </button>
            </div>

            @if ($tab === 'users')
                <livewire:users.index />
            @elseif ($tab === 'numbering')
                <livewire:settings.numbering />
            @endif
        </div>
    </div>
</div>
