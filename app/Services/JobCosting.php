<?php

namespace App\Services;

use App\Models\Job;

class JobCosting
{
    public function totalDisbursements(Job $job): string
    {
        return $this->sumLines($job, 'disbursement');
    }

    public function totalServices(Job $job): string
    {
        return $this->sumLines($job, 'service');
    }

    public function totalCost(Job $job): string
    {
        return bcadd($this->totalDisbursements($job), $this->totalServices($job), 2);
    }

    /**
     * What was actually paid out (e.g. to a subcontractor) to fulfill service lines —
     * never billed to the customer, so it never appears on the invoice. This is what
     * separates the service amount billed from the real margin kept on it.
     */
    public function totalServiceCosts(Job $job): string
    {
        return $job->serviceCosts()->get()
            ->reduce(fn (string $carry, $cost) => bcadd($carry, (string) $cost->amount, 2), '0.00');
    }

    public function totalAdvances(Job $job): string
    {
        return $job->advances()->where('type', 'advance')->get()
            ->reduce(fn (string $carry, $advance) => bcadd($carry, (string) $advance->amount, 2), '0.00');
    }

    /**
     * Disbursements are pass-through — billed at cost, so they net to zero margin here.
     * Service lines are the company's real earnings, reduced by whatever was actually
     * paid out to deliver them (see totalServiceCosts) rather than by their billed amount.
     */
    public function jobProfit(Job $job, string $invoiceSubtotal): string
    {
        $afterDisbursements = bcsub($invoiceSubtotal, $this->totalDisbursements($job), 2);

        return bcsub($afterDisbursements, $this->totalServiceCosts($job), 2);
    }

    public function companyProfit(Job $job, string $invoiceSubtotal): string
    {
        $profit = $this->jobProfit($job, $invoiceSubtotal);
        $profit = bcsub($profit, (string) $job->customer_incentive, 2);

        return bcsub($profit, (string) $job->job_commission, 2);
    }

    private function sumLines(Job $job, string $kind): string
    {
        return $job->costLines()->where('kind', $kind)->get()
            ->reduce(fn (string $carry, $line) => bcadd($carry, (string) $line->amount, 2), '0.00');
    }
}
