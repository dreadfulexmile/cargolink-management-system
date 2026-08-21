<div>
    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">{{ __('Vehicles & Leases') }}</h2>
            <button wire:click="createVehicle" class="inline-flex items-center px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">
                + New Vehicle
            </button>
        </div>

        @if ($showVehicleForm)
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 space-y-4">
                <h3 class="font-semibold text-gray-800 dark:text-slate-200">{{ $editingVehicleId ? 'Edit Vehicle' : 'New Vehicle' }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Registration No" />
                        <x-text-input type="text" class="mt-1 block w-full" wire:model="reg_no" />
                        <x-input-error :messages="$errors->get('reg_no')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Monthly Leasing Amount (leave blank if none)" />
                        <x-text-input type="number" step="0.01" class="mt-1 block w-full" wire:model="monthly_rental" />
                        <x-input-error :messages="$errors->get('monthly_rental')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Lease Due Day (of month)" />
                        <x-text-input type="number" min="1" max="28" class="mt-1 block w-full" wire:model="lease_due_day" />
                        <x-input-error :messages="$errors->get('lease_due_day')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Lease Term (number of months)" />
                        <x-text-input type="number" min="1" max="120" class="mt-1 block w-full" wire:model="lease_term_months" />
                        <x-input-error :messages="$errors->get('lease_term_months')" class="mt-1" />
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-slate-400">
                    Saving schedules a monthly payment for every month of the term. Raise the term later and
                    save again to extend the schedule — existing months are never touched. Individual months
                    can still be edited or marked paid at any time below.
                </p>
                <div class="flex gap-2">
                    <button wire:click="saveVehicle" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">Save</button>
                    <button wire:click="$set('showVehicleForm', false)" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                </div>
            </div>
        @endif

        @foreach ($vehicles as $vehicle)
            @php($isExpanded = $expandedVehicleId === $vehicle->id)
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg overflow-hidden {{ $vehicle->is_active ? '' : 'opacity-60' }}">
                <button type="button" wire:click="toggleVehicleDetails({{ $vehicle->id }})" class="w-full flex items-center justify-between gap-4 p-6 text-left">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold {{ $vehicle->is_active ? 'text-gray-900 dark:text-slate-100' : 'text-gray-400 dark:text-slate-500' }}">{{ $vehicle->reg_no }}</span>
                            @if ($vehicle->has_lease)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-400">Leased</span>
                            @endif
                            @unless ($vehicle->is_active)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                                    Deactivated{{ $vehicle->lease_term_months && $vehicle->paid_lease_payments_count >= $vehicle->lease_term_months ? ' — Lease Fully Paid' : '' }}
                                </span>
                            @endunless
                        </div>
                        @if ($vehicle->has_lease)
                            <div class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                                Monthly lease: <x-money :amount="$vehicle->monthly_rental" />
                                @if ($vehicle->lease_due_day)
                                    &middot; Due on day {{ $vehicle->lease_due_day }} of each month
                                @endif
                                @if ($vehicle->lease_term_months)
                                    &middot; {{ $vehicle->paid_lease_payments_count }} / {{ $vehicle->lease_term_months }} months paid
                                @endif
                            </div>
                        @endif
                    </div>
                    <svg class="w-5 h-5 flex-shrink-0 text-gray-400 transition-transform {{ $isExpanded ? 'rotate-90' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                @if ($isExpanded)
                    <div class="px-6 pb-6 space-y-3 border-t border-gray-100 dark:border-slate-700 pt-4">
                        <div class="flex items-center gap-2">
                            <button wire:click="editVehicle({{ $vehicle->id }})" class="px-3 py-1.5 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Edit</button>
                            @if ($vehicle->has_lease)
                                <button wire:click="payLease({{ $vehicle->id }})" class="px-3 py-1.5 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Add Lease Payment</button>
                            @endif
                            <span class="text-sm text-gray-500 dark:text-slate-400 ml-1">Active</span>
                            <x-active-toggle :active="$vehicle->is_active" :params="[$vehicle->id]" :label="$vehicle->reg_no" entity="Vehicle" />
                        </div>

                        @if ($payingVehicleId === $vehicle->id)
                            <div class="border-t border-gray-100 dark:border-slate-700 pt-3 space-y-3">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-slate-300">{{ $editingLeasePaymentId ? 'Edit Lease Payment' : 'New Lease Payment' }}</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
                                <div>
                                    <x-input-label value="Period" />
                                    <input type="month" wire:model="lease_period" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm">
                                </div>
                                <div>
                                    <x-input-label value="Due Date" />
                                    <x-text-input type="date" class="mt-1 block w-full text-sm" wire:model="lease_due_date" />
                                </div>
                                <div>
                                    <x-input-label value="Amount" />
                                    <x-text-input type="number" step="0.01" class="mt-1 block w-full text-sm" wire:model="lease_amount" />
                                </div>
                                <div>
                                    <x-input-label value="Paid On (leave blank if unpaid)" />
                                    <x-text-input type="date" class="mt-1 block w-full text-sm" wire:model="lease_paid_on" />
                                </div>
                                <div class="flex gap-2">
                                    <button wire:click="saveLeasePayment" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">Save</button>
                                    <button wire:click="cancelLeasePayment" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                                </div>
                            </div>
                            </div>
                        @endif

                        @if ($vehicle->leasePayments->isNotEmpty())
                            <table class="w-full text-sm border-t border-gray-100 dark:border-slate-700 pt-2">
                                <thead class="text-left text-gray-500 dark:text-slate-400">
                                    <tr><th class="py-1">Period</th><th class="py-1">Due Date</th><th class="py-1">Paid On</th><th class="py-1">Amount</th><th class="py-1"></th></tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                                    @foreach ($vehicle->leasePayments as $payment)
                                        <tr class="{{ $payment->isOverdue() ? 'text-red-600 dark:text-red-400' : 'text-gray-700 dark:text-slate-300' }}">
                                            <td class="py-1">{{ $payment->period->format('Y-m') }}</td>
                                            <td class="py-1">{{ $payment->due_date?->format('Y-m-d') ?? '—' }}</td>
                                            <td class="py-1">
                                                @if ($payment->paid_on)
                                                    {{ $payment->paid_on->format('Y-m-d') }}
                                                @elseif ($payment->isOverdue())
                                                    Overdue
                                                @else
                                                    Unpaid
                                                @endif
                                            </td>
                                            <td class="py-1"><x-money :amount="$payment->amount" /></td>
                                            <td class="py-1 space-x-2 whitespace-nowrap">
                                                <button wire:click="editLeasePayment({{ $payment->id }})" title="Edit" class="p-1.5 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md"><x-icon name="edit" class="w-3.5 h-3.5" /></button>
                                                @if (! $payment->paid_on)
                                                    <button wire:click="markLeasePaymentPaid({{ $payment->id }})" class="text-xs px-2 py-1 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md">Mark Paid</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach

        {{ $vehicles->links() }}
    </div>

    <x-confirm-modal />
</div>
