<div>
    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">
                {{ $invoice->invoice_no }} &mdash; {{ $invoice->customer->name }}
            </h2>
            <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">
                Download PDF
            </a>
        </div>

        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div><span class="text-gray-500 dark:text-slate-400">Job No</span><div class="font-medium text-gray-900 dark:text-slate-100">{{ $invoice->job->job_no }}</div></div>
            <div>
                <span class="text-gray-500 dark:text-slate-400">Invoice Date</span>
                @if ($editingDate)
                    <div class="flex items-center gap-2 mt-1">
                        <x-text-input type="date" class="block w-full text-sm" wire:model="invoice_date" />
                        <button wire:click="saveDate" class="text-brand-600 dark:text-brand-400 hover:underline text-xs">Save</button>
                        <button wire:click="cancelDate" class="text-gray-500 dark:text-slate-400 hover:underline text-xs">Cancel</button>
                    </div>
                @else
                    <div class="flex items-center gap-2">
                        <div class="font-medium text-gray-900 dark:text-slate-100">{{ $invoice->invoice_date->format('Y-m-d') }}</div>
                        <button wire:click="editDate" class="text-xs text-gray-500 dark:text-slate-400 hover:underline">Edit</button>
                    </div>
                @endif
            </div>
            <div><span class="text-gray-500 dark:text-slate-400">Subtotal</span><div class="font-medium text-gray-900 dark:text-slate-100"><x-money :amount="$invoice->subtotal" /></div></div>
            <div><span class="text-gray-500 dark:text-slate-400">Advance Applied</span><div class="font-medium text-gray-900 dark:text-slate-100"><x-money :amount="$invoice->advance_total" /></div></div>
            <div><span class="text-gray-500 dark:text-slate-400">Balance Due</span><div class="font-medium text-gray-900 dark:text-slate-100"><x-money :amount="$invoice->balance_due" /></div></div>
            <div><span class="text-gray-500 dark:text-slate-400">Status</span><div class="font-medium capitalize text-gray-900 dark:text-slate-100">{{ str_replace('_', ' ', $invoice->status) }}</div></div>
        </div>

        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 space-y-4">
            <h3 class="font-semibold text-gray-800 dark:text-slate-200">Invoice Lines</h3>

            <form wire:submit="saveLine" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                <div>
                    <x-input-label value="Description" />
                    <x-text-input type="text" class="mt-1 block w-full text-sm" wire:model="line_description" />
                    <x-input-error :messages="$errors->get('line_description')" class="mt-1" />
                </div>
                <div>
                    <x-input-label value="Kind" />
                    <select wire:model="line_kind" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm">
                        <option value="disbursement">Disbursement</option>
                        <option value="service">Service</option>
                    </select>
                </div>
                <div>
                    <x-input-label value="Amount" />
                    <x-text-input type="number" step="0.01" class="mt-1 block w-full text-sm" wire:model="line_amount" />
                    <x-input-error :messages="$errors->get('line_amount')" class="mt-1" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">{{ $editingLineId ? 'Save' : 'Add Line' }}</button>
                    @if ($editingLineId)
                        <button type="button" wire:click="cancelLine" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                    @endif
                </div>
            </form>

            <table class="w-full text-sm">
                <thead class="text-left text-gray-500 dark:text-slate-400">
                    <tr><th class="py-2">Description</th><th class="py-2">Kind</th><th class="py-2">Amount</th><th class="py-2"></th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @foreach ($lines as $line)
                        <tr class="text-gray-700 dark:text-slate-300">
                            <td class="py-2">{{ $line->description }}</td>
                            <td class="py-2">
                                <span class="px-2 py-0.5 rounded text-xs {{ $line->kind === 'service' ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300' : 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-300' }}">
                                    {{ $line->kind }}
                                </span>
                            </td>
                            <td class="py-2"><x-money :amount="$line->amount" /></td>
                            <td class="py-2 text-right space-x-3">
                                <button wire:click="editLine({{ $line->id }})" title="Edit" class="text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-slate-100"><x-icon name="edit" class="w-4 h-4" /></button>
                                <button type="button" title="Remove" x-on:click="$dispatch('confirm-open', @js([
                                        'title' => 'Remove Line',
                                        'message' => 'Remove this line?',
                                        'method' => 'removeLine',
                                        'params' => [$line->id],
                                        'confirmLabel' => 'Remove',
                                    ]))" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"><x-icon name="delete" class="w-4 h-4" /></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 space-y-4">
            <h3 class="font-semibold text-gray-800 dark:text-slate-200">Payments</h3>

            <form wire:submit="savePayment" class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
                <div>
                    <x-input-label value="Amount" />
                    <x-text-input type="number" step="0.01" class="mt-1 block w-full text-sm" wire:model="payment_amount" />
                    <x-input-error :messages="$errors->get('payment_amount')" class="mt-1" />
                </div>
                <div>
                    <x-input-label value="Method" />
                    <select wire:model="payment_method" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm">
                        <option value="cheque">Cheque</option>
                        <option value="bank">Bank</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
                <div>
                    <x-input-label value="Reference" />
                    <x-text-input type="text" class="mt-1 block w-full text-sm" wire:model="payment_reference" />
                </div>
                <div>
                    <x-input-label value="Paid On" />
                    <x-text-input type="date" class="mt-1 block w-full text-sm" wire:model="payment_paid_on" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">{{ $editingPaymentId ? 'Save' : 'Record Payment' }}</button>
                    @if ($editingPaymentId)
                        <button type="button" wire:click="cancelPayment" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                    @endif
                </div>
            </form>

            <table class="w-full text-sm">
                <thead class="text-left text-gray-500 dark:text-slate-400">
                    <tr><th class="py-2">Date</th><th class="py-2">Method</th><th class="py-2">Reference</th><th class="py-2">Amount</th><th class="py-2"></th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @foreach ($payments as $payment)
                        <tr wire:key="payment-{{ $payment->id }}" class="text-gray-700 dark:text-slate-300">
                            <td class="py-2">{{ $payment->paid_on->format('Y-m-d') }}</td>
                            <td class="py-2 capitalize">{{ $payment->method }}</td>
                            <td class="py-2">{{ $payment->reference ?: '—' }}</td>
                            <td class="py-2"><x-money :amount="$payment->amount" /></td>
                            <td class="py-2 text-right space-x-3">
                                <button wire:click="editPayment({{ $payment->id }})" title="Edit" class="text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-slate-100"><x-icon name="edit" class="w-4 h-4" /></button>
                                <button type="button" title="Remove" x-on:click="$dispatch('confirm-open', @js([
                                        'title' => 'Remove Payment',
                                        'message' => 'Remove this payment?',
                                        'method' => 'removePayment',
                                        'params' => [$payment->id],
                                        'confirmLabel' => 'Remove',
                                    ]))" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"><x-icon name="delete" class="w-4 h-4" /></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <x-confirm-modal />
</div>
