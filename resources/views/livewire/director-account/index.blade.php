<div>
    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">{{ __('Director Current Account') }}</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-4">
                <div class="text-xs text-gray-500 dark:text-slate-400">Account Balance</div>
                <div class="text-lg font-semibold text-gray-900 dark:text-slate-100"><x-money :amount="bcsub($totalCredit, $totalDebit, 2)" /></div>
            </div>
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-4">
                <div class="text-xs text-gray-500 dark:text-slate-400">Total Drawings (Debit)</div>
                <div class="text-lg font-semibold text-gray-900 dark:text-slate-100"><x-money :amount="$totalDebit" /></div>
            </div>
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-4">
                <div class="text-xs text-gray-500 dark:text-slate-400">Total Contributions (Credit)</div>
                <div class="text-lg font-semibold text-gray-900 dark:text-slate-100"><x-money :amount="$totalCredit" /></div>
            </div>
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-4">
                <div class="text-xs text-gray-500 dark:text-slate-400">Final Company Profit (MTD)</div>
                <div class="text-lg font-semibold text-gray-900 dark:text-slate-100"><x-money :amount="$finalCompanyProfitMtd" /></div>
            </div>
        </div>

        {{-- Post a completed month's final company profit into the ledger below, as a real
             transaction — this is what actually moves the account balance, not just the live
             MTD figure above (which is still moving and isn't final until the month closes). --}}
        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-4 space-y-3">
            <h3 class="font-semibold text-gray-800 dark:text-slate-200 text-sm">Post Final Earnings to Ledger</h3>
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <x-input-label value="Month" />
                    <input type="month" wire:model.live="postMonth" class="mt-1 block rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm">
                </div>
                <div class="text-sm text-gray-600 dark:text-slate-300 pb-2">
                    @if ($this->alreadyPosted)
                        <span class="text-amber-600 dark:text-amber-400">Already posted for this month.</span>
                    @elseif ($this->postPreview !== null)
                        Will post <span class="font-semibold"><x-money :amount="$this->postPreview" /></span>
                        as a {{ bccomp($this->postPreview, '0', 2) >= 0 ? 'credit' : 'debit' }}.
                    @endif
                </div>
                <button wire:click="postFinalEarnings" @disabled($this->alreadyPosted)
                    class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                    Post to Ledger
                </button>
            </div>
            @if ($postError)
                <p class="text-sm text-red-600 dark:text-red-400">{{ $postError }}</p>
            @endif
            @if ($postedMonths->isNotEmpty())
                <p class="text-xs text-gray-400 dark:text-slate-500">Already posted: {{ $postedMonths->implode(', ') }}</p>
            @endif
        </div>

        <div class="flex justify-end">
            <button wire:click="create" class="inline-flex items-center px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">
                + New Entry
            </button>
        </div>

        @if ($showForm)
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 space-y-4">
                <h3 class="font-semibold text-gray-800 dark:text-slate-200">{{ $editingId ? 'Edit Entry' : 'New Entry' }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <x-input-label value="Date" />
                        <x-text-input type="date" class="mt-1 block w-full" wire:model="txn_date" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label value="Description" />
                        <x-text-input type="text" class="mt-1 block w-full" wire:model="description" />
                        <x-input-error :messages="$errors->get('description')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Debit (drawing)" />
                        <x-text-input type="number" step="0.01" class="mt-1 block w-full" wire:model="debit" />
                    </div>
                    <div>
                        <x-input-label value="Credit (contribution)" />
                        <x-text-input type="number" step="0.01" class="mt-1 block w-full" wire:model="credit" />
                    </div>
                </div>
                <div class="flex gap-2">
                    <button wire:click="save" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">Save</button>
                    <button wire:click="$set('showForm', false)" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-900/50 text-left text-gray-500 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Debit</th>
                        <th class="px-4 py-3">Credit</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @foreach ($transactions as $txn)
                        <tr wire:key="txn-{{ $txn->id }}" class="text-gray-700 dark:text-slate-300">
                            <td class="px-4 py-3">{{ $txn->txn_date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">{{ $txn->description }}</td>
                            <td class="px-4 py-3"><x-money :amount="$txn->debit" /></td>
                            <td class="px-4 py-3"><x-money :amount="$txn->credit" /></td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <button wire:click="edit({{ $txn->id }})" title="Edit" class="text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-slate-100"><x-icon name="edit" class="w-4 h-4" /></button>
                                <button type="button" title="Delete" x-on:click="$dispatch('confirm-open', @js([
                                        'title' => 'Delete Entry',
                                        'message' => 'Delete this entry?',
                                        'method' => 'delete',
                                        'params' => [$txn->id],
                                        'confirmLabel' => 'Delete',
                                    ]))" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"><x-icon name="delete" class="w-4 h-4" /></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        {{ $transactions->links() }}
    </div>

    <x-confirm-modal />
</div>
