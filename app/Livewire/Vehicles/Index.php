<?php

namespace App\Livewire\Vehicles;

use App\Models\LeasePayment;
use App\Models\Vehicle;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Vehicles & Leases')]
class Index extends Component
{
    use WithPagination;

    public bool $showVehicleForm = false;

    public ?int $editingVehicleId = null;

    public string $reg_no = '';

    public string $monthly_rental = '';

    public string $lease_due_day = '';

    public string $lease_term_months = '';

    public ?int $expandedVehicleId = null;

    public ?int $payingVehicleId = null;

    public ?int $editingLeasePaymentId = null;

    public string $lease_period = '';

    public string $lease_due_date = '';

    public string $lease_amount = '';

    public string $lease_paid_on = '';

    public function mount(): void
    {
        $this->lease_period = now('Asia/Colombo')->format('Y-m');
        $this->lease_paid_on = now('Asia/Colombo')->format('Y-m-d');
    }

    public function createVehicle(): void
    {
        $this->reset(['editingVehicleId', 'reg_no', 'monthly_rental', 'lease_due_day', 'lease_term_months']);
        $this->showVehicleForm = true;
    }

    public function editVehicle(int $id): void
    {
        $vehicle = Vehicle::findOrFail($id);
        $this->editingVehicleId = $vehicle->id;
        $this->reg_no = $vehicle->reg_no;
        $this->monthly_rental = $vehicle->monthly_rental !== null ? (string) $vehicle->monthly_rental : '';
        $this->lease_due_day = $vehicle->lease_due_day !== null ? (string) $vehicle->lease_due_day : '';
        $this->lease_term_months = $vehicle->lease_term_months !== null ? (string) $vehicle->lease_term_months : '';
        $this->showVehicleForm = true;
    }

    public function saveVehicle(): void
    {
        $data = $this->validate([
            'reg_no' => 'required|string|max:255|unique:vehicles,reg_no,'.($this->editingVehicleId ?? 'NULL'),
            'monthly_rental' => 'nullable|numeric|min:0',
            'lease_due_day' => 'nullable|integer|min:1|max:28',
            'lease_term_months' => 'nullable|integer|min:1|max:120',
        ]);

        // 'nullable' only skips the other rules for blanks — it doesn't turn '' into null,
        // so empty strings must be normalized before hitting the DB (tinyint/decimal reject '').
        $data['monthly_rental'] = $data['monthly_rental'] !== '' ? $data['monthly_rental'] : null;
        $data['lease_due_day'] = $data['lease_due_day'] !== '' ? $data['lease_due_day'] : null;
        $data['lease_term_months'] = $data['lease_term_months'] !== '' ? $data['lease_term_months'] : null;

        // A vehicle only carries lease payments if a leasing/finance amount was entered.
        $data['has_lease'] = (float) ($data['monthly_rental'] ?? 0) > 0;

        if ($this->editingVehicleId) {
            $vehicle = Vehicle::findOrFail($this->editingVehicleId);
            $vehicle->update($data);
        } else {
            $vehicle = Vehicle::create($data);
        }

        // Top up the monthly schedule to cover the full term. Runs on every save, not just
        // creation, so raising the term later (renewing/extending a lease) schedules the
        // extra months too — it only ever adds what's missing, never touches existing rows.
        if ($vehicle->has_lease && $vehicle->lease_term_months) {
            $this->generateLeaseSchedule($vehicle);
        }

        $this->showVehicleForm = false;
        $this->reset(['editingVehicleId', 'reg_no', 'monthly_rental', 'lease_due_day', 'lease_term_months']);
    }

    private function generateLeaseSchedule(Vehicle $vehicle): void
    {
        $alreadyScheduled = $vehicle->leasePayments()->count();
        $remaining = $vehicle->lease_term_months - $alreadyScheduled;

        if ($remaining <= 0) {
            return;
        }

        $dueDay = $vehicle->lease_due_day ?? 1;

        // Continue from the month after the last scheduled period, or from this month if
        // nothing has been scheduled yet — so extending a term never duplicates past rows.
        $lastPeriod = $vehicle->leasePayments()->max('period');
        $start = $lastPeriod
            ? Carbon::parse($lastPeriod, 'Asia/Colombo')->addMonthNoOverflow()->startOfMonth()
            : now('Asia/Colombo')->startOfMonth();

        for ($i = 0; $i < $remaining; $i++) {
            $period = $start->copy()->addMonths($i);

            LeasePayment::create([
                'vehicle_id' => $vehicle->id,
                'period' => $period->format('Y-m-01'),
                'due_date' => $period->copy()->addDays($dueDay - 1)->format('Y-m-d'),
                'amount' => $vehicle->monthly_rental,
                'paid_on' => null,
            ]);
        }
    }

    public function toggleActive(int $id): void
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->update(['is_active' => ! $vehicle->is_active]);
    }

    public function toggleVehicleDetails(int $id): void
    {
        $this->expandedVehicleId = $this->expandedVehicleId === $id ? null : $id;
    }

    /**
     * Once every scheduled lease payment for a vehicle has been paid, the lease is
     * complete, so the vehicle is auto-labeled deactivated instead of requiring a manual click.
     */
    private function maybeDeactivateFullyPaidVehicle(Vehicle $vehicle): void
    {
        if (! $vehicle->is_active || ! $vehicle->lease_term_months) {
            return;
        }

        $paidCount = $vehicle->leasePayments()->whereNotNull('paid_on')->count();

        if ($paidCount >= $vehicle->lease_term_months) {
            $vehicle->update(['is_active' => false]);
        }
    }

    public function payLease(int $vehicleId): void
    {
        $vehicle = Vehicle::findOrFail($vehicleId);
        $this->payingVehicleId = $vehicleId;
        $this->editingLeasePaymentId = null;
        $this->lease_amount = (string) $vehicle->monthly_rental;
        $this->lease_period = now('Asia/Colombo')->format('Y-m');
        $this->lease_paid_on = '';

        $dueDay = $vehicle->lease_due_day ?? 1;
        $this->lease_due_date = now('Asia/Colombo')->startOfMonth()->addDays($dueDay - 1)->format('Y-m-d');
    }

    public function editLeasePayment(int $leasePaymentId): void
    {
        $payment = LeasePayment::findOrFail($leasePaymentId);
        $this->payingVehicleId = $payment->vehicle_id;
        $this->editingLeasePaymentId = $payment->id;
        $this->lease_period = $payment->period->format('Y-m');
        $this->lease_due_date = $payment->due_date?->format('Y-m-d') ?? '';
        $this->lease_amount = (string) $payment->amount;
        $this->lease_paid_on = $payment->paid_on?->format('Y-m-d') ?? '';
    }

    public function saveLeasePayment(): void
    {
        $this->validate([
            'lease_period' => 'required|date_format:Y-m',
            'lease_due_date' => 'required|date',
            'lease_amount' => 'required|numeric|min:0.01',
            'lease_paid_on' => 'nullable|date',
        ]);

        $data = [
            'vehicle_id' => $this->payingVehicleId,
            'period' => $this->lease_period.'-01',
            'due_date' => $this->lease_due_date,
            'amount' => $this->lease_amount,
            'paid_on' => $this->lease_paid_on ?: null,
        ];

        if ($this->editingLeasePaymentId) {
            $payment = LeasePayment::findOrFail($this->editingLeasePaymentId);
            $payment->update($data);
        } else {
            $payment = LeasePayment::create($data);
        }

        $this->maybeDeactivateFullyPaidVehicle($payment->vehicle);

        $this->payingVehicleId = null;
        $this->editingLeasePaymentId = null;
        $this->reset(['lease_amount']);
    }

    public function cancelLeasePayment(): void
    {
        $this->payingVehicleId = null;
        $this->editingLeasePaymentId = null;
    }

    public function markLeasePaymentPaid(int $leasePaymentId): void
    {
        $payment = LeasePayment::findOrFail($leasePaymentId);
        $payment->update(['paid_on' => now('Asia/Colombo')->format('Y-m-d')]);

        $this->maybeDeactivateFullyPaidVehicle($payment->vehicle);
    }

    public function render()
    {
        return view('livewire.vehicles.index', [
            'vehicles' => Vehicle::with(['leasePayments' => fn ($q) => $q->latest('period')->limit(6)])
                ->withCount(['leasePayments as paid_lease_payments_count' => fn ($q) => $q->whereNotNull('paid_on')])
                ->orderBy('reg_no')->paginate(15),
        ]);
    }
}
