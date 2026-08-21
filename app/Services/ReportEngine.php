<?php

namespace App\Services;

use App\Models\Creditor;
use App\Models\CreditorPayment;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\LeasePayment;
use Carbon\Carbon;

class ReportEngine
{
    public function periodSummary(Carbon $start, Carbon $end): array
    {
        $invoices = Invoice::whereBetween('invoice_date', [$start->toDateString(), $end->toDateString()])
            ->with('job.costLines', 'job.serviceCosts')
            ->get();

        $revenue = $invoices->reduce(fn (string $c, Invoice $i) => bcadd($c, (string) $i->subtotal, 2), '0.00');

        // "Cost of services" here means the disbursements recovered at cost — the pass-through
        // portion of costing, not the service lines (those are the company's actual earnings
        // and are what makes up the gross profit margin below). See docs/DOMAIN.md.
        $costOfServices = $invoices->reduce(
            fn (string $c, Invoice $i) => bcadd($c, (string) $i->job->costLines->where('kind', 'disbursement')->sum('amount'), 2),
            '0.00'
        );

        // What was actually paid out (e.g. to subcontractors) to fulfill service lines on
        // these invoiced jobs — a real expense that reduces the margin kept on service
        // charges, even though it's never billed to the customer. See JobServiceCost.
        $internalServiceCosts = $invoices->reduce(
            fn (string $c, Invoice $i) => bcadd($c, (string) $i->job->serviceCosts->sum('amount'), 2),
            '0.00'
        );

        $grossProfit = bcsub(bcsub($revenue, $costOfServices, 2), $internalServiceCosts, 2);

        $operatingExpenses = (string) Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])->sum('amount');
        $operatingProfit = bcsub($grossProfit, $operatingExpenses, 2);

        $leasePayments = (string) LeasePayment::whereBetween('period', [$start->toDateString(), $end->toDateString()])->sum('amount');
        $profitAfterLeases = bcsub($operatingProfit, $leasePayments, 2);

        $creditorRepayments = (string) CreditorPayment::whereBetween('period', [$start->toDateString(), $end->toDateString()])->sum('amount');
        $finalCompanyProfit = bcsub($profitAfterLeases, $creditorRepayments, 2);

        return [
            'revenue' => $revenue,
            'cost_of_services' => $costOfServices,
            'internal_service_costs' => $internalServiceCosts,
            'gross_profit' => $grossProfit,
            'operating_expenses' => $operatingExpenses,
            'operating_profit' => $operatingProfit,
            'lease_payments' => $leasePayments,
            'profit_after_leases' => $profitAfterLeases,
            'creditor_repayments' => $creditorRepayments,
            'final_company_profit' => $finalCompanyProfit,
        ];
    }

    public function jobCounts(Carbon $start, Carbon $end): array
    {
        $jobs = Job::whereBetween('created_at', [$start, $end])->get(['mode', 'direction', 'customer_id']);

        return [
            'sea' => $jobs->where('mode', 'sea')->count(),
            'air' => $jobs->where('mode', 'air')->count(),
            'import' => $jobs->where('direction', 'import')->count(),
            'export' => $jobs->where('direction', 'export')->count(),
            'total' => $jobs->count(),
            'active_customers' => $jobs->pluck('customer_id')->unique()->count(),
        ];
    }

    public function totalDebt(): string
    {
        return (string) Creditor::sum('outstanding');
    }

    public function topCustomersByProfit(Carbon $start, Carbon $end, int $limit = 5): \Illuminate\Support\Collection
    {
        return Invoice::whereBetween('invoice_date', [$start->toDateString(), $end->toDateString()])
            ->with('job.costLines', 'job.serviceCosts', 'customer')
            ->get()
            ->groupBy('customer_id')
            ->map(function ($invoices) {
                $profit = $invoices->reduce(function (string $carry, Invoice $invoice) {
                    $disbursements = (string) $invoice->job->costLines->where('kind', 'disbursement')->sum('amount');
                    $serviceCosts = (string) $invoice->job->serviceCosts->sum('amount');
                    $cost = bcadd($disbursements, $serviceCosts, 2);

                    return bcadd($carry, bcsub((string) $invoice->subtotal, $cost, 2), 2);
                }, '0.00');

                return [
                    'customer' => $invoices->first()->customer,
                    'profit' => $profit,
                ];
            })
            ->sortByDesc(fn ($row) => (float) $row['profit'])
            ->take($limit)
            ->values();
    }
}
