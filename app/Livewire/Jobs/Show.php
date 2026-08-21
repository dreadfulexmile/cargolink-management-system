<?php

namespace App\Livewire\Jobs;

use App\Models\ChargeType;
use App\Models\Customer;
use App\Models\Job;
use App\Models\JobServiceCost;
use App\Services\JobCosting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public Job $job;

    public bool $showJobEditForm = false;

    public int $customer_id = 0;

    public string $mode = 'sea';

    public string $direction = 'import';

    public string $vessel_flight = '';

    public ?string $vessel_date = null;

    public string $port_loading = '';

    public string $port_discharge = '';

    public string $mbl_no = '';

    public string $hbl_no = '';

    public string $cargo_description = '';

    public string $container_no = '';

    public string $quantity = '';

    public string $cusdec_no = '';

    public string $customer_incentive = '0';

    public string $job_commission = '0';

    public string $remarks = '';

    public ?int $editingCostLineId = null;

    public ?int $charge_type_id = null;

    public string $cost_description = '';

    public string $cost_amount = '';

    public bool $showNewChargeTypeForm = false;

    public string $new_charge_type_name = '';

    public string $new_charge_type_kind = 'disbursement';

    // Internal service cost form — the actual amount paid out (e.g. to a subcontractor) to
    // deliver a service line. Never mirrored onto the invoice; see JobServiceCost.
    public ?int $editingServiceCostId = null;

    public ?int $service_cost_charge_type_id = null;

    public string $service_cost_paid_to = '';

    public string $service_cost_description = '';

    public string $service_cost_amount = '';

    public ?int $editingAdvanceId = null;

    public string $advance_type = 'advance';

    public string $advance_amount = '';

    public string $advance_receipt_no = '';

    public string $advance_name = '';

    public string $advance_received_on = '';

    public function mount(Job $job): void
    {
        $this->job = $job;
        $this->advance_received_on = now('Asia/Colombo')->format('Y-m-d');
    }

    public function editJobDetails(): void
    {
        $this->customer_id = $this->job->customer_id;
        $this->mode = $this->job->mode;
        $this->direction = $this->job->direction;
        $this->vessel_flight = (string) $this->job->vessel_flight;
        $this->vessel_date = $this->job->vessel_date?->format('Y-m-d');
        $this->port_loading = (string) $this->job->port_loading;
        $this->port_discharge = (string) $this->job->port_discharge;
        $this->mbl_no = (string) $this->job->mbl_no;
        $this->hbl_no = (string) $this->job->hbl_no;
        $this->cargo_description = (string) $this->job->cargo_description;
        $this->container_no = (string) $this->job->container_no;
        $this->quantity = (string) $this->job->quantity;
        $this->cusdec_no = (string) $this->job->cusdec_no;
        $this->customer_incentive = (string) $this->job->customer_incentive;
        $this->job_commission = (string) $this->job->job_commission;
        $this->remarks = (string) $this->job->remarks;
        $this->showJobEditForm = true;
        $this->dispatch('scroll-to-form', id: 'job-details-form');
    }

    public function saveJobDetails(): void
    {
        $data = $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'mode' => 'required|in:sea,air',
            'direction' => 'required|in:import,export',
            'vessel_flight' => 'nullable|string|max:255',
            'vessel_date' => 'nullable|date',
            'port_loading' => 'nullable|string|max:255',
            'port_discharge' => 'nullable|string|max:255',
            'mbl_no' => 'nullable|string|max:255',
            'hbl_no' => 'nullable|string|max:255',
            'cargo_description' => 'nullable|string|max:1000',
            'container_no' => 'nullable|string|max:255',
            'quantity' => 'nullable|string|max:255',
            'cusdec_no' => 'nullable|string|max:255',
            'customer_incentive' => 'nullable|numeric|min:0',
            'job_commission' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $data['customer_incentive'] = $data['customer_incentive'] ?: 0;
        $data['job_commission'] = $data['job_commission'] ?: 0;

        $this->job->update($data);
        $this->job->refresh();

        $this->showJobEditForm = false;
    }

    public function cancelJobEdit(): void
    {
        $this->showJobEditForm = false;
    }

    public function editCostLine(int $id): void
    {
        $line = $this->job->costLines()->findOrFail($id);
        $this->editingCostLineId = $line->id;
        $this->charge_type_id = $line->charge_type_id;
        $this->cost_description = (string) $line->description;
        $this->cost_amount = (string) $line->amount;
        $this->dispatch('scroll-to-form', id: 'cost-line-form');
    }

    public function saveCostLine(): void
    {
        $chargeType = $this->charge_type_id ? ChargeType::find($this->charge_type_id) : null;

        $this->validate([
            'charge_type_id' => 'nullable|exists:charge_types,id',
            'cost_description' => 'nullable|string|max:255',
            'cost_amount' => 'required|numeric|min:0.01',
        ]);

        if (! $chargeType && ! $this->cost_description) {
            $this->addError('cost_description', 'Choose a charge type or enter a free-text description.');

            return;
        }

        $data = [
            'charge_type_id' => $chargeType?->id,
            'kind' => $chargeType?->kind ?? 'service',
            'description' => $this->cost_description ?: null,
            'amount' => $this->cost_amount,
        ];

        if ($this->editingCostLineId) {
            $line = $this->job->costLines()->whereKey($this->editingCostLineId)->first();
            $line->update($data);
        } else {
            $line = $this->job->costLines()->create($data);
        }

        $line->syncInvoiceLine();

        $this->reset(['editingCostLineId', 'charge_type_id', 'cost_description', 'cost_amount']);
        $this->job->refresh();
    }

    public function cancelCostLine(): void
    {
        $this->reset(['editingCostLineId', 'charge_type_id', 'cost_description', 'cost_amount']);
    }

    public function showAddChargeType(): void
    {
        $this->showNewChargeTypeForm = true;
    }

    public function saveNewChargeType(): void
    {
        $data = $this->validate([
            'new_charge_type_name' => 'required|string|max:255',
            'new_charge_type_kind' => 'required|in:disbursement,service',
        ]);

        $nextSortOrder = (int) ChargeType::max('sort_order') + 1;

        $chargeType = ChargeType::create([
            'name' => $data['new_charge_type_name'],
            'kind' => $data['new_charge_type_kind'],
            'sort_order' => $nextSortOrder,
        ]);

        $this->charge_type_id = $chargeType->id;

        $this->reset(['showNewChargeTypeForm', 'new_charge_type_name']);
        $this->new_charge_type_kind = 'disbursement';
    }

    public function cancelNewChargeType(): void
    {
        $this->reset(['showNewChargeTypeForm', 'new_charge_type_name']);
        $this->new_charge_type_kind = 'disbursement';
    }

    public function removeCostLine(int $id): void
    {
        // The FK cascade on invoice_lines.job_cost_line_id removes the mirrored invoice
        // line (if any) automatically; the invoice just needs its totals recomputed.
        $this->job->costLines()->whereKey($id)->delete();
        $this->job->invoice?->recalculate();
        $this->job->refresh();
    }

    public function editServiceCost(int $id): void
    {
        $cost = $this->job->serviceCosts()->findOrFail($id);
        $this->editingServiceCostId = $cost->id;
        $this->service_cost_charge_type_id = $cost->charge_type_id;
        $this->service_cost_paid_to = (string) $cost->paid_to;
        $this->service_cost_description = (string) $cost->description;
        $this->service_cost_amount = (string) $cost->amount;
        $this->dispatch('scroll-to-form', id: 'service-cost-form');
    }

    public function saveServiceCost(): void
    {
        $data = $this->validate([
            'service_cost_charge_type_id' => 'nullable|exists:charge_types,id',
            'service_cost_paid_to' => 'nullable|string|max:255',
            'service_cost_description' => 'nullable|string|max:255',
            'service_cost_amount' => 'required|numeric|min:0.01',
        ]);

        $payload = [
            'charge_type_id' => $data['service_cost_charge_type_id'] ?: null,
            'paid_to' => $data['service_cost_paid_to'] ?: null,
            'description' => $data['service_cost_description'] ?: null,
            'amount' => $data['service_cost_amount'],
        ];

        if ($this->editingServiceCostId) {
            $this->job->serviceCosts()->whereKey($this->editingServiceCostId)->update($payload);
        } else {
            $this->job->serviceCosts()->create($payload);
        }

        $this->cancelServiceCost();
        $this->job->refresh();
    }

    public function cancelServiceCost(): void
    {
        $this->reset(['editingServiceCostId', 'service_cost_charge_type_id', 'service_cost_paid_to', 'service_cost_description', 'service_cost_amount']);
    }

    public function removeServiceCost(int $id): void
    {
        $this->job->serviceCosts()->whereKey($id)->delete();
        $this->job->refresh();
    }

    public function editAdvance(int $id): void
    {
        $advance = $this->job->advances()->findOrFail($id);
        $this->editingAdvanceId = $advance->id;
        $this->advance_type = $advance->type;
        $this->advance_amount = (string) $advance->amount;
        $this->advance_receipt_no = (string) $advance->receipt_no;
        $this->advance_name = (string) $advance->name;
        $this->advance_received_on = $advance->received_on->format('Y-m-d');
        $this->dispatch('scroll-to-form', id: 'advance-form');
    }

    public function saveAdvance(): void
    {
        $this->validate([
            'advance_type' => 'required|in:advance',
            'advance_amount' => 'required|numeric|min:0.01',
            'advance_receipt_no' => 'nullable|string|max:255',
            'advance_name' => 'nullable|string|max:255',
            'advance_received_on' => 'required|date',
        ]);

        $data = [
            'type' => $this->advance_type,
            'amount' => $this->advance_amount,
            'receipt_no' => $this->advance_receipt_no ?: null,
            'name' => $this->advance_name ?: null,
            'received_on' => $this->advance_received_on,
        ];

        if ($this->editingAdvanceId) {
            $this->job->advances()->whereKey($this->editingAdvanceId)->update($data);
        } else {
            $this->job->advances()->create($data);
        }

        $this->reset(['editingAdvanceId', 'advance_amount', 'advance_receipt_no', 'advance_name']);
        $this->advance_type = 'advance';
        $this->advance_received_on = now('Asia/Colombo')->format('Y-m-d');
        $this->job->refresh();
    }

    public function cancelAdvance(): void
    {
        $this->reset(['editingAdvanceId', 'advance_amount', 'advance_receipt_no', 'advance_name']);
        $this->advance_type = 'advance';
        $this->advance_received_on = now('Asia/Colombo')->format('Y-m-d');
    }

    public function removeAdvance(int $id): void
    {
        $this->job->advances()->whereKey($id)->delete();
        $this->job->refresh();
    }

    public function updateStatus(string $status): void
    {
        $this->job->update(['status' => $status]);
        $this->job->refresh();
    }

    public function render()
    {
        $costing = app(JobCosting::class);
        $invoice = $this->job->invoice;

        $totalCost = $costing->totalCost($this->job);

        return view('livewire.jobs.show', [
            'costLines' => $this->job->costLines()->with('chargeType')->latest()->get(),
            'serviceCosts' => $this->job->serviceCosts()->with('chargeType', 'costLine')->latest()->get(),
            'advances' => $this->job->advances()->latest()->get(),
            'chargeTypes' => ChargeType::where('is_active', true)->orderBy('sort_order')->get(),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            // Once invoiced, the invoice is the source of truth for what the job is worth
            // (it can be adjusted independently of the cost lines); before that, the cost
            // lines are the best estimate of what it will be billed for.
            'totalJobValue' => $invoice ? (string) $invoice->subtotal : $totalCost,
            'totalDisbursements' => $costing->totalDisbursements($this->job),
            'totalServices' => $costing->totalServices($this->job),
            'totalServiceCosts' => $costing->totalServiceCosts($this->job),
            'totalAdvances' => $costing->totalAdvances($this->job),
            'companyProfit' => $invoice ? $costing->companyProfit($this->job, (string) $invoice->subtotal) : null,
        ])->title('Job '.$this->job->job_no);
    }
}
