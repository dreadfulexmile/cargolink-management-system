<?php

namespace App\Livewire\Creditors;

use App\Models\Creditor;
use App\Models\CreditorPayment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Creditors')]
class Index extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $type = 'individual';

    public string $outstanding = '0';

    public string $note = '';

    public string $monthly_repayment = '';

    public string $repayment_due_day = '';

    public string $repayment_term_months = '';

    public ?int $expandedCreditorId = null;

    public ?int $payingCreditorId = null;

    public ?int $editingPaymentId = null;

    public string $payment_period = '';

    public string $payment_due_date = '';

    public string $payment_amount = '';

    public string $payment_paid_on = '';

    public function mount(): void
    {
        $this->payment_period = now('Asia/Colombo')->format('Y-m');
        $this->payment_paid_on = now('Asia/Colombo')->format('Y-m-d');
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $creditor = Creditor::findOrFail($id);
        $this->editingId = $creditor->id;
        $this->name = $creditor->name;
        $this->type = $creditor->type;
        $this->outstanding = (string) $creditor->outstanding;
        $this->note = (string) $creditor->note;
        $this->monthly_repayment = $creditor->monthly_repayment !== null ? (string) $creditor->monthly_repayment : '';
        $this->repayment_due_day = $creditor->repayment_due_day !== null ? (string) $creditor->repayment_due_day : '';
        $this->repayment_term_months = $creditor->repayment_term_months !== null ? (string) $creditor->repayment_term_months : '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:individual,bank_facility,gold_loan',
            'outstanding' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:500',
            'monthly_repayment' => 'nullable|numeric|min:0',
            'repayment_due_day' => 'nullable|integer|min:1|max:28',
            'repayment_term_months' => 'nullable|integer|min:1|max:600',
        ]);

        $data['monthly_repayment'] = $data['monthly_repayment'] !== '' ? $data['monthly_repayment'] : null;
        $data['repayment_due_day'] = $data['repayment_due_day'] !== '' ? $data['repayment_due_day'] : null;
        $data['repayment_term_months'] = $data['repayment_term_months'] !== '' ? $data['repayment_term_months'] : null;

        if ($this->editingId) {
            $creditor = Creditor::findOrFail($this->editingId);
            $creditor->update($data);
        } else {
            $creditor = Creditor::create($data);
        }

        // Build the full monthly schedule up front the first time a repayment term is set,
        // the same way vehicle leases work — never runs twice for the same creditor.
        if ($creditor->monthly_repayment && $creditor->repayment_term_months && $creditor->payments()->doesntExist()) {
            $this->generateRepaymentSchedule($creditor);
        }

        $this->showForm = false;
        $this->resetForm();
    }

    private function generateRepaymentSchedule(Creditor $creditor): void
    {
        $dueDay = $creditor->repayment_due_day ?? 1;
        $start = now('Asia/Colombo')->startOfMonth();

        for ($i = 0; $i < $creditor->repayment_term_months; $i++) {
            $period = $start->copy()->addMonths($i);

            CreditorPayment::create([
                'creditor_id' => $creditor->id,
                'period' => $period->format('Y-m-01'),
                'due_date' => $period->copy()->addDays($dueDay - 1)->format('Y-m-d'),
                'amount' => $creditor->monthly_repayment,
                'paid_on' => null,
            ]);
        }
    }

    public function delete(int $id): void
    {
        Creditor::findOrFail($id)->delete();
    }

    public function toggleCreditorDetails(int $id): void
    {
        $this->expandedCreditorId = $this->expandedCreditorId === $id ? null : $id;
    }

    public function payRepayment(int $creditorId): void
    {
        $creditor = Creditor::findOrFail($creditorId);
        $this->payingCreditorId = $creditorId;
        $this->editingPaymentId = null;
        $this->payment_amount = (string) $creditor->monthly_repayment;
        $this->payment_period = now('Asia/Colombo')->format('Y-m');
        $this->payment_paid_on = '';

        $dueDay = $creditor->repayment_due_day ?? 1;
        $this->payment_due_date = now('Asia/Colombo')->startOfMonth()->addDays($dueDay - 1)->format('Y-m-d');
    }

    public function editRepaymentPayment(int $paymentId): void
    {
        $payment = CreditorPayment::findOrFail($paymentId);
        $this->payingCreditorId = $payment->creditor_id;
        $this->editingPaymentId = $payment->id;
        $this->payment_period = $payment->period->format('Y-m');
        $this->payment_due_date = $payment->due_date?->format('Y-m-d') ?? '';
        $this->payment_amount = (string) $payment->amount;
        $this->payment_paid_on = $payment->paid_on?->format('Y-m-d') ?? '';
    }

    public function saveRepaymentPayment(): void
    {
        $this->validate([
            'payment_period' => 'required|date_format:Y-m',
            'payment_due_date' => 'required|date',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_paid_on' => 'nullable|date',
        ]);

        $previousPaidContribution = '0.00';

        if ($this->editingPaymentId) {
            $existing = CreditorPayment::findOrFail($this->editingPaymentId);
            $previousPaidContribution = $existing->paid_on ? (string) $existing->amount : '0.00';
        }

        $data = [
            'creditor_id' => $this->payingCreditorId,
            'period' => $this->payment_period.'-01',
            'due_date' => $this->payment_due_date,
            'amount' => $this->payment_amount,
            'paid_on' => $this->payment_paid_on ?: null,
        ];

        if ($this->editingPaymentId) {
            CreditorPayment::findOrFail($this->editingPaymentId)->update($data);
        } else {
            CreditorPayment::create($data);
        }

        $newPaidContribution = $data['paid_on'] ? (string) $data['amount'] : '0.00';
        $this->applyOutstandingDelta($this->payingCreditorId, $previousPaidContribution, $newPaidContribution);

        $this->payingCreditorId = null;
        $this->editingPaymentId = null;
        $this->reset(['payment_amount']);
    }

    public function cancelRepaymentPayment(): void
    {
        $this->payingCreditorId = null;
        $this->editingPaymentId = null;
    }

    public function markRepaymentPaid(int $paymentId): void
    {
        $payment = CreditorPayment::findOrFail($paymentId);
        $payment->update(['paid_on' => now('Asia/Colombo')->format('Y-m-d')]);

        $this->applyOutstandingDelta($payment->creditor_id, '0.00', (string) $payment->amount);
    }

    /**
     * Keeps the creditor's outstanding balance in step with actual repayments recorded,
     * so it doesn't have to be updated by hand every time a payment is marked paid.
     */
    private function applyOutstandingDelta(int $creditorId, string $previousPaidContribution, string $newPaidContribution): void
    {
        $delta = bcsub($newPaidContribution, $previousPaidContribution, 2);

        if (bccomp($delta, '0', 2) === 0) {
            return;
        }

        $creditor = Creditor::findOrFail($creditorId);
        $creditor->update(['outstanding' => max(bcsub((string) $creditor->outstanding, $delta, 2), '0.00')]);
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'outstanding', 'note', 'monthly_repayment', 'repayment_due_day', 'repayment_term_months']);
        $this->type = 'individual';
        $this->outstanding = '0';
        $this->resetValidation();
    }

    public function render()
    {
        $creditors = Creditor::with(['payments' => fn ($q) => $q->latest('period')->limit(6)])
            ->withCount(['payments as paid_payments_count' => fn ($q) => $q->whereNotNull('paid_on')])
            ->orderByDesc('outstanding')
            ->paginate(15);

        return view('livewire.creditors.index', [
            'creditors' => $creditors,
            'totalDebt' => (string) Creditor::sum('outstanding'),
        ]);
    }
}
