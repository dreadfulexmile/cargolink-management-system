<?php

namespace App\Livewire;

use App\Livewire\Concerns\HasDateRangeFilter;
use App\Models\DirectorTransaction;
use App\Models\Job;
use App\Services\Receivables;
use App\Services\ReportEngine;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    use HasDateRangeFilter;

    public function mount(): void
    {
        $this->initDateRange();
    }

    public function render()
    {
        $start = $this->dateRangeStart();
        $end = $this->dateRangeEnd();

        $engine = app(ReportEngine::class);
        $receivables = app(Receivables::class);

        $summary = $engine->periodSummary($start, $end);
        $jobCounts = $engine->jobCounts($start, $end);

        $drawings = (string) DirectorTransaction::whereBetween('txn_date', [$start->toDateString(), $end->toDateString()])->sum('debit');
        $excessDrawings = bccomp($drawings, $summary['final_company_profit'], 2) > 0
            ? bcsub($drawings, $summary['final_company_profit'], 2)
            : '0.00';

        $recentJobs = Job::with('customer')
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->limit(6)
            ->get();

        return view('livewire.dashboard', [
            'summary' => $summary,
            'jobCounts' => $jobCounts,
            'recentJobs' => $recentJobs,
            'totalDebt' => $engine->totalDebt(),
            'totalOutstanding' => $receivables->totalOutstanding(),
            'totalOverdue' => $receivables->totalOverdue(),
            'drawings' => $drawings,
            'excessDrawings' => $excessDrawings,
        ]);
    }
}
