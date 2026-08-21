<?php

namespace App\Livewire\Jobs;

use App\Livewire\Concerns\HasDateRangeFilter;
use App\Models\Customer;
use App\Models\Job;
use App\Services\JobNumberGenerator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Jobs')]
class Index extends Component
{
    use WithPagination;
    use HasDateRangeFilter;

    public bool $showForm = false;

    #[Url]
    public ?string $filterCustomer = null;

    public ?string $filterMode = null;

    public ?string $filterDirection = null;

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

    public function mount(): void
    {
        // Arriving with a customer filter already set (e.g. from the customer's job
        // count link) means "show all their jobs" — don't also clamp to a date range.
        if (! $this->filterCustomer) {
            $this->initDateRange();
        }
    }

    public function updatedDateFrom(): void
    {
        $this->clampDateRange();
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->clampDateRange();
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
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
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['job_no'] = app(JobNumberGenerator::class)->next($data['direction'], $data['mode']);
        $data['customer_incentive'] = $data['customer_incentive'] ?: 0;
        $data['job_commission'] = $data['job_commission'] ?: 0;

        $job = Job::create($data);

        $this->showForm = false;
        $this->resetForm();
        $this->redirect(route('jobs.show', $job), navigate: true);
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'customer_id', 'mode', 'direction', 'vessel_flight', 'vessel_date',
            'port_loading', 'port_discharge', 'mbl_no', 'hbl_no', 'cargo_description',
            'container_no', 'quantity', 'cusdec_no', 'customer_incentive', 'job_commission', 'remarks',
        ]);
        $this->mode = 'sea';
        $this->direction = 'import';
        $this->customer_incentive = '0';
        $this->job_commission = '0';
        $this->resetValidation();
    }

    public function render()
    {
        $jobs = Job::query()
            ->with('customer')
            ->when($this->filterCustomer, fn ($q) => $q->where('customer_id', $this->filterCustomer))
            ->when($this->dateFrom && $this->dateTo, fn ($q) => $q->whereBetween('created_at', [$this->dateRangeStart(), $this->dateRangeEnd()]))
            ->when($this->filterMode, fn ($q) => $q->where('mode', $this->filterMode))
            ->when($this->filterDirection, fn ($q) => $q->where('direction', $this->filterDirection))
            ->latest()
            ->paginate(15);

        return view('livewire.jobs.index', [
            'jobs' => $jobs,
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
