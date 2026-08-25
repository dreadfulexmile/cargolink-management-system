<div>
    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">{{ __('Jobs') }}</h2>

        <div class="flex flex-wrap items-center gap-3">
            <select wire:model.live="filterCustomer" class="rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm">
                <option value="">All customers</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
            <x-date-range-filter :date-from="$dateFrom" :error="$dateRangeError" />
            <select wire:model.live="filterMode" class="rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm">
                <option value="">All modes</option>
                <option value="sea">Sea</option>
                <option value="air">Air</option>
            </select>
            <select wire:model.live="filterDirection" class="rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm">
                <option value="">All directions</option>
                <option value="import">Import</option>
                <option value="export">Export</option>
            </select>
            <button wire:click="create" class="ml-auto inline-flex items-center px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium hover:bg-brand-700 dark:hover:bg-brand-400">
                + New Job
            </button>
        </div>

        @if ($showForm)
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 space-y-4">
                <h3 class="font-semibold text-gray-800 dark:text-slate-200">Open Job File</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="customer_id" value="Customer" />
                        <select id="customer_id" wire:model="customer_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                            <option value="0">Select customer</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('customer_id')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="mode" value="Mode" />
                        <select id="mode" wire:model="mode" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                            <option value="sea">Sea</option>
                            <option value="air">Air</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="direction" value="Direction" />
                        <select id="direction" wire:model="direction" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                            <option value="import">Import</option>
                            <option value="export">Export</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="vessel_flight" value="Vessel / Flight" />
                        <x-text-input id="vessel_flight" type="text" class="mt-1 block w-full" wire:model="vessel_flight" />
                    </div>
                    <div>
                        <x-input-label for="vessel_date" value="Vessel / Flight Date" />
                        <x-text-input id="vessel_date" type="date" class="mt-1 block w-full" wire:model="vessel_date" />
                    </div>
                    <div>
                        <x-input-label for="container_no" value="Container No" />
                        <x-text-input id="container_no" type="text" class="mt-1 block w-full" wire:model="container_no" />
                    </div>
                    <div>
                        <x-input-label for="port_loading" value="Port of Loading" />
                        <x-text-input id="port_loading" type="text" class="mt-1 block w-full" wire:model="port_loading" />
                    </div>
                    <div>
                        <x-input-label for="port_discharge" value="Port of Discharge" />
                        <x-text-input id="port_discharge" type="text" class="mt-1 block w-full" wire:model="port_discharge" />
                    </div>
                    <div>
                        <x-input-label for="quantity" value="Quantity (e.g. 2 x 40)" />
                        <x-text-input id="quantity" type="text" class="mt-1 block w-full" wire:model="quantity" />
                    </div>
                    <div>
                        <x-input-label for="mbl_no" value="MBL No" />
                        <x-text-input id="mbl_no" type="text" class="mt-1 block w-full" wire:model="mbl_no" />
                    </div>
                    <div>
                        <x-input-label for="hbl_no" value="HBL No" />
                        <x-text-input id="hbl_no" type="text" class="mt-1 block w-full" wire:model="hbl_no" />
                    </div>
                    <div>
                        <x-input-label for="cusdec_no" value="CusDec No" />
                        <x-text-input id="cusdec_no" type="text" class="mt-1 block w-full" wire:model="cusdec_no" />
                    </div>
                    <div>
                        <x-input-label for="customer_incentive" value="Customer Incentive" />
                        <x-text-input id="customer_incentive" type="number" step="0.01" class="mt-1 block w-full" wire:model="customer_incentive" />
                    </div>
                    <div>
                        <x-input-label for="job_commission" value="Job Commission" />
                        <x-text-input id="job_commission" type="number" step="0.01" class="mt-1 block w-full" wire:model="job_commission" />
                    </div>
                    <div class="sm:col-span-3">
                        <x-input-label for="cargo_description" value="Cargo Description" />
                        <x-text-input id="cargo_description" type="text" class="mt-1 block w-full" wire:model="cargo_description" />
                    </div>
                    <div class="sm:col-span-3">
                        <x-input-label for="remarks" value="Remarks" />
                        <x-text-input id="remarks" type="text" class="mt-1 block w-full" wire:model="remarks" />
                    </div>
                </div>
                <div class="flex gap-2">
                    <button wire:click="save" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">Save &amp; Open</button>
                    <button wire:click="cancel" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-900/50 text-left text-gray-500 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Job No</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Mode</th>
                        <th class="px-4 py-3">Direction</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Opened</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @foreach ($jobs as $job)
                        <tr wire:key="job-{{ $job->id }}" class="text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/50">
                            <td class="px-4 py-3 font-medium">
                                <a href="{{ route('jobs.show', $job) }}" wire:navigate class="text-brand-600 dark:text-brand-400 underline underline-offset-2 hover:text-brand-700 dark:hover:text-brand-300">{{ $job->job_no }}</a>
                            </td>
                            <td class="px-4 py-3 max-w-[200px] truncate" title="{{ $job->customer->name }}">{{ $job->customer->name }}</td>
                            <td class="px-4 py-3 uppercase">{{ $job->mode }}</td>
                            <td class="px-4 py-3 capitalize">{{ $job->direction }}</td>
                            <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $job->status) }}</td>
                            <td class="px-4 py-3">{{ $job->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        {{ $jobs->links() }}
    </div>
</div>
