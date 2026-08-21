<div>
    <div class="py-6 sm:py-8 max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @php($activeLorry = $lorries->firstWhere('id', $activeLorryId))

        <div class="flex flex-wrap items-center gap-3 {{ $activeLorry ? 'justify-between' : 'justify-end' }}">
            @if ($activeLorry)
                <x-date-range-filter :date-from="$dateFrom" :error="$dateRangeError" />
            @endif

            <div class="flex items-center gap-2">
                @if ($lorries->isNotEmpty())
                    <select wire:change="selectLorry($event.target.value)"
                        class="rounded-full border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm">
                        @foreach ($lorries as $lorry)
                            <option value="{{ $lorry->id }}" @selected($activeLorryId === $lorry->id)>
                                {{ $lorry->reg_no }}{{ $lorry->name ? ' — '.$lorry->name : '' }}{{ $lorry->is_active ? '' : ' (Inactive)' }}
                            </option>
                        @endforeach
                    </select>
                @endif
                <button type="button" x-data x-on:click="$dispatch('open-modal', 'manage-lorries')"
                    class="px-4 py-2 rounded-full text-sm font-medium bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 hover:bg-gray-200 dark:hover:bg-slate-600">
                    Manage Lorries
                </button>
            </div>
        </div>

        @if ($activeLorry)
            @php($totalHireIncome = bcadd((string) ($activeLorry->total_hire_income ?? '0'), (string) ($activeLorry->total_held_fee ?? '0'), 2))
            @php($net = bcsub($totalHireIncome, (string) ($activeLorry->total_expenses ?? '0'), 2))
            @php($periodLabel = \Carbon\Carbon::parse($dateFrom)->format('d M Y').' – '.\Carbon\Carbon::parse($dateTo)->format('d M Y'))

            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-500 via-brand-600 to-brand-800 dark:from-brand-600 dark:via-brand-700 dark:to-slate-900 text-white p-6 sm:p-8">
                <div class="absolute -right-10 -top-16 w-56 h-56 rounded-full bg-white/10"></div>
                <div class="absolute -right-24 top-10 w-48 h-48 rounded-full bg-white/10"></div>
                <div class="relative flex flex-wrap items-end justify-between gap-6">
                    <div>
                        <div class="text-brand-100 text-sm">Hire Income — {{ $activeLorry->reg_no }}</div>
                        <div class="text-3xl sm:text-4xl font-bold mt-1 whitespace-nowrap">
                            <x-money :amount="$totalHireIncome" :cents="false" :symbol="false" />
                        </div>
                    </div>
                    <div class="flex gap-8">
                        <div>
                            <div class="text-brand-100 text-xs">Number of Hires</div>
                            <div class="text-lg font-semibold whitespace-nowrap">{{ $activeLorry->hires_count ?? 0 }}</div>
                        </div>
                        <div>
                            <div class="text-brand-100 text-xs">Final Income</div>
                            <div class="text-lg font-semibold whitespace-nowrap">
                                <x-money :amount="$net" :cents="false" :symbol="false" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <x-stat-card label="Held-up Fee (Rs)" icon="revenue" color="teal">
                    <x-money :amount="$activeLorry->total_held_fee ?? 0" :cents="false" :symbol="false" />
                </x-stat-card>
                <x-stat-card label="Lease Amount (Rs)" icon="key" color="fuchsia">
                    <x-money :amount="$activeLorry->total_lease ?? 0" :cents="false" :symbol="false" />
                </x-stat-card>
                <x-stat-card label="Diesel (Rs)" icon="cost" color="amber">
                    <x-money :amount="$activeLorry->total_diesel ?? 0" :cents="false" :symbol="false" />
                </x-stat-card>
                <x-stat-card label="Repairs & Maintenance (Rs)" icon="cost" color="amber">
                    <x-money :amount="bcadd((string) ($activeLorry->total_repair ?? 0), (string) ($activeLorry->total_maintenance ?? 0), 2)" :cents="false" :symbol="false" />
                </x-stat-card>
                <x-stat-card label="Driver Fee (Rs)" icon="creditors" color="rose">
                    <x-money :amount="$activeLorry->total_driver_fee ?? 0" :cents="false" :symbol="false" />
                </x-stat-card>
                <x-stat-card label="Yard OT (Rs)" icon="creditors" color="rose">
                    <x-money :amount="$activeLorry->total_yard_ot ?? 0" :cents="false" :symbol="false" />
                </x-stat-card>
            </div>

            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-3xl p-6 space-y-5">
                <div class="flex gap-1 border-b border-gray-100 dark:border-slate-700 -mt-1">
                    <button type="button" wire:click="selectLorryTab('income')"
                        class="px-3 py-2 text-sm font-medium border-b-2 -mb-px {{ $lorryTab === 'income' ? 'border-brand-600 dark:border-brand-400 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-200' }}">
                        Income
                    </button>
                    <button type="button" wire:click="selectLorryTab('expenses')"
                        class="px-3 py-2 text-sm font-medium border-b-2 -mb-px {{ $lorryTab === 'expenses' ? 'border-brand-600 dark:border-brand-400 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-200' }}">
                        Expenses
                    </button>
                </div>

                @if ($lorryTab === 'income')
                    <button wire:click="addHire({{ $activeLorry->id }})" class="px-3 py-1.5 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">+ Add Hire Income</button>
                @elseif ($lorryTab === 'expenses')
                    <button wire:click="addExpense({{ $activeLorry->id }})" class="px-3 py-1.5 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">+ Add Expense</button>
                @endif

                {{-- Hire income form --}}
                @if ($lorryTab === 'income' && $hiringLorryId === $activeLorry->id)
                    <div class="border-t border-gray-100 dark:border-slate-700 pt-3 space-y-3">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-slate-300">{{ $editingHireId ? 'Edit Hire Income' : 'New Hire Income' }}</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                            <div>
                                <x-input-label value="Date" />
                                <x-text-input type="date" class="mt-1 block w-full text-sm" wire:model="hire_date" />
                                <x-input-error :messages="$errors->get('hire_date')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label value="Hired By (optional)" />
                                <x-text-input type="text" class="mt-1 block w-full text-sm" wire:model="hirer_name" />
                            </div>
                            <div>
                                <x-input-label value="Amount" />
                                <x-text-input type="number" step="0.01" class="mt-1 block w-full text-sm" wire:model="hire_amount" />
                                <x-input-error :messages="$errors->get('hire_amount')" class="mt-1" />
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="saveHire" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">Save</button>
                                <button wire:click="cancelHire" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 dark:border-slate-700 pt-3">
                            <p class="text-xs text-gray-500 dark:text-slate-400 mb-2">Held-up fee — charge the customer hourly if the lorry was held up due to their delay.</p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                                <div>
                                    <x-input-label value="Held-up Hours (optional)" />
                                    <div class="mt-1 flex gap-1.5">
                                        <x-text-input type="number" step="0.01" class="block w-full text-sm" wire:model.live="held_hours" />
                                        <button type="button" wire:click="incrementFormHeldHour" title="Add 1 hour" class="shrink-0 px-2.5 rounded-md text-sm font-medium bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 hover:bg-gray-200 dark:hover:bg-slate-600">+1h</button>
                                    </div>
                                    <x-input-error :messages="$errors->get('held_hours')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label value="Rate / Hour (optional)" />
                                    <x-text-input type="number" step="0.01" class="mt-1 block w-full text-sm" wire:model.live="held_hourly_rate" />
                                    <x-input-error :messages="$errors->get('held_hourly_rate')" class="mt-1" />
                                </div>
                                <div class="text-sm text-gray-600 dark:text-slate-300 pb-2">
                                    Held-up fee: <span class="font-semibold"><x-money :amount="$this->heldFeePreview" /></span>
                                </div>
                            </div>
                        </div>

                        @unless ($editingHireId)
                            <div class="border-t border-gray-100 dark:border-slate-700 pt-3">
                                <p class="text-xs text-gray-500 dark:text-slate-400 mb-2">Optionally log running costs for this trip at the same time — each is saved as its own expense entry, dated the same as the hire.</p>
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                    <div>
                                        <x-input-label value="Diesel (optional)" />
                                        <x-text-input type="number" step="0.01" class="mt-1 block w-full text-sm" wire:model="hire_diesel" />
                                        <x-input-error :messages="$errors->get('hire_diesel')" class="mt-1" />
                                    </div>
                                    <div>
                                        <x-input-label value="Driver Fee (optional)" />
                                        <x-text-input type="number" step="0.01" class="mt-1 block w-full text-sm" wire:model="hire_driver_fee" />
                                        <x-input-error :messages="$errors->get('hire_driver_fee')" class="mt-1" />
                                    </div>
                                    <div>
                                        <x-input-label value="Yard OT (optional)" />
                                        <x-text-input type="number" step="0.01" class="mt-1 block w-full text-sm" wire:model="hire_yard_ot" />
                                        <x-input-error :messages="$errors->get('hire_yard_ot')" class="mt-1" />
                                    </div>
                                    <div>
                                        <x-input-label value="Other (optional)" />
                                        <x-text-input type="number" step="0.01" class="mt-1 block w-full text-sm" wire:model="hire_other" />
                                        <x-input-error :messages="$errors->get('hire_other')" class="mt-1" />
                                    </div>
                                </div>
                            </div>
                        @endunless

                        <div>
                            <x-input-label value="Notes (optional)" />
                            <textarea wire:model="hire_notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm"></textarea>
                        </div>
                    </div>
                @endif

                {{-- Expense form --}}
                @if ($lorryTab === 'expenses' && $expensingLorryId === $activeLorry->id)
                    <div class="border-t border-gray-100 dark:border-slate-700 pt-3 space-y-3">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-slate-300">{{ $editingExpenseId ? 'Edit Expense' : 'New Expense' }}</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                            <div>
                                <x-input-label value="Category" />
                                <select wire:model="expense_category" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm">
                                    <option value="lease">Lease</option>
                                    <option value="diesel">Diesel</option>
                                    <option value="repair">Repair</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="driver_fee">Driver Fee</option>
                                    <option value="yard_ot">Yard OT</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label value="Date" />
                                <x-text-input type="date" class="mt-1 block w-full text-sm" wire:model="expense_date" />
                                <x-input-error :messages="$errors->get('expense_date')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label value="Amount" />
                                <x-text-input type="number" step="0.01" class="mt-1 block w-full text-sm" wire:model="expense_amount" />
                                <x-input-error :messages="$errors->get('expense_amount')" class="mt-1" />
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="saveExpense" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">Save</button>
                                <button wire:click="cancelExpense" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                            </div>
                        </div>
                        <div>
                            <x-input-label value="Notes (optional)" />
                            <textarea wire:model="expense_notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm"></textarea>
                        </div>
                    </div>
                @endif

                {{-- Hire income list --}}
                @if ($lorryTab === 'income' && $activeLorry->hires->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-slate-400">No hire income logged for {{ $periodLabel }}.</p>
                @endif
                @if ($lorryTab === 'income' && $activeLorry->hires->isNotEmpty())
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-slate-300 mb-2">Hire Income — {{ $periodLabel }}</h4>
                        <table class="w-full text-sm border-t border-gray-100 dark:border-slate-700 pt-2">
                            <thead class="text-left text-gray-500 dark:text-slate-400">
                                <tr><th class="py-1">Date</th><th class="py-1">Hired By</th><th class="py-1">Amount</th><th class="py-1">Held-up Fee</th><th class="py-1">Total</th><th class="py-1"></th></tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                                @foreach ($activeLorry->hires as $hire)
                                    <tr class="text-gray-700 dark:text-slate-300">
                                        <td class="py-1">{{ $hire->hire_date->format('Y-m-d') }}</td>
                                        <td class="py-1">{{ $hire->hirer_name ?? '—' }}</td>
                                        <td class="py-1"><x-money :amount="$hire->amount" /></td>
                                        <td class="py-1">
                                            @if (bccomp((string) $hire->held_fee, '0', 2) > 0)
                                                <x-money :amount="$hire->held_fee" />
                                                <span class="text-xs text-gray-400 dark:text-slate-500">({{ rtrim(rtrim($hire->held_hours, '0'), '.') }}h &times; {{ number_format((float) $hire->held_hourly_rate, 2) }})</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="py-1 font-medium"><x-money :amount="bcadd((string) $hire->amount, (string) $hire->held_fee, 2)" /></td>
                                        <td class="py-1 space-x-2 whitespace-nowrap">
                                            @if ($hire->held_hourly_rate !== null)
                                                <button wire:click="incrementHeldHour({{ $hire->id }})" title="Add another held-up hour" class="text-xs px-2 py-1 bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400 rounded-md font-medium">+1h</button>
                                            @endif
                                            <button wire:click="editHire({{ $hire->id }})" title="Edit" class="p-1.5 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md"><x-icon name="edit" class="w-3.5 h-3.5" /></button>
                                            <button type="button" title="Delete" x-on:click="$dispatch('confirm-open', @js([
                                                    'title' => 'Delete Hire Income',
                                                    'message' => 'Delete this hire income entry?',
                                                    'method' => 'deleteHire',
                                                    'params' => [$hire->id],
                                                    'confirmLabel' => 'Delete',
                                                ]))" class="p-1.5 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-md"><x-icon name="delete" class="w-3.5 h-3.5" /></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Expense list --}}
                @if ($lorryTab === 'expenses' && $activeLorry->expenses->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-slate-400">No expenses logged for {{ $periodLabel }}.</p>
                @endif
                @if ($lorryTab === 'expenses' && $activeLorry->expenses->isNotEmpty())
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-slate-300 mb-2">Expenses — {{ $periodLabel }}</h4>
                        <table class="w-full text-sm border-t border-gray-100 dark:border-slate-700 pt-2">
                            <thead class="text-left text-gray-500 dark:text-slate-400">
                                <tr><th class="py-1">Date</th><th class="py-1">Category</th><th class="py-1">Amount</th><th class="py-1"></th></tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                                @foreach ($activeLorry->expenses as $expense)
                                    <tr class="text-gray-700 dark:text-slate-300">
                                        <td class="py-1">{{ $expense->expense_date->format('Y-m-d') }}</td>
                                        <td class="py-1 capitalize">{{ str_replace('_', ' ', $expense->category) }}</td>
                                        <td class="py-1"><x-money :amount="$expense->amount" /></td>
                                        <td class="py-1 space-x-2 whitespace-nowrap">
                                            <button wire:click="editExpense({{ $expense->id }})" title="Edit" class="p-1.5 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md"><x-icon name="edit" class="w-3.5 h-3.5" /></button>
                                            <button type="button" title="Delete" x-on:click="$dispatch('confirm-open', @js([
                                                    'title' => 'Delete Expense',
                                                    'message' => 'Delete this expense entry?',
                                                    'method' => 'deleteExpense',
                                                    'params' => [$expense->id],
                                                    'confirmLabel' => 'Delete',
                                                ]))" class="p-1.5 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-md"><x-icon name="delete" class="w-3.5 h-3.5" /></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @elseif ($lorries->isEmpty())
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-3xl p-6 text-sm text-gray-500 dark:text-slate-400">
                No lorries yet. Click "Manage Lorries" above to add one.
            </div>
        @endif
    </div>

    <x-modal name="manage-lorries" maxWidth="lg">
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-lg text-gray-800 dark:text-slate-200">Manage Lorries</h3>
                <button type="button" x-on:click="$dispatch('close')" class="text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300">
                    <x-icon name="close" class="w-5 h-5" />
                </button>
            </div>

            @if ($showLorryForm)
                <div class="bg-gray-50 dark:bg-slate-900/50 rounded-2xl p-4 space-y-3">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-slate-300">{{ $editingLorryId ? 'Edit Lorry' : 'New Lorry' }}</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <x-input-label value="Registration No" />
                            <x-text-input type="text" class="mt-1 block w-full text-sm" wire:model="reg_no" />
                            <x-input-error :messages="$errors->get('reg_no')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label value="Name / Label (optional)" />
                            <x-text-input type="text" class="mt-1 block w-full text-sm" wire:model="name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="saveLorry" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">Save</button>
                        <button wire:click="cancelLorryForm" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                    </div>
                </div>
            @else
                <button type="button" wire:click="createLorry" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">+ New Lorry</button>
            @endif

            @if ($lorries->isNotEmpty())
                <div class="max-h-80 overflow-y-auto -mx-1 px-1">
                    <table class="w-full text-sm">
                        <thead class="text-left text-gray-500 dark:text-slate-400">
                            <tr><th class="py-2">Reg No</th><th class="py-2">Name</th><th class="py-2">Active</th><th class="py-2"></th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            @foreach ($lorries as $lorry)
                                <tr wire:key="manage-lorry-{{ $lorry->id }}" class="{{ $lorry->is_active ? 'text-gray-700 dark:text-slate-300' : 'text-gray-400 dark:text-slate-500 bg-gray-50/60 dark:bg-slate-900/30' }}">
                                    <td class="py-2 font-medium {{ $lorry->is_active ? 'text-gray-900 dark:text-slate-100' : 'text-gray-400 dark:text-slate-500' }}">
                                        <span class="inline-flex items-center gap-2">
                                            {{ $lorry->reg_no }}
                                            @unless ($lorry->is_active)
                                                <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">Inactive</span>
                                            @endunless
                                        </span>
                                    </td>
                                    <td class="py-2">{{ $lorry->name ?: '—' }}</td>
                                    <td class="py-2">
                                        <x-active-toggle :active="$lorry->is_active" :params="[$lorry->id]" :label="$lorry->reg_no" entity="Lorry" />
                                    </td>
                                    <td class="py-2 text-right space-x-3 whitespace-nowrap">
                                        <button wire:click="editLorry({{ $lorry->id }})" title="Edit" class="text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-slate-100"><x-icon name="edit" class="w-4 h-4" /></button>
                                        <button type="button" title="Delete" x-on:click="$dispatch('confirm-open', @js([
                                                'title' => 'Delete Lorry',
                                                'message' => "Delete {$lorry->reg_no} and all its hire income and expense records? This cannot be undone.",
                                                'method' => 'deleteLorry',
                                                'params' => [$lorry->id],
                                                'confirmLabel' => 'Delete',
                                            ]))" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"><x-icon name="delete" class="w-4 h-4" /></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-slate-400">No lorries yet.</p>
            @endif
        </div>
    </x-modal>

    <x-confirm-modal />
</div>
