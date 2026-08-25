<!-- Mobile overlay -->
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 bg-black/30 z-30 sm:hidden"></div>

<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full sm:translate-x-0'"
    class="fixed sm:static inset-y-0 left-0 z-40 w-64 bg-white dark:bg-slate-800 flex flex-col shrink-0 transition-transform duration-200 ease-in-out"
>
    <div class="h-16 flex items-center justify-between px-5 shrink-0">
        <a href="{{ route('dashboard') }}" wire:navigate>
            <x-application-logo class="h-9 w-auto" />
        </a>
        <button @click="sidebarOpen = false" class="sm:hidden text-gray-400 dark:text-slate-500">
            <x-icon name="close" class="w-5 h-5" />
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
        <div>
            <div class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Menu</div>
            <div class="space-y-1">
                <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="dashboard" wire:navigate>
                    {{ __('Dashboard') }}
                </x-sidebar-link>
                <x-sidebar-link :href="route('customers.index')" :active="request()->routeIs('customers.*')" icon="customers" wire:navigate>
                    {{ __('Customers') }}
                </x-sidebar-link>
                <x-sidebar-link :href="route('jobs.index')" :active="request()->routeIs('jobs.*')" icon="jobs" wire:navigate>
                    {{ __('Jobs') }}
                </x-sidebar-link>
                <x-sidebar-link :href="route('invoices.index')" :active="request()->routeIs('invoices.*')" icon="invoices" wire:navigate>
                    {{ __('Invoices') }}
                </x-sidebar-link>
                <x-sidebar-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')" icon="expenses" wire:navigate>
                    {{ __('Expenses') }}
                </x-sidebar-link>
                <x-sidebar-link :href="route('vehicles.index')" :active="request()->routeIs('vehicles.*')" icon="vehicles" wire:navigate>
                    {{ __('Vehicles') }}
                </x-sidebar-link>
                <x-sidebar-link :href="route('reports.index')" :active="request()->routeIs('reports.*')" icon="reports" wire:navigate>
                    {{ __('Reports') }}
                </x-sidebar-link>
            </div>
        </div>

        <div>
            <div class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Financial</div>
            <div class="space-y-1">
                <x-sidebar-link :href="route('director-account.index')" :active="request()->routeIs('director-account.*')" icon="director" wire:navigate>
                    {{ __('Director A/C') }}
                </x-sidebar-link>
                <x-sidebar-link :href="route('creditors.index')" :active="request()->routeIs('creditors.*')" icon="creditors" wire:navigate>
                    {{ __('Creditors') }}
                </x-sidebar-link>
            </div>
        </div>

        @role('gm|co-gm')
            <div>
                <div class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Admin</div>
                <div class="space-y-1">
                    <x-sidebar-link :href="route('settings.index')" :active="request()->routeIs('settings.*')" icon="settings" wire:navigate>
                        {{ __('Settings') }}
                    </x-sidebar-link>
                </div>
            </div>
        @endrole
    </nav>

    <div class="px-5 py-4 text-xs text-gray-400 dark:text-slate-500 shrink-0">
        Cargo Link Customs Brokers (Pvt) Ltd
    </div>
</aside>
