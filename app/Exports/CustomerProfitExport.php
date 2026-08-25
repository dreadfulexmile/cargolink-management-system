<?php

namespace App\Exports;

use App\Services\ReportEngine;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Deliberately no date range — every invoice ever recorded. See
// ReportExportController::customerProfitExcel().
class CustomerProfitExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings
{
    public function collection()
    {
        $rows = app(ReportEngine::class)->customerProfitDetail();

        $totals = [
            'revenue' => $rows->reduce(fn (string $c, array $r) => bcadd($c, $r['revenue'], 2), '0.00'),
            'cost_of_services' => $rows->reduce(fn (string $c, array $r) => bcadd($c, $r['cost_of_services'], 2), '0.00'),
            'internal_service_costs' => $rows->reduce(fn (string $c, array $r) => bcadd($c, $r['internal_service_costs'], 2), '0.00'),
            'gross_profit' => $rows->reduce(fn (string $c, array $r) => bcadd($c, $r['gross_profit'], 2), '0.00'),
        ];

        $rows = $rows->map(fn (array $r) => [
            $r['customer'],
            $r['job_no'],
            $r['invoice_no'],
            $r['invoice_date'],
            $r['revenue'],
            $r['cost_of_services'],
            $r['internal_service_costs'],
            $r['gross_profit'],
        ]);

        // Grand total row, so the sheet doesn't need a pivot table just to
        // answer "what's the total gross profit for this range".
        $rows->push(['TOTAL', '', '', '', $totals['revenue'], $totals['cost_of_services'], $totals['internal_service_costs'], $totals['gross_profit']]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Customer', 'Job No', 'Invoice No', 'Invoice Date',
            'Revenue (LKR)', 'Cost of Services (LKR)', 'Internal Service Costs (LKR)', 'Gross Profit (LKR)',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }
}
