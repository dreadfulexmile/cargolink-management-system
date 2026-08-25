<div
    x-data
    x-on:scroll-to-form.window="
        const el = document.getElementById($event.detail.id);
        if (! el) return;
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        const focusable = el.querySelector('input, select, textarea');
        if (focusable) focusable.focus({ preventScroll: true });
    "
>
    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">
                {{ $job->job_no }} &mdash; {{ $job->customer->name }}
            </h2>
            <div class="flex items-center gap-2">
                <select wire:change="updateStatus($event.target.value)" class="text-sm rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                    @foreach (['open', 'cleared', 'invoiced', 'closed'] as $status)
                        <option value="{{ $status }}" @selected($job->status === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                @unless ($job->invoice)
                    <a href="{{ route('invoices.index') }}?job={{ $job->id }}" wire:navigate class="inline-flex items-center px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">
                        Generate Invoice
                    </a>
                @else
                    <a href="{{ route('invoices.show', $job->invoice) }}" wire:navigate class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">
                        View Invoice
                    </a>
                @endunless
            </div>
        </div>

        <!-- Job details -->
        @if (! $showJobEditForm)
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800 dark:text-slate-200">Job Details</h3>
                    <button wire:click="editJobDetails" class="px-3 py-1.5 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Edit</button>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div><span class="text-gray-500 dark:text-slate-400">Customer</span><div class="font-medium text-gray-900 dark:text-slate-100">{{ $job->customer->name }}</div></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Mode</span><div class="font-medium uppercase text-gray-900 dark:text-slate-100">{{ $job->mode }}</div></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Direction</span><div class="font-medium capitalize text-gray-900 dark:text-slate-100">{{ $job->direction }}</div></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Vessel/Flight</span><div class="font-medium text-gray-900 dark:text-slate-100">{{ $job->vessel_flight ?: '—' }}</div></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Container No</span><div class="font-medium text-gray-900 dark:text-slate-100">{{ $job->container_no ?: '—' }}</div></div>
                    <div><span class="text-gray-500 dark:text-slate-400">MBL / HBL</span><div class="font-medium text-gray-900 dark:text-slate-100">{{ $job->mbl_no ?: '—' }} / {{ $job->hbl_no ?: '—' }}</div></div>
                    <div><span class="text-gray-500 dark:text-slate-400">CusDec No</span><div class="font-medium text-gray-900 dark:text-slate-100">{{ $job->cusdec_no ?: '—' }}</div></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Ports</span><div class="font-medium text-gray-900 dark:text-slate-100">{{ $job->port_loading ?: '—' }} → {{ $job->port_discharge ?: '—' }}</div></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Quantity</span><div class="font-medium text-gray-900 dark:text-slate-100">{{ $job->quantity ?: '—' }}</div></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Customer Incentive</span><div class="font-medium text-gray-900 dark:text-slate-100"><x-money :amount="$job->customer_incentive" /></div></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Job Commission</span><div class="font-medium text-gray-900 dark:text-slate-100"><x-money :amount="$job->job_commission" /></div></div>

                    <div class="col-span-2 sm:col-span-4 border-t border-gray-100 dark:border-slate-700 pt-4 -mb-1">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Financials</span>
                    </div>

                    <div><span class="text-gray-500 dark:text-slate-400">Total Job Value</span><div class="font-medium text-gray-900 dark:text-slate-100"><x-money :amount="$totalJobValue" /></div></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Disbursements</span><div class="font-medium text-gray-900 dark:text-slate-100"><x-money :amount="$totalDisbursements" /></div></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Services</span><div class="font-medium text-gray-900 dark:text-slate-100"><x-money :amount="$totalServices" /></div></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Advances Received</span><div class="font-medium text-gray-900 dark:text-slate-100"><x-money :amount="$totalAdvances" /></div></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Internal Service Costs</span><div class="font-medium text-gray-900 dark:text-slate-100"><x-money :amount="$totalServiceCosts" /></div></div>
                    @if ($companyProfit !== null)
                        <div><span class="text-gray-500 dark:text-slate-400">Final Earning</span><div class="font-semibold text-emerald-600 dark:text-emerald-400"><x-money :amount="$companyProfit" /></div></div>
                    @endif
                    <div class="col-span-2 sm:col-span-4"><span class="text-gray-500 dark:text-slate-400">Cargo Description</span><div class="font-medium text-gray-900 dark:text-slate-100">{{ $job->cargo_description ?: '—' }}</div></div>
                    <div class="col-span-2 sm:col-span-4"><span class="text-gray-500 dark:text-slate-400">Remarks</span><div class="font-medium text-gray-900 dark:text-slate-100">{{ $job->remarks ?: '—' }}</div></div>
                </div>
            </div>
        @else
            <div id="job-details-form" class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 space-y-4">
                <h3 class="font-semibold text-gray-800 dark:text-slate-200">Edit Job Details</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="edit_customer_id" value="Customer" />
                        <select id="edit_customer_id" wire:model="customer_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('customer_id')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="edit_mode" value="Mode" />
                        <select id="edit_mode" wire:model="mode" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                            <option value="sea">Sea</option>
                            <option value="air">Air</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="edit_direction" value="Direction" />
                        <select id="edit_direction" wire:model="direction" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                            <option value="import">Import</option>
                            <option value="export">Export</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="edit_vessel_flight" value="Vessel / Flight" />
                        <x-text-input id="edit_vessel_flight" type="text" class="mt-1 block w-full" wire:model="vessel_flight" />
                    </div>
                    <div>
                        <x-input-label for="edit_vessel_date" value="Vessel / Flight Date" />
                        <x-text-input id="edit_vessel_date" type="date" class="mt-1 block w-full" wire:model="vessel_date" />
                    </div>
                    <div>
                        <x-input-label for="edit_container_no" value="Container No" />
                        <x-text-input id="edit_container_no" type="text" class="mt-1 block w-full" wire:model="container_no" />
                    </div>
                    <div>
                        <x-input-label for="edit_port_loading" value="Port of Loading" />
                        <x-text-input id="edit_port_loading" type="text" class="mt-1 block w-full" wire:model="port_loading" />
                    </div>
                    <div>
                        <x-input-label for="edit_port_discharge" value="Port of Discharge" />
                        <x-text-input id="edit_port_discharge" type="text" class="mt-1 block w-full" wire:model="port_discharge" />
                    </div>
                    <div>
                        <x-input-label for="edit_quantity" value="Quantity (e.g. 2 x 40)" />
                        <x-text-input id="edit_quantity" type="text" class="mt-1 block w-full" wire:model="quantity" />
                    </div>
                    <div>
                        <x-input-label for="edit_mbl_no" value="MBL No" />
                        <x-text-input id="edit_mbl_no" type="text" class="mt-1 block w-full" wire:model="mbl_no" />
                    </div>
                    <div>
                        <x-input-label for="edit_hbl_no" value="HBL No" />
                        <x-text-input id="edit_hbl_no" type="text" class="mt-1 block w-full" wire:model="hbl_no" />
                    </div>
                    <div>
                        <x-input-label for="edit_cusdec_no" value="CusDec No" />
                        <x-text-input id="edit_cusdec_no" type="text" class="mt-1 block w-full" wire:model="cusdec_no" />
                    </div>
                    <div>
                        <x-input-label for="edit_customer_incentive" value="Customer Incentive" />
                        <x-text-input id="edit_customer_incentive" type="number" step="0.01" class="mt-1 block w-full" wire:model="customer_incentive" />
                    </div>
                    <div>
                        <x-input-label for="edit_job_commission" value="Job Commission" />
                        <x-text-input id="edit_job_commission" type="number" step="0.01" class="mt-1 block w-full" wire:model="job_commission" />
                    </div>
                    <div class="sm:col-span-3">
                        <x-input-label for="edit_cargo_description" value="Cargo Description" />
                        <x-text-input id="edit_cargo_description" type="text" class="mt-1 block w-full" wire:model="cargo_description" />
                    </div>
                    <div class="sm:col-span-3">
                        <x-input-label for="edit_remarks" value="Remarks" />
                        <x-text-input id="edit_remarks" type="text" class="mt-1 block w-full" wire:model="remarks" />
                    </div>
                </div>
                <div class="flex gap-2">
                    <button wire:click="saveJobDetails" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">Save</button>
                    <button wire:click="cancelJobEdit" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                </div>
            </div>
        @endif

        <!-- Job Per Cost -->
        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 space-y-4">
            <h3 class="font-semibold text-gray-800 dark:text-slate-200">Job Per Cost</h3>

            <form id="cost-line-form" wire:submit="saveCostLine" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                <div>
                    <x-input-label value="Charge Type" />
                    <select wire:model="charge_type_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm">
                        <option value="">— Free text —</option>
                        <optgroup label="Disbursement">
                            @foreach ($chargeTypes->where('kind', 'disbursement') as $ct)
                                <option value="{{ $ct->id }}">{{ $ct->name }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Service">
                            @foreach ($chargeTypes->where('kind', 'service') as $ct)
                                <option value="{{ $ct->id }}">{{ $ct->name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                    <button type="button" wire:click="showAddChargeType" class="mt-1 text-xs text-brand-600 dark:text-brand-400 hover:underline">+ Add new charge type</button>
                </div>
                <div>
                    <x-input-label value="Description (if free text)" />
                    <x-text-input type="text" class="mt-1 block w-full text-sm" wire:model="cost_description" />
                    <x-input-error :messages="$errors->get('cost_description')" class="mt-1" />
                </div>
                <div>
                    <x-input-label value="Amount" />
                    <x-text-input type="number" step="0.01" class="mt-1 block w-full text-sm" wire:model="cost_amount" />
                    <x-input-error :messages="$errors->get('cost_amount')" class="mt-1" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">{{ $editingCostLineId ? 'Save' : 'Add Cost Line' }}</button>
                    @if ($editingCostLineId)
                        <button type="button" wire:click="cancelCostLine" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                    @endif
                </div>
            </form>

            @if ($showNewChargeTypeForm)
                <div class="border-t border-gray-100 dark:border-slate-700 pt-3">
                    <form wire:submit="saveNewChargeType" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                        <div>
                            <x-input-label value="New Charge Type Name" />
                            <x-text-input type="text" class="mt-1 block w-full text-sm" wire:model="new_charge_type_name" />
                            <x-input-error :messages="$errors->get('new_charge_type_name')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label value="Kind" />
                            <select wire:model="new_charge_type_kind" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm">
                                <option value="disbursement">Disbursement</option>
                                <option value="service">Service</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">Save Charge Type</button>
                            <button type="button" wire:click="cancelNewChargeType" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-gray-500 dark:text-slate-400">
                    <tr>
                        <th class="py-2">Charge</th>
                        <th class="py-2">Kind</th>
                        <th class="py-2">Amount</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @foreach ($costLines as $line)
                        <tr wire:key="cost-{{ $line->id }}" class="text-gray-700 dark:text-slate-300">
                            <td class="py-2">{{ $line->displayDescription() }}</td>
                            <td class="py-2">
                                <span class="px-2 py-0.5 rounded text-xs {{ $line->kind === 'service' ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300' : 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-300' }}">
                                    {{ $line->kind }}
                                </span>
                            </td>
                            <td class="py-2"><x-money :amount="$line->amount" /></td>
                            <td class="py-2 text-right space-x-3">
                                <button wire:click="editCostLine({{ $line->id }})" title="Edit" class="text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-slate-100"><x-icon name="edit" class="w-4 h-4" /></button>
                                <button type="button" title="Remove" x-on:click="$dispatch('confirm-open', @js([
                                        'title' => 'Remove Cost Line',
                                        'message' => 'Remove this cost line?',
                                        'method' => 'removeCostLine',
                                        'params' => [$line->id],
                                        'confirmLabel' => 'Remove',
                                    ]))" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"><x-icon name="delete" class="w-4 h-4" /></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        {{-- Internal Service Costs — not printed on the invoice --}}
        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-800 dark:text-slate-200">Internal Service Costs</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                        What was actually paid out to fulfill a service line — e.g. a subcontractor hired for
                        "Transport Charges". Never appears on the invoice; only used to work out the real profit
                        kept on that service.
                    </p>
                </div>

                <form id="service-cost-form" wire:submit="saveServiceCost" class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
                    <div>
                        <x-input-label value="Related Service (optional)" />
                        <select wire:model="service_cost_charge_type_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm">
                            <option value="">— Not categorized —</option>
                            @foreach ($chargeTypes->where('kind', 'service') as $chargeType)
                                <option value="{{ $chargeType->id }}">{{ $chargeType->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('service_cost_charge_type_id')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Paid To (optional)" />
                        <x-text-input type="text" class="mt-1 block w-full text-sm" wire:model="service_cost_paid_to" />
                    </div>
                    <div>
                        <x-input-label value="Notes (optional)" />
                        <x-text-input type="text" class="mt-1 block w-full text-sm" wire:model="service_cost_description" />
                    </div>
                    <div>
                        <x-input-label value="Amount" />
                        <x-text-input type="number" step="0.01" class="mt-1 block w-full text-sm" wire:model="service_cost_amount" />
                        <x-input-error :messages="$errors->get('service_cost_amount')" class="mt-1" />
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">{{ $editingServiceCostId ? 'Save' : 'Add' }}</button>
                        @if ($editingServiceCostId)
                            <button type="button" wire:click="cancelServiceCost" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                        @endif
                    </div>
                </form>

                @if ($serviceCosts->isNotEmpty())
                    <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-gray-500 dark:text-slate-400">
                            <tr>
                                <th class="py-2">Related Service</th>
                                <th class="py-2">Paid To</th>
                                <th class="py-2">Notes</th>
                                <th class="py-2">Amount</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            @foreach ($serviceCosts as $cost)
                                <tr wire:key="service-cost-{{ $cost->id }}" class="text-gray-700 dark:text-slate-300">
                                    <td class="py-2">{{ $cost->displayCategory() }}</td>
                                    <td class="py-2">{{ $cost->paid_to ?: '—' }}</td>
                                    <td class="py-2">{{ $cost->description ?: '—' }}</td>
                                    <td class="py-2"><x-money :amount="$cost->amount" /></td>
                                    <td class="py-2 text-right space-x-3">
                                        <button wire:click="editServiceCost({{ $cost->id }})" title="Edit" class="text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-slate-100"><x-icon name="edit" class="w-4 h-4" /></button>
                                        <button type="button" title="Remove" x-on:click="$dispatch('confirm-open', @js([
                                                'title' => 'Remove Service Cost',
                                                'message' => 'Remove this internal cost entry?',
                                                'method' => 'removeServiceCost',
                                                'params' => [$cost->id],
                                                'confirmLabel' => 'Remove',
                                            ]))" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"><x-icon name="delete" class="w-4 h-4" /></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-slate-400">No internal service costs recorded.</p>
                @endif
        </div>

        <!-- Advances -->
        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 space-y-4">
            <h3 class="font-semibold text-gray-800 dark:text-slate-200">Advances</h3>

            <form id="advance-form" wire:submit="saveAdvance" class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
                <div>
                    <x-input-label value="Type" />
                    <select wire:model="advance_type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm">
                        <option value="advance">Advance</option>
                    </select>
                </div>
                <div>
                    <x-input-label value="Amount" />
                    <x-text-input type="number" step="0.01" class="mt-1 block w-full text-sm" wire:model="advance_amount" />
                    <x-input-error :messages="$errors->get('advance_amount')" class="mt-1" />
                </div>
                <div>
                    <x-input-label value="Receipt No" />
                    <x-text-input type="text" class="mt-1 block w-full text-sm" wire:model="advance_receipt_no" />
                </div>
                <div>
                    <x-input-label value="Name" />
                    <x-text-input type="text" class="mt-1 block w-full text-sm" wire:model="advance_name" />
                </div>
                <div>
                    <x-input-label value="Received On" />
                    <x-text-input type="date" class="mt-1 block w-full text-sm" wire:model="advance_received_on" />
                </div>
                <div class="sm:col-span-5 flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">{{ $editingAdvanceId ? 'Save' : 'Add' }}</button>
                    @if ($editingAdvanceId)
                        <button type="button" wire:click="cancelAdvance" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                    @endif
                </div>
            </form>

            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-gray-500 dark:text-slate-400">
                    <tr>
                        <th class="py-2">Type</th>
                        <th class="py-2">Amount</th>
                        <th class="py-2">Receipt</th>
                        <th class="py-2">Name</th>
                        <th class="py-2">Received</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @foreach ($advances as $advance)
                        <tr wire:key="advance-{{ $advance->id }}" class="text-gray-700 dark:text-slate-300">
                            <td class="py-2 capitalize">{{ $advance->type }}</td>
                            <td class="py-2"><x-money :amount="$advance->amount" /></td>
                            <td class="py-2">{{ $advance->receipt_no ?: '—' }}</td>
                            <td class="py-2">{{ $advance->name ?: '—' }}</td>
                            <td class="py-2">{{ $advance->received_on->format('Y-m-d') }}</td>
                            <td class="py-2 text-right space-x-3">
                                <button wire:click="editAdvance({{ $advance->id }})" title="Edit" class="text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-slate-100"><x-icon name="edit" class="w-4 h-4" /></button>
                                <button type="button" title="Remove" x-on:click="$dispatch('confirm-open', @js([
                                        'title' => 'Remove Entry',
                                        'message' => 'Remove this entry?',
                                        'method' => 'removeAdvance',
                                        'params' => [$advance->id],
                                        'confirmLabel' => 'Remove',
                                    ]))" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"><x-icon name="delete" class="w-4 h-4" /></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <x-confirm-modal />
</div>
