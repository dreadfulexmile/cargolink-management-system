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
    public function customerProfitExcel(Request $request)
    {
        $month = $request->query('month', now('Asia/Colombo')->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $month, 'Asia/Colombo')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return Excel::download(new CustomerProfitExport($start, $end), "customer-profit-{$month}.xlsx");
    }

    public function managementReportPdf(Request $request)
    {
        $month = $request->query('month', now('Asia/Colombo')->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $month, 'Asia/Colombo')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $engine = app(ReportEngine::class);

        $pdf = Pdf::loadView('pdf.management-report', [
            'month' => $start->format('F Y'),
            'summary' => $engine->periodSummary($start, $end),
            'jobCounts' => $engine->jobCounts($start, $end),
            'totalDebt' => $engine->totalDebt(),
        ]);

        return $pdf->stream("management-report-{$month}.pdf");
    }
}
