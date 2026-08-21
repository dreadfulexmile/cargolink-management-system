<?php

namespace App\Livewire\DirectorAccount;

use App\Models\DirectorTransaction;
use App\Services\ReportEngine;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Director A/C')]
class Index extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $txn_date = '';

    public string $description = '';

    public string $debit = '0';

    public string $credit = '0';

    public function mount(): void
    {
        $this->txn_date = now('Asia/Colombo')->format('Y-m-d');
    }

    public function create(): void
    {
        $this->reset(['editingId', 'description', 'debit', 'credit']);
        $this->debit = '0';
        $this->credit = '0';
        $this->txn_date = now('Asia/Colombo')->format('Y-m-d');
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $txn = DirectorTransaction::findOrFail($id);
        $this->editingId = $txn->id;
        $this->txn_date = $txn->txn_date->format('Y-m-d');
        $this->description = $txn->description;
        $this->debit = (string) $txn->debit;
        $this->credit = (string) $txn->credit;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'txn_date' => 'required|date',
            'description' => 'required|string|max:255',
            'debit' => 'required|numeric|min:0',
            'credit' => 'required|numeric|min:0',
        ]);

        if ($this->editingId) {
            DirectorTransaction::findOrFail($this->editingId)->update($data);
        } else {
            DirectorTransaction::create($data);
        }

        $this->showForm = false;
        $this->reset(['editingId']);
    }

    public function delete(int $id): void
    {
        DirectorTransaction::findOrFail($id)->delete();
    }

    public function render()
    {
        $start = now('Asia/Colombo')->startOfMonth();
        $end = now('Asia/Colombo');
        $summary = app(ReportEngine::class)->periodSummary($start, $end);

        $transactions = DirectorTransaction::latest('txn_date')->paginate(20);

        return view('livewire.director-account.index', [
            'transactions' => $transactions,
            'profitAfterLeases' => $summary['profit_after_leases'],
            'totalDebit' => (string) DirectorTransaction::sum('debit'),
            'totalCredit' => (string) DirectorTransaction::sum('credit'),
        ]);
    }
}
