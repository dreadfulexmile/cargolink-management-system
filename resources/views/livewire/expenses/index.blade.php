<div>
    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">{{ __('Expenses') }}</h2>

        <div class="flex flex-wrap items-center justify-between gap-4">
            <x-date-range-filter :date-from="$dateFrom" :error="$dateRangeError" />
            <div class="text-sm text-gray-600 dark:text-slate-400">
                Total: <x-money :amount="$rangeTotal" class="font-semibold text-gray-900 dark:text-slate-100" />
            </div>
            <button wire:click="create" class="shrink-0 inline-flex items-center px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium hover:bg-brand-700 dark:hover:bg-brand-400">
                + New Expense
            </button>
        </div>

        @if ($showForm)
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 space-y-4">
                <h3 class="font-semibold text-gray-800 dark:text-slate-200">{{ $editingId ? 'Edit Expense' : 'New Expense' }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Category" />
                        <select wire:model="expense_category_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                            <option value="0">Select category</option>
                            @foreach ($categories->groupBy('group') as $group => $items)
                                <optgroup label="{{ $group }}">
                                    @foreach ($items as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('expense_category_id')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Date" />
                        <x-text-input type="date" class="mt-1 block w-full" wire:model="expense_date" />
                        <x-input-error :messages="$errors->get('expense_date')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Amount" />
                        <x-text-input type="number" step="0.01" class="mt-1 block w-full" wire:model="amount" />
                        <x-input-error :messages="$errors->get('amount')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Payee" />
                        <x-text-input type="text" class="mt-1 block w-full" wire:model="payee" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label value="Note" />
                        <x-text-input type="text" class="mt-1 block w-full" wire:model="note" />
                    </div>
                </div>
                <div class="flex gap-2">
                    <button wire:click="save" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">Save</button>
                    <button wire:click="cancel" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-900/50 text-left text-gray-500 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Payee</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @foreach ($expenses as $expense)
                        <tr wire:key="expense-{{ $expense->id }}" class="text-gray-700 dark:text-slate-300">
                            <td class="px-4 py-3">{{ $expense->expense_date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">{{ $expense->category->name }}</td>
                            <td class="px-4 py-3">{{ $expense->payee ?: '—' }}</td>
                            <td class="px-4 py-3"><x-money :amount="$expense->amount" /></td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <button wire:click="edit({{ $expense->id }})" title="Edit" class="text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-slate-100"><x-icon name="edit" class="w-4 h-4" /></button>
                                <button type="button" title="Delete" x-on:click="$dispatch('confirm-open', @js([
                                        'title' => 'Delete Expense',
                                        'message' => 'Delete this expense?',
                                        'method' => 'delete',
                                        'params' => [$expense->id],
                                        'confirmLabel' => 'Delete',
                                    ]))" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"><x-icon name="delete" class="w-4 h-4" /></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        {{ $expenses->links() }}
    </div>

    <x-confirm-modal />
</div>
