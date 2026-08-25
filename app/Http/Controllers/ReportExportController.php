<?php

namespace App\Http\Controllers;

use App\Exports\CustomerProfitExport;
use App\Services\ReportEngine;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportExportController extends Controller
{
    // Always this month, start to now — never a past month, never the
    // rest of the month that hasn't happened yet. Matches the "current
    // month so far" convention documented in CLAUDE.md.
    public function managementReportPdf()
    {
        $start = now('Asia/Colombo')->startOfMonth();
        $end = now('Asia/Colombo');

        $engine = app(ReportEngine::class);

        $pdf = Pdf::loadView('pdf.management-report', [
            'month' => $start->format('F Y').' (so far, as of '.$end->format('d M').')',
            'summary' => $engine->periodSummary($start, $end),
            'jobCounts' => $engine->jobCounts($start, $end),
            'totalDebt' => $engine->totalDebt(),
        ]);

        return $pdf->stream('management-report-'.$end->format('Y-m-d').'.pdf');
    }

    // Whatever date range is selected on the Reports page (capped at one
    // year by HasDateRangeFilter on the way in, and again here in case the
    // URL is hand-edited), broken down month by month so the trend across
    // the range is visible, not just its total.
    public function annualManagementReportPdf(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request);

        $engine = app(ReportEngine::class);

        $months = collect();
        $cursor = $start->copy()->startOfMonth();

        while ($cursor->lte($end)) {
            $monthStart = $cursor->copy();
            $monthEnd = $cursor->copy()->endOfMonth();

            // Clip the first/last month to the actual selected range, so a
            // range starting or ending mid-month doesn't report days
            // outside what was picked.
            $clippedStart = $monthStart->lt($start) ? $start->copy() : $monthStart;
            $clippedEnd = $monthEnd->gt($end) ? $end->copy() : $monthEnd;

            $months->push([
                'label' => $cursor->format('M Y'),
                'summary' => $engine->periodSummary($clippedStart, $clippedEnd),
            ]);

            $cursor->addMonthNoOverflow()->startOfMonth();
        }

        $periodLabel = $start->format('d M Y').' – '.$end->format('d M Y');

        $pdf = Pdf::loadView('pdf.annual-management-report', [
            'periodLabel' => $periodLabel,
            'months' => $months,
            'summary' => $engine->periodSummary($start, $end),
            'jobCounts' => $engine->jobCounts($start, $end),
            'totalDebt' => $engine->totalDebt(),
        ]);

        return $pdf->stream('annual-report-'.$start->format('Y-m-d').'-to-'.$end->format('Y-m-d').'.pdf');
    }

    // Every invoice ever recorded — deliberately ignores any date range,
    // unlike the two reports above. A full-history customer/job/invoice
    // profit sheet a GM can filter or pivot on their own in Excel.
    public function customerProfitExcel()
    {
        return Excel::download(new CustomerProfitExport, 'customer-profit-all-time.xlsx');
    }

    /**
     * "from"/"to" (Y-m-d) query params, defaulting to the current month —
     * same one-year cap as the on-screen date filter (HasDateRangeFilter),
     * so a hand-edited URL can't trigger an unbounded multi-year query.
     */
    private function resolveDateRange(Request $request): array
    {
        $defaultStart = now('Asia/Colombo')->startOfMonth();

        $start = $request->query('from')
            ? Carbon::parse($request->query('from'), 'Asia/Colombo')->startOfDay()
            : $defaultStart->copy()->startOfDay();

        $end = $request->query('to')
            ? Carbon::parse($request->query('to'), 'Asia/Colombo')->endOfDay()
            : now('Asia/Colombo')->endOfDay();

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        if ($start->diffInDays($end) > 365) {
            $end = $start->copy()->addDays(365)->endOfDay();
        }

        return [$start, $end];
    }
}
