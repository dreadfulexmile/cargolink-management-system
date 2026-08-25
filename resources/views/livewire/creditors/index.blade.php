<div>
    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">{{ __('Creditors / Debt') }}</h2>

        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-4 max-w-xs">
            <div class="text-xs text-gray-500 dark:text-slate-400">Total Debt</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-slate-100"><x-money :amount="$totalDebt" /></div>
        </div>

        <div class="flex justify-end">
            <button wire:click="create" class="inline-flex items-center px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">
                + New Creditor
            </button>
        </div>

        @if ($showForm)
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Name" />
                        <x-text-input type="text" class="mt-1 block w-full" wire:model="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Type" />
                        <select wire:model="type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                            <option value="individual">Individual</option>
                            <option value="bank_facility">Bank Facility</option>
                            <option value="gold_loan">Gold Loan</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Outstanding" />
                        <x-text-input type="number" step="0.01" class="mt-1 block w-full" wire:model="outstanding" />
                    </div>
                    <div>
                        <x-input-label value="Note" />
                        <x-text-input type="text" class="mt-1 block w-full" wire:model="note" />
                    </div>
                    <div>
                        <x-input-label value="Monthly Repayment (leave blank if none)" />
                        <x-text-input type="number" step="0.01" class="mt-1 block w-full" wire:model="monthly_repayment" />
                        <x-input-error :messages="$errors->get('monthly_repayment')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Repayment Due Day (of month)" />
                        <x-text-input type="number" min="1" max="28" class="mt-1 block w-full" wire:model="repayment_due_day" />
                        <x-input-error :messages="$errors->get('repayment_due_day')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Repayment Term (number of months)" />
                        <x-text-input type="number" min="1" max="600" class="mt-1 block w-full" wire:model="repayment_term_months" />
                        <x-input-error :messages="$errors->get('repayment_term_months')" class="mt-1" />
                    </div>
                </div>
                <div class="flex gap-2">
                    <button wire:click="save" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">Save</button>
                    <button wire:click="$set('showForm', false)" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                </div>
            </div>
        @endif

        @foreach ($creditors as $creditor)
            @php($isExpanded = $expandedCreditorId === $creditor->id)
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg overflow-hidden">
                <button type="button" wire:click="toggleCreditorDetails({{ $creditor->id }})" class="w-full flex items-center justify-between gap-4 p-6 text-left">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-gray-900 dark:text-slate-100">{{ $creditor->name }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-300">
                                {{ str_replace('_', ' ', ucfirst($creditor->type)) }}
                            </span>
                            @if ($creditor->monthly_repayment)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-400">Scheduled Payments</span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                            Outstanding: <x-money :amount="$creditor->outstanding" />
                            @if ($creditor->monthly_repayment)
                                &middot; Monthly: <x-money :amount="$creditor->monthly_repayment" />
                                @if ($creditor->repayment_due_day)
                                    &middot; Due on day {{ $creditor->repayment_due_day }} of each month
                                @endif
                                @if ($creditor->repayment_term_months)
                                    &middot; {{ $creditor->paid_payments_count }} / {{ $creditor->repayment_term_months }} months paid
                                @endif
                            @endif
                        </div>
                    </div>
                    <svg class="w-5 h-5 flex-shrink-0 text-gray-400 transition-transform {{ $isExpanded ? 'rotate-90' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                @if ($isExpanded)
                    <div class="px-6 pb-6 space-y-3 border-t border-gray-100 dark:border-slate-700 pt-4">
                        <div class="text-sm text-gray-500 dark:text-slate-400">{{ $creditor->note ?: 'No note.' }}</div>

                        <div class="flex gap-2">
                            <button wire:click="edit({{ $creditor->id }})" class="px-3 py-1.5 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Edit</button>
                            @if ($creditor->monthly_repayment)
                                <button wire:click="payRepayment({{ $creditor->id }})" class="px-3 py-1.5 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Add Repayment</button>
                            @endif
                            <button type="button" x-on:click="$dispatch('confirm-open', @js([
                                    'title' => 'Delete Creditor',
                                    'message' => "Delete {$creditor->name}?",
                                    'method' => 'delete',
                                    'params' => [$creditor->id],
                                    'confirmLabel' => 'Delete',
                                ]))" class="px-3 py-1.5 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-md text-sm font-medium">Delete</button>
                        </div>

                        @if ($payingCreditorId === $creditor->id)
                            <div class="border-t border-gray-100 dark:border-slate-700 pt-3 space-y-3">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-slate-300">{{ $editingPaymentId ? 'Edit Repayment' : 'New Repayment' }}</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
                                    <div>
                                        <x-input-label value="Period" />
                                        <input type="month" wire:model="payment_period" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm">
                                    </div>
                                    <div>
                                        <x-input-label value="Due Date" />
                                        <x-text-input type="date" class="mt-1 block w-full text-sm" wire:model="payment_due_date" />
                                    </div>
                                    <div>
                                        <x-input-label value="Amount" />
                                        <x-text-input type="number" step="0.01" class="mt-1 block w-full text-sm" wire:model="payment_amount" />
                                    </div>
                                    <div>
                                        <x-input-label value="Paid On (leave blank if unpaid)" />
                                        <x-text-input type="date" class="mt-1 block w-full text-sm" wire:model="payment_paid_on" />
                                    </div>
                                    <div class="flex gap-2">
                                        <button wire:click="saveRepaymentPayment" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">Save</button>
                                        <button wire:click="cancelRepaymentPayment" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($creditor->payments->isNotEmpty())
                            <div class="overflow-x-auto">
                            <table class="w-full text-sm border-t border-gray-100 dark:border-slate-700 pt-2">
                                <thead class="text-left text-gray-500 dark:text-slate-400">
                                    <tr><th class="py-1">Period</th><th class="py-1">Due Date</th><th class="py-1">Paid On</th><th class="py-1">Amount</th><th class="py-1"></th></tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                                    @foreach ($creditor->payments as $payment)
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
                                                <button wire:click="editRepaymentPayment({{ $payment->id }})" title="Edit" class="p-1.5 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md"><x-icon name="edit" class="w-3.5 h-3.5" /></button>
                                                @if (! $payment->paid_on)
                                                    <button wire:click="markRepaymentPaid({{ $payment->id }})" class="text-xs px-2 py-1 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md">Mark Paid</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach

        {{ $creditors->links() }}
    </div>

    <x-confirm-modal />
</div>
