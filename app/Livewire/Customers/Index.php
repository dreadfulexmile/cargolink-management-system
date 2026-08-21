<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Customers')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $address = '';

    public string $contact_person = '';

    public string $phone = '';

    public string $email = '';

    public int $credit_days = 30;

    public ?string $credit_limit = null;

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'credit_days' => 'required|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $customer = Customer::findOrFail($id);
        $this->editingId = $customer->id;
        $this->name = $customer->name;
        $this->address = (string) $customer->address;
        $this->contact_person = (string) $customer->contact_person;
        $this->phone = (string) $customer->phone;
        $this->email = (string) $customer->email;
        $this->credit_days = $customer->credit_days;
        $this->credit_limit = $customer->credit_limit !== null ? (string) $customer->credit_limit : null;
        $this->is_active = $customer->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        // 'nullable' only skips other rules for blanks — it doesn't turn '' into null,
        // so an emptied field must be normalized before hitting the nullable decimal column.
        $data['credit_limit'] = $data['credit_limit'] !== '' ? $data['credit_limit'] : null;

        if ($this->editingId) {
            Customer::findOrFail($this->editingId)->update($data);
        } else {
            Customer::create($data);
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $customer = Customer::findOrFail($id);
        $customer->update(['is_active' => ! $customer->is_active]);
    }

    // Customers with job history are never hard-deleted — jobs/invoices cascade off
    // customer_id, so removing one would silently wipe real accounting records.
    // Deactivating (toggleActive) is the correct way to retire a customer like that.
    public function deleteCustomer(int $id): void
    {
        $customer = Customer::withCount('jobs')->findOrFail($id);

        if ($customer->jobs_count > 0) {
            return;
        }

        $customer->delete();

        if ($this->editingId === $id) {
            $this->cancel();
        }
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'name', 'address', 'contact_person', 'phone',
            'email', 'credit_days', 'credit_limit', 'is_active',
        ]);
        $this->credit_days = 30;
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $customers = Customer::query()
            ->withCount('jobs')
            ->withSum('invoices as outstanding_balance', 'balance_due')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.customers.index', ['customers' => $customers]);
    }
}
