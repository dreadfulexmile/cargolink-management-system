<div>
    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">{{ __('Invoices') }}</h2>

        @if ($job)
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 space-y-4">
                <h3 class="font-semibold text-gray-800 dark:text-slate-200">Generate Invoice for {{ $job->job_no }} &mdash; {{ $job->customer->name }}</h3>
                <p class="text-sm text-gray-500 dark:text-slate-400">
                    Lines are pre-filled from the job's cost sheet. Adjust amounts to what's actually billed to the
                    customer before saving &mdash; the difference between these lines and the job cost feeds job/company profit.
                </p>

                <div class="max-w-xs">
                    <x-input-label for="invoice_date" value="Invoice Date" />
                    <x-text-input id="invoice_date" type="date" class="mt-1 block w-full" wire:model="invoice_date" />
                    <x-input-error :messages="$errors->get('invoice_date')" class="mt-1" />
                </div>

                <table class="w-full text-sm">
                    <thead class="text-left text-gray-500 dark:text-slate-400">
                        <tr><th class="py-2">Description</th><th class="py-2">Kind</th><th class="py-2">Amount</th><th class="py-2"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                        @foreach ($draftLines as $i => $line)
                            <tr wire:key="draft-{{ $i }}">
                                <td class="py-2"><x-text-input type="text" class="block w-full text-sm" wire:model="draftLines.{{ $i }}.description" /></td>
                                <td class="py-2">
                                    <select wire:model="draftLines.{{ $i }}.kind" class="rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm">
                                        <option value="disbursement">Disbursement</option>
                                        <option value="service">Service</option>
                                    </select>
                                </td>
                                <td class="py-2"><x-text-input type="number" step="0.01" class="block w-full text-sm" wire:model="draftLines.{{ $i }}.amount" /></td>
                                <td class="py-2 text-right">
                                    <button type="button" wire:click="removeDraftLine({{ $i }})" title="Remove" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"><x-icon name="delete" class="w-4 h-4" /></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <x-input-error :messages="$errors->get('draftLines')" class="mt-1" />

                <button type="button" wire:click="addDraftLine" class="text-sm text-gray-600 dark:text-slate-300 hover:underline">+ Add line</button>

                <div class="flex gap-2">
                    <button wire:click="generate" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">Generate Invoice</button>
                    <button wire:click="cancelGenerate" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                </div>
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-3">
            <select wire:model.live="filterStatus" class="rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm">
                <option value="">All statuses</option>
                <option value="unpaid">Unpaid</option>
                <option value="part_paid">Part Paid</option>
                <option value="paid">Paid</option>
            </select>
            <select wire:model.live="filterCustomer" class="rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm">
                <option value="">All customers</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-900/50 text-left text-gray-500 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Invoice No</th>
                        <th class="px-4 py-3">Job</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Subtotal</th>
                        <th class="px-4 py-3">Balance Due</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @foreach ($invoices as $invoice)
                        <tr class="text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/50">
                            <td class="px-4 py-3 font-medium">
                                <a href="{{ route('invoices.show', $invoice) }}" wire:navigate class="text-brand-600 dark:text-brand-400 underline underline-offset-2 hover:text-brand-700 dark:hover:text-brand-300">{{ $invoice->invoice_no }}</a>
                            </td>
                            <td class="px-4 py-3">{{ $invoice->job->job_no }}</td>
                            <td class="px-4 py-3 max-w-[200px] truncate" title="{{ $invoice->customer->name }}">{{ $invoice->customer->name }}</td>
                            <td class="px-4 py-3">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3"><x-money :amount="$invoice->subtotal" /></td>
                            <td class="px-4 py-3"><x-money :amount="$invoice->balance_due" /></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs whitespace-nowrap
                                    {{ $invoice->status === 'paid' ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300' : ($invoice->status === 'part_paid' ? 'bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300' : 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-300') }}">
                                    {{ str_replace('_', ' ', $invoice->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $invoices->links() }}
    </div>
</div>
