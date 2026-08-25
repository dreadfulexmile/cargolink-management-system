<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Receipt;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Records one lump sum a customer paid and splits it across whichever of
 * their invoices it covers — see Receipt for why this exists separately
 * from recording a payment directly on a single invoice.
 */
#[Layout('layouts.app')]
#[Title('Receive Payment')]
class ReceivePayment extends Component
{
    public Customer $customer;

    public string $amount = '';

    public string $method = 'cheque';

    public string $reference = '';

    public string $received_on = '';

    /** @var array<int, string> invoice_id => allocated amount (string, may be blank) */
    public array $allocations = [];

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;
        $this->received_on = now('Asia/Colombo')->format('Y-m-d');
        $this->resetAllocationsForOpenInvoices();
    }

    private function openInvoices()
    {
        return $this->customer->invoices()
            ->where('balance_due', '>', 0)
            ->orderBy('invoice_date')
            ->get();
    }

    private function resetAllocationsForOpenInvoices(): void
    {
        $this->allocations = $this->openInvoices()
            ->mapWithKeys(fn (Invoice $invoice) => [$invoice->id => ''])
            ->all();
    }

    /** Fills allocations oldest-invoice-first until the amount received runs out. */
    public function autoAllocate(): void
    {
        $this->validateOnly('amount', ['amount' => 'required|numeric|min:0.01']);

        $remaining = (string) $this->amount;

        foreach ($this->openInvoices() as $invoice) {
            if (bccomp($remaining, '0', 2) <= 0) {
                $this->allocations[$invoice->id] = '';

                continue;
            }

            $share = bccomp($remaining, (string) $invoice->balance_due, 2) >= 0
                ? (string) $invoice->balance_due
                : $remaining;

            $this->allocations[$invoice->id] = $share;
            $remaining = bcsub($remaining, $share, 2);
        }
    }

    public function allocatedTotal(): string
    {
        return array_reduce(
            $this->allocations,
            fn (string $carry, string $value) => bcadd($carry, $value !== '' ? $value : '0.00', 2),
            '0.00'
        );
    }

    public function remainingToAllocate(): string
    {
        return bcsub($this->amount !== '' ? $this->amount : '0.00', $this->allocatedTotal(), 2);
    }

    public function save(): void
    {
        $data = $this->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cheque,bank,cash',
            'reference' => 'nullable|string|max:255',
            'received_on' => 'required|date',
        ]);

        $invoices = $this->openInvoices()->keyBy('id');

        $shares = [];
        foreach ($this->allocations as $invoiceId => $value) {
            if ($value === '' || bccomp($value, '0', 2) <= 0) {
                continue;
            }

            $invoice = $invoices->get($invoiceId);

            if (! $invoice) {
                continue;
            }

            if (bccomp($value, (string) $invoice->balance_due, 2) > 0) {
                $this->addError('allocations', "Allocation for {$invoice->invoice_no} can't exceed its balance due.");

                return;
            }

            $shares[$invoiceId] = $value;
        }

        if (empty($shares)) {
            $this->addError('allocations', 'Allocate the amount to at least one invoice.');

            return;
        }

        $allocatedTotal = array_reduce($shares, fn (string $carry, string $value) => bcadd($carry, $value, 2), '0.00');

        if (bccomp($allocatedTotal, $data['amount'], 2) !== 0) {
            $this->addError('allocations', 'The amounts allocated to invoices must add up to exactly the amount received.');

            return;
        }

        DB::transaction(function () use ($data, $shares, $invoices) {
            $receipt = Receipt::create([
                'customer_id' => $this->customer->id,
                'amount' => $data['amount'],
                'method' => $data['method'],
                'reference' => $data['reference'] ?: null,
                'received_on' => $data['received_on'],
            ]);

            foreach ($shares as $invoiceId => $value) {
                $invoices[$invoiceId]->payments()->create([
                    'receipt_id' => $receipt->id,
                    'amount' => $value,
                    'method' => $data['method'],
                    'reference' => $data['reference'] ?: null,
                    'paid_on' => $data['received_on'],
                ]);

                $invoices[$invoiceId]->recalculate();
            }
        });

        $this->reset(['amount', 'reference']);
        $this->method = 'cheque';
        $this->received_on = now('Asia/Colombo')->format('Y-m-d');
        $this->resetAllocationsForOpenInvoices();
        $this->customer->refresh();
    }

    public function deleteReceipt(int $id): void
    {
        $receipt = $this->customer->receipts()->with('payments')->findOrFail($id);
        $invoiceIds = $receipt->payments->pluck('invoice_id')->all();

        $receipt->delete();

        Invoice::whereKey($invoiceIds)->get()->each->recalculate();

        $this->resetAllocationsForOpenInvoices();
        $this->customer->refresh();
    }

    public function render()
    {
        return view('livewire.customers.receive-payment', [
            'openInvoices' => $this->openInvoices(),
            'receipts' => $this->customer->receipts()->with('payments.invoice')->latest('received_on')->get(),
        ]);
    }
}
