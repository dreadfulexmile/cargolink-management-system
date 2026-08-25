<div>
    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">{{ __('Reports') }}</h2>
            <x-date-range-filter :date-from="$dateFrom" :error="$dateRangeError" />
        </div>

        @php($periodLabel = \Carbon\Carbon::parse($dateFrom)->format('d M Y').' – '.\Carbon\Carbon::parse($dateTo)->format('d M Y'))

        <div class="flex flex-wrap gap-3">
                <a href="{{ route('reports.management-report.pdf') }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">
                    Management Report (PDF)
                </a>
                <a href="{{ route('reports.annual-management-report.pdf', ['from' => $dateFrom, 'to' => $dateTo]) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">
                    Annual Report (PDF)
                </a>
                <a href="{{ route('reports.customer-profit.export') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">
                    Customer Profit (Excel) — All Time
                </a>
            </div>
            <p class="text-xs text-gray-400 dark:text-slate-500 -mt-4">Management Report covers this month so far. Annual Report covers the date range selected above (max 1 year), broken down month by month. Customer Profit covers every invoice ever recorded, regardless of the range selected above.</p>

            <!-- Customer/Job-wise profit -->
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 dark:text-slate-200 mb-3">Customer-wise Profit &mdash; {{ $periodLabel }}</h3>
                <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-gray-500 dark:text-slate-400">
                        <tr><th class="py-2">Customer</th><th class="py-2 text-right">Gross Profit</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                        @foreach ($topCustomers as $row)
                            <tr class="text-gray-700 dark:text-slate-300">
                                <td class="py-2">{{ $row['customer']->name }}</td>
                                <td class="py-2 text-right"><x-money :amount="$row['profit']" /></td>
                            </tr>
                        @endforeach
                        @if ($topCustomers->isEmpty())
                            <tr><td class="py-2 text-gray-400" colspan="2">No invoices this period.</td></tr>
                        @endif
                    </tbody>
                </table>
                </div>
            </div>

        <!-- Receivables ageing -->
        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6">
            <h3 class="font-semibold text-gray-800 dark:text-slate-200 mb-3">Receivables Ageing</h3>
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-gray-500 dark:text-slate-400">
                    <tr><th class="py-2">Customer</th><th class="py-2">Invoice</th><th class="py-2">Days</th><th class="py-2">Bucket</th><th class="py-2 text-right">Balance</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @foreach ($ageing as $customerId => $rows)
                        @foreach ($rows as $row)
                            <tr class="text-gray-700 dark:text-slate-300 {{ $row['overdue'] ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                                <td class="py-2">{{ $row['invoice']->customer->name }}</td>
                                <td class="py-2">{{ $row['invoice']->invoice_no }}</td>
                                <td class="py-2">{{ $row['days'] }}</td>
                                <td class="py-2">{{ $row['bucket'] }}</td>
                                <td class="py-2 text-right"><x-money :amount="$row['invoice']->balance_due" /></td>
                            </tr>
                        @endforeach
                    @endforeach
                    @if ($ageing->isEmpty())
                        <tr><td class="py-2 text-gray-400" colspan="5">No outstanding receivables.</td></tr>
                    @endif
                </tbody>
            </table>
            </div>
        </div>

        <!-- Expenses by category -->
        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6">
            <h3 class="font-semibold text-gray-800 dark:text-slate-200 mb-3">Expenses by Category &mdash; {{ $periodLabel }}</h3>
            @foreach ($expensesByCategory as $group => $items)
                <div class="mb-3">
                    <div class="text-sm font-medium text-gray-700 dark:text-slate-300">{{ $group }}</div>
                    <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            @foreach ($items as $name => $amount)
                                <tr class="text-gray-600 dark:text-slate-400">
                                    <td class="py-1 pl-4">{{ $name }}</td>
                                    <td class="py-1 text-right"><x-money :amount="$amount" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            @endforeach
            @if ($expensesByCategory->isEmpty())
                <p class="text-gray-400 text-sm">No expenses recorded this period.</p>
            @endif
        </div>
    </div>
</div>
