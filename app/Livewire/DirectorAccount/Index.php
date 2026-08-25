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

    // Which month to post final earnings for — defaults to last month, since
    // the current month's figure is still moving and isn't "final" yet.
    public string $postMonth = '';

    public ?string $postError = null;

    public function mount(): void
    {
        $this->txn_date = now('Asia/Colombo')->format('Y-m-d');
        $this->postMonth = now('Asia/Colombo')->subMonthNoOverflow()->format('Y-m');
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

    private function postMonthLabel(): string
    {
        return Carbon::createFromFormat('Y-m', $this->postMonth, 'Asia/Colombo')->format('F Y');
    }

    private function postMonthDescription(): string
    {
        return 'Final Earnings — '.$this->postMonthLabel();
    }

    /**
     * The final company profit for the selected month — what "Post to
     * Ledger" would actually record, shown before they commit to it.
     */
    public function getPostPreviewProperty(): ?string
    {
        if (! $this->postMonth) {
            return null;
        }

        $start = Carbon::createFromFormat('Y-m', $this->postMonth, 'Asia/Colombo')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return app(ReportEngine::class)->periodSummary($start, $end)['final_company_profit'];
    }

    public function getAlreadyPostedProperty(): bool
    {
        return $this->postMonth && DirectorTransaction::where('description', $this->postMonthDescription())->exists();
    }

    /**
     * Posts a month's final company profit into the ledger as a real
     * transaction — a credit if it was a profit, a debit if the month was a
     * loss — so the director's running balance actually reflects what the
     * company earned, not just a live figure that resets every page load.
     * Guarded against posting the same month twice.
     */
    public function postFinalEarnings(): void
    {
        $this->postError = null;

        $this->validate(['postMonth' => 'required|date_format:Y-m']);

        if ($this->alreadyPosted) {
            $this->postError = 'Final earnings for '.$this->postMonthLabel().' have already been posted.';

            return;
        }

        $amount = $this->postPreview;

        if (bccomp($amount, '0', 2) === 0) {
            $this->postError = 'Final earnings for '.$this->postMonthLabel().' are exactly zero — nothing to post.';

            return;
        }

        $start = Carbon::createFromFormat('Y-m', $this->postMonth, 'Asia/Colombo')->startOfMonth();
        $isProfit = bccomp($amount, '0', 2) > 0;

        DirectorTransaction::create([
            'txn_date' => $start->copy()->endOfMonth()->format('Y-m-d'),
            'description' => $this->postMonthDescription(),
            'credit' => $isProfit ? $amount : '0',
            'debit' => $isProfit ? '0' : bcmul($amount, '-1', 2),
        ]);

        $this->postMonth = $start->copy()->addMonthNoOverflow()->format('Y-m');
    }

    public function render()
    {
        $start = now('Asia/Colombo')->startOfMonth();
        $end = now('Asia/Colombo');
        $summary = app(ReportEngine::class)->periodSummary($start, $end);

        $transactions = DirectorTransaction::latest('txn_date')->paginate(20);

        $postedMonths = DirectorTransaction::where('description', 'like', 'Final Earnings — %')
            ->orderByDesc('txn_date')
            ->limit(12)
            ->pluck('description')
            ->map(fn ($d) => str_replace('Final Earnings — ', '', $d));

        return view('livewire.director-account.index', [
            'transactions' => $transactions,
            'finalCompanyProfitMtd' => $summary['final_company_profit'],
            'totalDebit' => (string) DirectorTransaction::sum('debit'),
            'totalCredit' => (string) DirectorTransaction::sum('credit'),
            'postedMonths' => $postedMonths,
        ]);
    }
}
