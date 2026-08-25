<div>
    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex items-center justify-between w-full">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">
                    Receive Payment &mdash; {{ $customer->name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5">
                    Record one amount the customer paid and split it across whichever invoices it covers.
                </p>
            </div>
            <a href="{{ route('customers.index') }}" wire:navigate class="text-sm text-gray-500 dark:text-slate-400 hover:underline">
                &larr; Back to Customers
            </a>
        </div>

        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-gray-500 dark:text-slate-400">Outstanding Balance</span>
                <div class="font-medium text-gray-900 dark:text-slate-100 text-lg"><x-money :amount="$customer->outstandingBalance()" /></div>
            </div>
            <div>
                <span class="text-gray-500 dark:text-slate-400">Open Invoices</span>
                <div class="font-medium text-gray-900 dark:text-slate-100 text-lg">{{ $openInvoices->count() }}</div>
            </div>
        </div>

        @if ($openInvoices->isEmpty())
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 text-sm text-gray-500 dark:text-slate-400">
                This customer has no outstanding invoices right now — nothing to receive a payment against.
            </div>
        @else
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 space-y-4">
                <h3 class="font-semibold text-gray-800 dark:text-slate-200">Record a Receipt</h3>

                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                        <div>
                            <x-input-label value="Amount Received" />
                            <x-text-input type="number" step="0.01" class="mt-1 block w-full text-sm" wire:model="amount" />
                            <x-input-error :messages="$errors->get('amount')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label value="Method" />
                            <select wire:model="method" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm">
                                <option value="cheque">Cheque</option>
                                <option value="bank">Bank</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label value="Reference" />
                            <x-text-input type="text" class="mt-1 block w-full text-sm" wire:model="reference" />
                        </div>
                        <div>
                            <x-input-label value="Received On" />
                            <x-text-input type="date" class="mt-1 block w-full text-sm" wire:model="received_on" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-b border-gray-100 dark:border-slate-700 py-3">
                        <button type="button" wire:click="autoAllocate" class="px-3 py-1.5 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-xs font-medium hover:bg-gray-200 dark:hover:bg-slate-600">
                            Auto-allocate (oldest invoice first)
                        </button>
                        <div class="text-xs text-gray-500 dark:text-slate-400">
                            Remaining to allocate:
                            <span @class([
                                'font-semibold',
                                'text-green-600 dark:text-green-400' => bccomp($this->remainingToAllocate(), '0', 2) === 0,
                                'text-amber-600 dark:text-amber-400' => bccomp($this->remainingToAllocate(), '0', 2) !== 0,
                            ])>
                                <x-money :amount="$this->remainingToAllocate()" />
                            </span>
                        </div>
                    </div>

                    <x-input-error :messages="$errors->get('allocations')" class="mt-1" />

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-left text-gray-500 dark:text-slate-400">
                                <tr>
                                    <th class="py-2">Invoice</th>
                                    <th class="py-2">Job No</th>
                                    <th class="py-2">Balance Due</th>
                                    <th class="py-2">Allocate</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                                @foreach ($openInvoices as $invoice)
                                    <tr wire:key="open-invoice-{{ $invoice->id }}" class="text-gray-700 dark:text-slate-300">
                                        <td class="py-2">
                                            <a href="{{ route('invoices.show', $invoice) }}" wire:navigate class="text-brand-600 dark:text-brand-400 hover:underline">
                                                {{ $invoice->invoice_no }}
                                            </a>
                                        </td>
                                        <td class="py-2">{{ $invoice->job->job_no }}</td>
                                        <td class="py-2"><x-money :amount="$invoice->balance_due" /></td>
                                        <td class="py-2">
                                            <x-text-input type="number" step="0.01" class="block w-32 text-sm" wire:model="allocations.{{ $invoice->id }}" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <button type="submit" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">
                            Record Receipt
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 space-y-4">
            <h3 class="font-semibold text-gray-800 dark:text-slate-200">Receipt History</h3>

            @if ($receipts->isEmpty())
                <p class="text-sm text-gray-500 dark:text-slate-400">No receipts recorded for this customer yet.</p>
            @else
                <div class="space-y-3">
                    @foreach ($receipts as $receipt)
                        <div wire:key="receipt-{{ $receipt->id }}" class="border border-gray-100 dark:border-slate-700 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="text-sm">
                                    <span class="font-medium text-gray-900 dark:text-slate-100"><x-money :amount="$receipt->amount" /></span>
                                    <span class="text-gray-500 dark:text-slate-400">
                                        &middot; {{ $receipt->received_on->format('Y-m-d') }}
                                        &middot; <span class="capitalize">{{ $receipt->method }}</span>
                                        @if ($receipt->reference)
                                            &middot; {{ $receipt->reference }}
                                        @endif
                                    </span>
                                </div>
                                <button type="button" title="Remove" x-on:click="$dispatch('confirm-open', @js([
                                        'title' => 'Remove Receipt',
                                        'message' => 'Remove this receipt and its allocations? Affected invoice balances will be recalculated.',
                                        'method' => 'deleteReceipt',
                                        'params' => [$receipt->id],
                                        'confirmLabel' => 'Remove',
                                    ]))" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                                    <x-icon name="delete" class="w-4 h-4" />
                                </button>
                            </div>
                            <ul class="mt-2 space-y-1 text-xs text-gray-500 dark:text-slate-400">
                                @foreach ($receipt->payments as $payment)
                                    <li>
                                        &rarr; <x-money :amount="$payment->amount" /> applied to
                                        <a href="{{ route('invoices.show', $payment->invoice) }}" wire:navigate class="text-brand-600 dark:text-brand-400 hover:underline">
                                            {{ $payment->invoice->invoice_no }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <x-confirm-modal />
</div>
