<div>
    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">{{ __('Customers') }}</h2>

        <div class="flex items-center justify-between gap-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search customers..."
                class="w-full max-w-sm rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 shadow-sm">
            <button type="button" wire:click="create" x-data x-on:click="$dispatch('open-modal', 'customer-details')"
                class="shrink-0 inline-flex items-center px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium hover:bg-brand-700 dark:hover:bg-brand-400">
                + New Customer
            </button>
        </div>

        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-900/50 text-left text-gray-500 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Jobs</th>
                        <th class="px-4 py-3">Outstanding</th>
                        <th class="px-4 py-3">Credit Days</th>
                        <th class="px-4 py-3">Active</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @foreach ($customers as $customer)
                        <tr class="{{ $customer->is_active ? 'text-gray-700 dark:text-slate-300' : 'text-gray-400 dark:text-slate-500 bg-gray-50/60 dark:bg-slate-900/30' }}">
                            <td class="px-4 py-3 font-medium max-w-[220px] {{ $customer->is_active ? 'text-gray-900 dark:text-slate-100' : 'text-gray-400 dark:text-slate-500' }}">
                                <button type="button" wire:click="edit({{ $customer->id }})" x-data x-on:click="$dispatch('open-modal', 'customer-details')"
                                    class="hover:text-brand-600 dark:hover:text-brand-400 hover:underline text-left inline-flex items-center gap-2 min-w-0 max-w-full">
                                    <span class="truncate" title="{{ $customer->name }}">{{ $customer->name }}</span>
                                    @unless ($customer->is_active)
                                        <span class="shrink-0 text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">Inactive</span>
                                    @endunless
                                </button>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('jobs.index', ['filterCustomer' => $customer->id]) }}" wire:navigate class="text-brand-600 dark:text-brand-400 underline underline-offset-2 hover:text-brand-700 dark:hover:text-brand-300">
                                    {{ $customer->jobs_count }}
                                </a>
                            </td>
                            <td class="px-4 py-3"><x-money :amount="$customer->outstanding_balance" /></td>
                            <td class="px-4 py-3">{{ $customer->credit_days }}</td>
                            <td class="px-4 py-3">
                                <x-active-toggle :active="$customer->is_active" :params="[$customer->id]" :label="$customer->name" entity="Customer" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $customers->links() }}
    </div>

    <x-modal name="customer-details" maxWidth="lg">
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-lg text-gray-800 dark:text-slate-200">
                    {{ $editingId ? 'Customer Details' : 'New Customer' }}
                </h3>
                <button type="button" x-on:click="$dispatch('close')" class="text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300">
                    <x-icon name="close" class="w-5 h-5" />
                </button>
            </div>

            @php($viewingCustomer = $editingId ? $customers->firstWhere('id', $editingId) : null)

            @if ($viewingCustomer)
                <div class="grid grid-cols-3 gap-3 text-sm bg-gray-50 dark:bg-slate-900/50 rounded-xl p-3">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-slate-400">Jobs</div>
                        <a href="{{ route('jobs.index', ['filterCustomer' => $viewingCustomer->id]) }}" wire:navigate class="font-medium text-brand-600 dark:text-brand-400 underline underline-offset-2">
                            {{ $viewingCustomer->jobs_count }}
                        </a>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-slate-400">Outstanding</div>
                        <div class="font-medium text-gray-900 dark:text-slate-100"><x-money :amount="$viewingCustomer->outstanding_balance ?? 0" /></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-slate-400 mb-0.5">Active</div>
                        <x-active-toggle :active="$viewingCustomer->is_active" :params="[$viewingCustomer->id]" :label="$viewingCustomer->name" entity="Customer" />
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="modal_name" value="Name" />
                    <x-text-input id="modal_name" type="text" class="mt-1 block w-full" wire:model="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="modal_contact_person" value="Contact Person" />
                    <x-text-input id="modal_contact_person" type="text" class="mt-1 block w-full" wire:model="contact_person" />
                </div>
                <div>
                    <x-input-label for="modal_phone" value="Phone" />
                    <x-text-input id="modal_phone" type="text" class="mt-1 block w-full" wire:model="phone" />
                </div>
                <div>
                    <x-input-label for="modal_email" value="Email" />
                    <x-text-input id="modal_email" type="email" class="mt-1 block w-full" wire:model="email" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="modal_address" value="Address" />
                    <x-text-input id="modal_address" type="text" class="mt-1 block w-full" wire:model="address" />
                </div>
                <div>
                    <x-input-label for="modal_credit_days" value="Credit Days" />
                    <x-text-input id="modal_credit_days" type="number" class="mt-1 block w-full" wire:model="credit_days" />
                    <x-input-error :messages="$errors->get('credit_days')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="modal_credit_limit" value="Credit Limit (LKR, optional)" />
                    <x-text-input id="modal_credit_limit" type="number" step="0.01" class="mt-1 block w-full" wire:model="credit_limit" />
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <div class="flex gap-2">
                    <button wire:click="save" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">Save</button>
                    <button type="button" wire:click="cancel" x-on:click="$dispatch('close')" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                </div>

                @if ($viewingCustomer)
                    @if ($viewingCustomer->jobs_count > 0)
                        <span class="text-xs text-gray-400 dark:text-slate-500">Has {{ $viewingCustomer->jobs_count }} job(s) — deactivate instead of deleting.</span>
                    @else
                        <button type="button" x-on:click="$dispatch('confirm-open', @js([
                                'title' => 'Delete Customer',
                                'message' => "Delete {$viewingCustomer->name}? This cannot be undone.",
                                'method' => 'deleteCustomer',
                                'params' => [$viewingCustomer->id],
                                'confirmLabel' => 'Delete',
                            ])); $dispatch('close')" class="text-sm text-red-600 dark:text-red-400 hover:underline">
                            Delete Customer
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </x-modal>

    <x-confirm-modal />
</div>
