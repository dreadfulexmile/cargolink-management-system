<?php

namespace App\Exports;

use App\Services\ReportEngine;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomerProfitExport implements FromCollection, WithHeadings
{
    public function __construct(private Carbon $start, private Carbon $end)
    {
    }

    public function collection()
    {
        return app(ReportEngine::class)
            ->topCustomersByProfit($this->start, $this->end, 1000)
            ->map(fn ($row) => [
                'customer' => $row['customer']->name,
                'profit' => $row['profit'],
            ]);
    }

    public function headings(): array
    {
        return ['Customer', 'Gross Profit (LKR)'];
    }
}
