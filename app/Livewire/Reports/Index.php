<?php

namespace App\Livewire\Reports;

use App\Livewire\Concerns\HasDateRangeFilter;
use App\Models\Expense;
use App\Services\Receivables;
use App\Services\ReportEngine;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Reports')]
class Index extends Component
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

        $expensesByCategory = Expense::with('category')
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn ($e) => $e->category->group)
            ->map(fn ($rows) => $rows->groupBy(fn ($e) => $e->category->name)->map(fn ($r) => $r->sum('amount')));

        return view('livewire.reports.index', [
            'ageing' => app(Receivables::class)->ageing(),
            'topCustomers' => app(ReportEngine::class)->topCustomersByProfit($start, $end, 50),
            'expensesByCategory' => $expensesByCategory,
        ]);
    }
}
