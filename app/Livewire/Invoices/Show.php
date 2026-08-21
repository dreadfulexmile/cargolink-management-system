<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\JobCostLine;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public Invoice $invoice;

    public bool $editingDate = false;

    public string $invoice_date = '';

    public ?int $editingLineId = null;

    public string $line_description = '';

    public string $line_kind = 'service';

    public string $line_amount = '';

    public ?int $editingPaymentId = null;

    public string $payment_amount = '';

    public string $payment_method = 'cheque';

    public string $payment_reference = '';

    public string $payment_paid_on = '';

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice;
        $this->payment_paid_on = now('Asia/Colombo')->format('Y-m-d');
    }

    public function editDate(): void
    {
        $this->invoice_date = $this->invoice->invoice_date->format('Y-m-d');
        $this->editingDate = true;
    }

    public function saveDate(): void
    {
        $data = $this->validate(['invoice_date' => 'required|date']);

        $this->invoice->update($data);
        $this->invoice->refresh();
        $this->editingDate = false;
    }

    public function cancelDate(): void
    {
        $this->editingDate = false;
    }

    public function editLine(int $id): void
    {
        $line = $this->invoice->lines()->findOrFail($id);
        $this->editingLineId = $line->id;
        $this->line_description = $line->description;
        $this->line_kind = $line->kind;
        $this->line_amount = (string) $line->amount;
    }

    public function saveLine(): void
    {
        $data = $this->validate([
            'line_description' => 'required|string|max:255',
            'line_kind' => 'required|in:disbursement,service',
            'line_amount' => 'required|numeric|min:0.01',
        ]);

        $lineData = [
            'description' => $data['line_description'],
            'kind' => $data['line_kind'],
            'amount' => $data['line_amount'],
        ];

        if ($this->editingLineId) {
            $line = $this->invoice->lines()->findOrFail($this->editingLineId);
            $line->update($lineData);
        } else {
            $line = $this->invoice->lines()->create($lineData);
        }

        $this->syncJobCostLine($line);

        $this->reset(['editingLineId', 'line_description', 'line_amount']);
        $this->line_kind = 'service';

        $this->invoice->recalculate();
    }

    public function cancelLine(): void
    {
        $this->reset(['editingLineId', 'line_description', 'line_amount']);
        $this->line_kind = 'service';
    }

    public function removeLine(int $id): void
    {
        $line = $this->invoice->lines()->findOrFail($id);

        if ($line->job_cost_line_id) {
            // Cascades and removes this invoice line along with it.
            JobCostLine::whereKey($line->job_cost_line_id)->delete();
        } else {
            $line->delete();
        }

        $this->invoice->recalculate();
    }

    /**
     * Keeps the job's costing in step with a line edited directly on the invoice —
     * everything ultimately lives on the job, this just mirrors the edit back onto it.
     */
    private function syncJobCostLine(InvoiceLine $line): void
    {
        if ($line->job_cost_line_id) {
            JobCostLine::whereKey($line->job_cost_line_id)->update([
                'kind' => $line->kind,
                'description' => $line->description,
                'amount' => $line->amount,
            ]);

            return;
        }

        $costLine = $this->invoice->job->costLines()->create([
            'kind' => $line->kind,
            'description' => $line->description,
            'amount' => $line->amount,
        ]);

        $line->update(['job_cost_line_id' => $costLine->id]);
    }

    public function editPayment(int $id): void
    {
        $payment = $this->invoice->payments()->findOrFail($id);
        $this->editingPaymentId = $payment->id;
        $this->payment_amount = (string) $payment->amount;
        $this->payment_method = $payment->method;
        $this->payment_reference = (string) $payment->reference;
        $this->payment_paid_on = $payment->paid_on->format('Y-m-d');
    }

    public function savePayment(): void
    {
        $data = $this->validate([
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cheque,bank,cash',
            'payment_reference' => 'nullable|string|max:255',
            'payment_paid_on' => 'required|date',
        ]);

        $paymentData = [
            'amount' => $data['payment_amount'],
            'method' => $data['payment_method'],
            'reference' => $data['payment_reference'] ?: null,
            'paid_on' => $data['payment_paid_on'],
        ];

        if ($this->editingPaymentId) {
            $this->invoice->payments()->whereKey($this->editingPaymentId)->update($paymentData);
        } else {
            $this->invoice->payments()->create($paymentData);
        }

        $this->reset(['editingPaymentId', 'payment_amount', 'payment_reference']);
        $this->payment_method = 'cheque';
        $this->payment_paid_on = now('Asia/Colombo')->format('Y-m-d');

        $this->invoice->recalculate();
    }

    public function cancelPayment(): void
    {
        $this->reset(['editingPaymentId', 'payment_amount', 'payment_reference']);
        $this->payment_method = 'cheque';
        $this->payment_paid_on = now('Asia/Colombo')->format('Y-m-d');
    }

    public function removePayment(int $id): void
    {
        $this->invoice->payments()->whereKey($id)->delete();
        $this->invoice->recalculate();
    }

    public function render()
    {
        return view('livewire.invoices.show', [
            'lines' => $this->invoice->lines,
            'payments' => $this->invoice->payments()->latest('paid_on')->get(),
        ])->title('Invoice '.$this->invoice->invoice_no);
    }
}
