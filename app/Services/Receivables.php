<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Collection;

class Receivables
{
    public function ageing(): Collection
    {
        $today = now('Asia/Colombo')->startOfDay();

        return Invoice::query()
            ->with('customer')
            ->where('balance_due', '>', 0)
            ->get()
            ->map(function (Invoice $invoice) use ($today) {
                $days = (int) $invoice->invoice_date->startOfDay()->diffInDays($today);
                $bucket = match (true) {
                    $days <= 30 => 'current',
                    $days <= 60 => '30',
                    $days <= 90 => '60',
                    default => '90+',
                };

                return [
                    'invoice' => $invoice,
                    'days' => $days,
                    'bucket' => $bucket,
                    'overdue' => $days > $invoice->customer->credit_days,
                ];
            })
            ->groupBy(fn ($row) => $row['invoice']->customer_id);
    }

    public function totalOutstanding(): string
    {
        return (string) Invoice::sum('balance_due');
    }

    public function totalOverdue(): string
    {
        $today = now('Asia/Colombo')->startOfDay();

        return Invoice::with('customer')
            ->where('balance_due', '>', 0)
            ->get()
            ->filter(fn (Invoice $invoice) => $today->diffInDays($invoice->invoice_date) > $invoice->customer->credit_days)
            ->reduce(fn (string $carry, Invoice $invoice) => bcadd($carry, (string) $invoice->balance_due, 2), '0.00');
    }
}
