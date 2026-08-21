<div>
    <div class="py-6 sm:py-8 max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">{{ __('Dashboard') }}</h2>
            <x-date-range-filter :date-from="$dateFrom" :error="$dateRangeError" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <!-- Left: headline profit, cost breakdown, recent jobs -->
            <div class="lg:col-span-2 space-y-6">
                @role('gm')
                    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-500 via-brand-600 to-brand-800 dark:from-brand-600 dark:via-brand-700 dark:to-slate-900 text-white p-6 sm:p-8">
                        <div class="absolute -right-10 -top-16 w-56 h-56 rounded-full bg-white/10"></div>
                        <div class="absolute -right-24 top-10 w-48 h-48 rounded-full bg-white/10"></div>
                        <div class="relative flex flex-wrap items-end justify-between gap-6">
                            <div>
                                <div class="text-brand-100 text-sm">Revenue (Selected Period)</div>
                                <div class="text-3xl sm:text-4xl font-bold mt-1 whitespace-nowrap">
                                    <x-money :amount="$summary['revenue']" :cents="false" :symbol="false" />
                                </div>
                            </div>
                            <div class="flex gap-8">
                                <div>
                                    <div class="text-brand-100 text-xs">Gross Profit</div>
                                    <div class="text-lg font-semibold whitespace-nowrap">
                                        <x-money :amount="$summary['gross_profit']" :cents="false" :symbol="false" />
                                    </div>
                                </div>
                                <div>
                                    <div class="text-brand-100 text-xs">Final Earning</div>
                                    <div class="text-lg font-semibold whitespace-nowrap">
                                        <x-money :amount="$summary['final_company_profit']" :cents="false" :symbol="false" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <x-stat-card label="Cost of Services (Rs)" icon="cost" color="amber">
                            <x-money :amount="$summary['cost_of_services']" :cents="false" :symbol="false" />
                        </x-stat-card>
                        <x-stat-card label="Internal Service Costs (Rs)" icon="cost" color="rose">
                            <x-money :amount="$summary['internal_service_costs']" :cents="false" :symbol="false" />
                        </x-stat-card>
                        <x-stat-card label="Operating Expenses (Rs)" icon="expenses" color="rose">
                            <x-money :amount="$summary['operating_expenses']" :cents="false" :symbol="false" />
                        </x-stat-card>
                        <x-stat-card label="Lease Payments (Rs)" icon="key" color="violet">
                            <x-money :amount="$summary['lease_payments']" :cents="false" :symbol="false" />
                        </x-stat-card>
                        <x-stat-card label="Creditor Repayments (Rs)" icon="creditors" color="fuchsia">
                            <x-money :amount="$summary['creditor_repayments']" :cents="false" :symbol="false" />
                        </x-stat-card>
                        <x-stat-card label="Operating Profit (Rs)" icon="profit" color="emerald">
                            <x-money :amount="$summary['operating_profit']" :cents="false" :symbol="false" />
                        </x-stat-card>
                    </div>
                @endrole

                <div class="grid grid-cols-2 gap-4">
                    <x-stat-card label="Jobs (Selected Period)" icon="jobs" color="indigo">
                        {{ $jobCounts['total'] }}
                        <x-slot:footer>{{ $jobCounts['sea'] }} sea / {{ $jobCounts['air'] }} air</x-slot:footer>
                    </x-stat-card>
                    <x-stat-card label="Active Customers" icon="customers" color="teal">
                        {{ $jobCounts['active_customers'] }}
                        <x-slot:footer>{{ $jobCounts['import'] }} import / {{ $jobCounts['export'] }} export</x-slot:footer>
                    </x-stat-card>
                </div>

                <div class="bg-white dark:bg-slate-800 shadow-sm rounded-3xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800 dark:text-slate-200">Recent Jobs</h3>
                        <a href="{{ route('jobs.index') }}" wire:navigate class="text-sm text-brand-600 dark:text-brand-400 hover:underline">View all</a>
                    </div>

                    @forelse ($recentJobs as $job)
                        <a href="{{ route('jobs.show', $job) }}" wire:navigate
                            class="flex items-center justify-between gap-4 py-3 border-b border-gray-100 dark:border-slate-700 last:border-0 hover:bg-gray-50 dark:hover:bg-slate-700/40 -mx-2 px-2 rounded-lg">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-300 shrink-0">
                                    <x-icon name="jobs" class="w-5 h-5" />
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-gray-900 dark:text-slate-100 truncate">{{ $job->job_no }}</div>
                                    <div class="text-xs text-gray-400 dark:text-slate-500 truncate">{{ $job->customer->name }} &middot; {{ $job->created_at->format('d M Y') }}</div>
                                </div>
                            </div>
                            <span class="text-xs px-2.5 py-1 rounded-full capitalize shrink-0 {{ match ($job->status) {
                                'closed' => 'bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-slate-300',
                                'invoiced' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
                                'cleared' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-400',
                                default => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
                            } }}">{{ $job->status }}</span>
                        </a>
                    @empty
                        <p class="text-sm text-gray-400 dark:text-slate-500">No jobs opened in this period.</p>
                    @endforelse
                </div>
            </div>

            <!-- Right: margin chart, at-a-glance balances -->
            <div class="space-y-6">
                @role('gm')
                    <div class="bg-white dark:bg-slate-800 shadow-sm rounded-3xl p-6">
                        <h3 class="font-semibold text-gray-800 dark:text-slate-200 mb-4">Revenue / Cost / Gross Profit</h3>
                        <canvas
                            x-data="{
                                init() {
                                    new Chart(this.$el, {
                                        type: 'bar',
                                        data: {
                                            labels: ['Revenue', 'Cost of Services', 'Gross Profit'],
                                            datasets: [{
                                                data: [{{ $summary['revenue'] }}, {{ $summary['cost_of_services'] }}, {{ $summary['gross_profit'] }}],
                                                backgroundColor: ['#18226b', '#f59e0b', '#10b981'],
                                                borderRadius: 8,
                                            }],
                                        },
                                        options: { plugins: { legend: { display: false } }, responsive: true },
                                    });
                                }
                            }"
                            height="180"
                        ></canvas>
                    </div>
                @endrole

                <div class="space-y-4">
                    <x-stat-card label="Receivables Outstanding (Rs)" icon="clock" color="sky">
                        <x-money :amount="$totalOutstanding" :cents="false" :symbol="false" />
                        <x-slot:footer>
                            <span class="text-red-600 dark:text-red-400">Overdue: <x-money :amount="$totalOverdue" :cents="false" :symbol="false" /></span>
                        </x-slot:footer>
                    </x-stat-card>

                    @role('gm')
                        <x-stat-card label="Total Debt (Rs)" icon="creditors" color="rose">
                            <x-money :amount="$totalDebt" :cents="false" :symbol="false" />
                        </x-stat-card>
                        <x-stat-card
                            label="Director Drawings vs Profit (Rs)"
                            icon="warning"
                            color="amber"
                            :highlight="bccomp($excessDrawings, '0', 2) > 0"
                        >
                            <x-money :amount="$drawings" :cents="false" :symbol="false" />
                            @if (bccomp($excessDrawings, '0', 2) > 0)
                                <x-slot:footer>
                                    <span class="text-amber-700 dark:text-amber-400">Excess over profit: <x-money :amount="$excessDrawings" :cents="false" :symbol="false" /></span>
                                </x-slot:footer>
                            @endif
                        </x-stat-card>
                    @endrole
                </div>
            </div>
        </div>
    </div>
</div>
