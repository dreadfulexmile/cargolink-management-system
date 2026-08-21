<?php

namespace App\Livewire\Invoices;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\JobCostLine;
use App\Services\InvoiceNumberGenerator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Invoices')]
class Index extends Component
{
    use WithPagination;

    public ?int $generateFromJobId = null;

    public string $invoice_date = '';

    public array $draftLines = [];

    public ?string $filterStatus = null;

    public ?string $filterCustomer = null;

    public function mount(): void
    {
        $jobId = request()->query('job');

        if ($jobId) {
            $job = Job::with('costLines.chargeType')->find($jobId);
            if ($job && ! $job->invoice) {
                $this->generateFromJobId = $job->id;
                $this->invoice_date = now('Asia/Colombo')->format('Y-m-d');
                $this->draftLines = $job->costLines->map(fn ($line) => [
                    'job_cost_line_id' => $line->id,
                    'description' => $line->displayDescription(),
                    'kind' => $line->kind,
                    'amount' => (string) $line->amount,
                ])->values()->all();
            }
        }
    }

    public function addDraftLine(): void
    {
        $this->draftLines[] = ['job_cost_line_id' => null, 'description' => '', 'kind' => 'service', 'amount' => ''];
    }

    public function removeDraftLine(int $index): void
    {
        unset($this->draftLines[$index]);
        $this->draftLines = array_values($this->draftLines);
    }

    public function generate(): void
    {
        $job = Job::find($this->generateFromJobId);

        if (! $job || $job->invoice) {
            $this->generateFromJobId = null;

            return;
        }

        $this->validate([
            'invoice_date' => 'required|date',
            'draftLines' => 'required|array|min:1',
            'draftLines.*.description' => 'required|string|max:255',
            'draftLines.*.kind' => 'required|in:disbursement,service',
            'draftLines.*.amount' => 'required|numeric|min:0.01',
        ]);

        $advanceTotal = $job->advances()->where('type', 'advance')->sum('amount');
        $subtotal = array_reduce($this->draftLines, fn ($carry, $line) => bcadd($carry, (string) $line['amount'], 2), '0.00');

        $invoice = Invoice::create([
            'invoice_no' => app(InvoiceNumberGenerator::class)->next(),
            'job_id' => $job->id,
            'customer_id' => $job->customer_id,
            'invoice_date' => $this->invoice_date,
            'subtotal' => $subtotal,
            'advance_total' => $advanceTotal,
            'balance_due' => bcsub($subtotal, (string) $advanceTotal, 2),
            'status' => 'unpaid',
        ]);

        foreach ($this->draftLines as $line) {
            // Whatever the GM finalizes here becomes the job's authoritative cost line too —
            // covers both a pre-existing line they tweaked and a brand new one added on this screen.
            if (! empty($line['job_cost_line_id'])) {
                $costLine = JobCostLine::find($line['job_cost_line_id']);
                $costLine->update([
                    'kind' => $line['kind'],
                    'description' => $line['description'],
                    'amount' => $line['amount'],
                ]);
            } else {
                $costLine = $job->costLines()->create([
                    'kind' => $line['kind'],
                    'description' => $line['description'],
                    'amount' => $line['amount'],
                ]);
            }

            $invoice->lines()->create([
                'job_cost_line_id' => $costLine->id,
                'description' => $line['description'],
                'kind' => $line['kind'],
                'amount' => $line['amount'],
            ]);
        }

        $job->update(['status' => 'invoiced']);

        $this->redirect(route('invoices.show', $invoice), navigate: true);
    }

    public function cancelGenerate(): void
    {
        $this->generateFromJobId = null;
        $this->draftLines = [];
    }

    public function render()
    {
        $job = $this->generateFromJobId ? Job::with('customer', 'costLines')->find($this->generateFromJobId) : null;

        $invoices = Invoice::query()
            ->with('customer', 'job')
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterCustomer, fn ($q) => $q->where('customer_id', $this->filterCustomer))
            ->latest('invoice_date')
            ->paginate(15);

        return view('livewire.invoices.index', [
            'invoices' => $invoices,
            'job' => $job,
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }
}
