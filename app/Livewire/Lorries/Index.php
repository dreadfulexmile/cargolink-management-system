<?php

namespace App\Livewire\Lorries;

use App\Livewire\Concerns\HasDateRangeFilter;
use App\Models\Lorry;
use App\Models\LorryExpense;
use App\Models\LorryHire;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Lorries')]
class Index extends Component
{
    use HasDateRangeFilter, WithPagination;

    public bool $showLorryForm = false;

    public ?int $editingLorryId = null;

    public string $reg_no = '';

    public string $name = '';

    public ?int $activeLorryId = null;

    // Which sub-panel is showing for the active lorry: 'income' or 'expenses'.
    public string $lorryTab = 'income';

    // Hire history — deliberately independent of the date-range filter
    // above (which only scopes the period stat cards): this is the full,
    // searchable, all-time log of hires for the active lorry.
    public string $hireHistorySearch = '';

    // Expense history — same idea, filterable by category since that's a
    // fixed set rather than free text.
    public string $expenseHistoryCategory = '';

    // Hire income form
    public ?int $hiringLorryId = null;

    public ?int $editingHireId = null;

    public string $hire_date = '';

    public string $hirer_name = '';

    public string $hire_amount = '';

    // Held-up fee — charged when the lorry is held at the customer's premises
    // beyond the agreed time (e.g. customer's own loading/unloading delay).
    // Billed hourly and stored on the hire itself, since it's part of what
    // the customer is invoiced, not a running cost of the lorry.
    public string $held_hours = '';

    public string $held_hourly_rate = '';

    public string $hire_notes = '';

    // Trip details — all optional, only used to fill out the client-facing receipt.
    public string $hire_from_location = '';

    public string $hire_to_location = '';

    public string $hire_distance_km = '';

    public string $hire_started_at = '';

    public string $hire_ended_at = '';

    // Optional running costs entered alongside a new hire — create their own
    // LorryExpense rows on save rather than being stored on the hire itself.
    public string $hire_diesel = '';

    public string $hire_driver_fee = '';

    public string $hire_yard_ot = '';

    public string $hire_other = '';

    // Expense form
    public ?int $expensingLorryId = null;

    public ?int $editingExpenseId = null;

    public string $expense_category = 'diesel';

    public string $expense_date = '';

    public string $expense_amount = '';

    public string $expense_notes = '';

    public function mount(): void
    {
        $this->activeLorryId = Lorry::orderBy('id')->value('id');
        $this->initDateRange();
    }

    /**
     * Scope a hires/expenses query builder to the selected date range.
     */
    private function applyDateRangeFilter($query, string $column)
    {
        return $query->whereBetween($column, [$this->dateRangeStart()->toDateString(), $this->dateRangeEnd()->toDateString()]);
    }

    public function selectLorry(int $id): void
    {
        $this->activeLorryId = $id;
        $this->lorryTab = 'income';
        $this->hireHistorySearch = '';
        $this->expenseHistoryCategory = '';
        $this->resetPage('hirePage');
        $this->resetPage('expensePage');
        $this->cancelHire();
        $this->cancelExpense();
    }

    public function selectLorryTab(string $tab): void
    {
        $this->lorryTab = $tab;

        if ($tab !== 'income') {
            $this->cancelHire();
        }

        if ($tab !== 'expenses') {
            $this->cancelExpense();
        }
    }

    public function updatedHireHistorySearch(): void
    {
        $this->resetPage('hirePage');
    }

    public function updatedExpenseHistoryCategory(): void
    {
        $this->resetPage('expensePage');
    }

    public function createLorry(): void
    {
        $this->reset(['editingLorryId', 'reg_no', 'name']);
        $this->showLorryForm = true;
    }

    // Rename/deactivate/delete all live in the "Manage Lorries" modal now, not
    // inline on the dashboard — this just opens that modal's form for one lorry.
    public function editLorry(int $id): void
    {
        $lorry = Lorry::findOrFail($id);
        $this->editingLorryId = $lorry->id;
        $this->reg_no = $lorry->reg_no;
        $this->name = (string) $lorry->name;
        $this->showLorryForm = true;
    }

    public function cancelLorryForm(): void
    {
        $this->showLorryForm = false;
        $this->reset(['editingLorryId', 'reg_no', 'name']);
        $this->resetValidation(['reg_no', 'name']);
    }

    public function saveLorry(): void
    {
        $data = $this->validate([
            'reg_no' => 'required|string|max:255|unique:lorries,reg_no,'.($this->editingLorryId ?? 'NULL'),
            'name' => 'nullable|string|max:255',
        ]);

        $data['name'] = $data['name'] !== '' ? $data['name'] : null;

        if ($this->editingLorryId) {
            $lorry = Lorry::findOrFail($this->editingLorryId);
            $lorry->update($data);
        } else {
            $lorry = Lorry::create($data);
        }

        // Newly added lorries land as the selected tab, right after the ones already there.
        $this->activeLorryId = $lorry->id;
        $this->showLorryForm = false;
        $this->reset(['editingLorryId', 'reg_no', 'name']);
        $this->lorryTab = 'income';
    }

    public function toggleActive(int $id): void
    {
        $lorry = Lorry::findOrFail($id);
        $lorry->update(['is_active' => ! $lorry->is_active]);
    }

    public function deleteLorry(int $id): void
    {
        Lorry::findOrFail($id)->delete();

        if ($this->activeLorryId === $id) {
            $this->activeLorryId = Lorry::orderBy('id')->value('id');
        }

        if ($this->editingLorryId === $id) {
            $this->cancelLorryForm();
        }
    }

    public function addHire(int $lorryId): void
    {
        $this->cancelExpense();
        $this->hiringLorryId = $lorryId;
        $this->editingHireId = null;
        $this->hire_date = now('Asia/Colombo')->format('Y-m-d');
        $this->reset([
            'hirer_name', 'hire_amount', 'held_hours', 'held_hourly_rate', 'hire_notes',
            'hire_diesel', 'hire_driver_fee', 'hire_yard_ot', 'hire_other',
            'hire_from_location', 'hire_to_location', 'hire_distance_km', 'hire_started_at', 'hire_ended_at',
        ]);
    }

    public function editHire(int $hireId): void
    {
        $this->cancelExpense();
        $hire = LorryHire::findOrFail($hireId);
        $this->hiringLorryId = $hire->lorry_id;
        $this->editingHireId = $hire->id;
        $this->hire_date = $hire->hire_date->format('Y-m-d');
        $this->hirer_name = (string) $hire->hirer_name;
        $this->hire_amount = (string) $hire->amount;
        $this->held_hours = $hire->held_hours !== null ? (string) $hire->held_hours : '';
        $this->held_hourly_rate = $hire->held_hourly_rate !== null ? (string) $hire->held_hourly_rate : '';
        $this->hire_notes = (string) $hire->notes;
        $this->hire_from_location = (string) $hire->from_location;
        $this->hire_to_location = (string) $hire->to_location;
        $this->hire_distance_km = $hire->distance_km !== null ? (string) $hire->distance_km : '';
        $this->hire_started_at = $hire->started_at?->format('Y-m-d\TH:i') ?? '';
        $this->hire_ended_at = $hire->ended_at?->format('Y-m-d\TH:i') ?? '';
        // Editing a hire never touches expenses — they're independent records.
        $this->reset(['hire_diesel', 'hire_driver_fee', 'hire_yard_ot', 'hire_other']);
    }

    /**
     * Held-up fee = hours the lorry was held at the customer's premises ×
     * the agreed hourly rate. Computed with bcmath, never floats.
     */
    public function getHeldFeePreviewProperty(): string
    {
        if ($this->held_hours === '' || $this->held_hourly_rate === '') {
            return '0.00';
        }

        return bcmul($this->held_hours, $this->held_hourly_rate, 2);
    }

    /**
     * +1 Hour on the open form, before the hire has been saved.
     */
    public function incrementFormHeldHour(): void
    {
        $this->held_hours = bcadd($this->held_hours !== '' ? $this->held_hours : '0', '1', 2);
    }

    public function saveHire(): void
    {
        $data = $this->validate([
            'hire_date' => 'required|date',
            'hirer_name' => 'nullable|string|max:255',
            'hire_amount' => 'required|numeric|min:0.01',
            'held_hours' => 'nullable|numeric|min:0',
            'held_hourly_rate' => 'nullable|numeric|min:0',
            'hire_notes' => 'nullable|string|max:1000',
            'hire_diesel' => 'nullable|numeric|min:0',
            'hire_driver_fee' => 'nullable|numeric|min:0',
            'hire_yard_ot' => 'nullable|numeric|min:0',
            'hire_other' => 'nullable|numeric|min:0',
            'hire_from_location' => 'nullable|string|max:255',
            'hire_to_location' => 'nullable|string|max:255',
            'hire_distance_km' => 'nullable|numeric|min:0',
            'hire_started_at' => 'nullable|date',
            'hire_ended_at' => 'nullable|date|after_or_equal:hire_started_at',
        ]);

        $isNewHire = ! $this->editingHireId;

        $heldHours = $data['held_hours'] !== '' ? $data['held_hours'] : null;
        $heldRate = $data['held_hourly_rate'] !== '' ? $data['held_hourly_rate'] : null;
        $heldFee = ($heldHours !== null && $heldRate !== null) ? bcmul($heldHours, $heldRate, 2) : '0.00';

        $hireData = [
            'lorry_id' => $this->hiringLorryId,
            'hire_date' => $data['hire_date'],
            'amount' => $data['hire_amount'],
            'held_hours' => $heldHours,
            'held_hourly_rate' => $heldRate,
            'held_fee' => $heldFee,
            'hirer_name' => $data['hirer_name'] !== '' ? $data['hirer_name'] : null,
            'notes' => $data['hire_notes'] !== '' ? $data['hire_notes'] : null,
            'from_location' => $data['hire_from_location'] !== '' ? $data['hire_from_location'] : null,
            'to_location' => $data['hire_to_location'] !== '' ? $data['hire_to_location'] : null,
            'distance_km' => $data['hire_distance_km'] !== '' ? $data['hire_distance_km'] : null,
            'started_at' => $data['hire_started_at'] !== '' ? $data['hire_started_at'] : null,
            'ended_at' => $data['hire_ended_at'] !== '' ? $data['hire_ended_at'] : null,
        ];

        if ($this->editingHireId) {
            LorryHire::findOrFail($this->editingHireId)->update($hireData);
        } else {
            LorryHire::create($hireData);
        }

        // Convenience: log the running costs for this trip as their own expense
        // entries at the same time, only when creating a fresh hire.
        if ($isNewHire) {
            foreach (['diesel' => 'hire_diesel', 'driver_fee' => 'hire_driver_fee', 'yard_ot' => 'hire_yard_ot', 'other' => 'hire_other'] as $category => $field) {
                if ($data[$field] !== '' && (float) $data[$field] > 0) {
                    LorryExpense::create([
                        'lorry_id' => $this->hiringLorryId,
                        'category' => $category,
                        'expense_date' => $data['hire_date'],
                        'amount' => $data[$field],
                        'notes' => null,
                    ]);
                }
            }
        }

        $this->resetPage('hirePage');
        $this->cancelHire();
    }

    public function cancelHire(): void
    {
        $this->hiringLorryId = null;
        $this->editingHireId = null;
        $this->reset([
            'hirer_name', 'hire_amount', 'held_hours', 'held_hourly_rate', 'hire_notes',
            'hire_diesel', 'hire_driver_fee', 'hire_yard_ot', 'hire_other',
            'hire_from_location', 'hire_to_location', 'hire_distance_km', 'hire_started_at', 'hire_ended_at',
        ]);
    }

    public function deleteHire(int $hireId): void
    {
        LorryHire::findOrFail($hireId)->delete();
        $this->resetPage('hirePage');
    }

    /**
     * +1 Hour on an already-saved hire — for a lorry still sitting held up,
     * so the fee can be topped up hour by hour without reopening the edit
     * form each time. Requires an hourly rate to already be set on the hire.
     */
    public function incrementHeldHour(int $hireId): void
    {
        $hire = LorryHire::findOrFail($hireId);

        if ($hire->held_hourly_rate === null) {
            return;
        }

        $hours = bcadd($hire->held_hours !== null ? (string) $hire->held_hours : '0', '1', 2);
        $fee = bcmul($hours, (string) $hire->held_hourly_rate, 2);

        $hire->update(['held_hours' => $hours, 'held_fee' => $fee]);

        // Keep an open edit form for this same hire in sync.
        if ($this->editingHireId === $hireId) {
            $this->held_hours = $hours;
        }
    }

    public function addExpense(int $lorryId): void
    {
        $this->cancelHire();
        $this->expensingLorryId = $lorryId;
        $this->editingExpenseId = null;
        $this->expense_date = now('Asia/Colombo')->format('Y-m-d');
        $this->expense_category = 'diesel';
        $this->reset(['expense_amount', 'expense_notes']);
    }

    public function editExpense(int $expenseId): void
    {
        $this->cancelHire();
        $expense = LorryExpense::findOrFail($expenseId);
        $this->expensingLorryId = $expense->lorry_id;
        $this->editingExpenseId = $expense->id;
        $this->expense_category = $expense->category;
        $this->expense_date = $expense->expense_date->format('Y-m-d');
        $this->expense_amount = (string) $expense->amount;
        $this->expense_notes = (string) $expense->notes;
    }

    public function saveExpense(): void
    {
        $data = $this->validate([
            'expense_category' => 'required|in:'.implode(',', LorryExpense::CATEGORIES),
            'expense_date' => 'required|date',
            'expense_amount' => 'required|numeric|min:0.01',
            'expense_notes' => 'nullable|string|max:1000',
        ]);

        $payload = [
            'lorry_id' => $this->expensingLorryId,
            'category' => $data['expense_category'],
            'expense_date' => $data['expense_date'],
            'amount' => $data['expense_amount'],
            'notes' => $data['expense_notes'] !== '' ? $data['expense_notes'] : null,
        ];

        if ($this->editingExpenseId) {
            LorryExpense::findOrFail($this->editingExpenseId)->update($payload);
        } else {
            LorryExpense::create($payload);
        }

        $this->resetPage('expensePage');
        $this->cancelExpense();
    }

    public function cancelExpense(): void
    {
        $this->expensingLorryId = null;
        $this->editingExpenseId = null;
        $this->reset(['expense_amount', 'expense_notes']);
        $this->expense_category = 'diesel';
    }

    public function deleteExpense(int $expenseId): void
    {
        LorryExpense::findOrFail($expenseId)->delete();
        $this->resetPage('expensePage');
    }

    public function render()
    {
        $lorries = Lorry::withCount(['hires as hires_count' => fn ($q) => $this->applyDateRangeFilter($q, 'hire_date')])
            ->withSum(['hires as total_hire_income' => fn ($q) => $this->applyDateRangeFilter($q, 'hire_date')], 'amount')
            ->withSum(['hires as total_held_fee' => fn ($q) => $this->applyDateRangeFilter($q, 'hire_date')], 'held_fee')
            ->withSum(['expenses as total_expenses' => fn ($q) => $this->applyDateRangeFilter($q, 'expense_date')], 'amount')
            ->withSum(['expenses as total_lease' => fn ($q) => $this->applyDateRangeFilter($q->where('category', 'lease'), 'expense_date')], 'amount')
            ->withSum(['expenses as total_diesel' => fn ($q) => $this->applyDateRangeFilter($q->where('category', 'diesel'), 'expense_date')], 'amount')
            ->withSum(['expenses as total_repair' => fn ($q) => $this->applyDateRangeFilter($q->where('category', 'repair'), 'expense_date')], 'amount')
            ->withSum(['expenses as total_maintenance' => fn ($q) => $this->applyDateRangeFilter($q->where('category', 'maintenance'), 'expense_date')], 'amount')
            ->withSum(['expenses as total_driver_fee' => fn ($q) => $this->applyDateRangeFilter($q->where('category', 'driver_fee'), 'expense_date')], 'amount')
            ->withSum(['expenses as total_yard_ot' => fn ($q) => $this->applyDateRangeFilter($q->where('category', 'yard_ot'), 'expense_date')], 'amount')
            ->orderBy('id')
            ->get();

        // Hire and expense history are deliberately their own queries,
        // independent of the date-range filter above — searchable/filterable
        // and paginated across every record ever entered for the active
        // lorry, not just the selected period's top 10.
        $hireHistory = $this->activeLorryId
            ? LorryHire::where('lorry_id', $this->activeLorryId)
                ->when($this->hireHistorySearch, fn ($q) => $q->where('hirer_name', 'like', "%{$this->hireHistorySearch}%"))
                ->orderByDesc('hire_date')
                ->orderByDesc('id')
                ->paginate(15, ['*'], 'hirePage')
            : null;

        $expenseHistory = $this->activeLorryId
            ? LorryExpense::where('lorry_id', $this->activeLorryId)
                ->when($this->expenseHistoryCategory, fn ($q) => $q->where('category', $this->expenseHistoryCategory))
                ->orderByDesc('expense_date')
                ->orderByDesc('id')
                ->paginate(15, ['*'], 'expensePage')
            : null;

        return view('livewire.lorries.index', [
            'lorries' => $lorries,
            'hireHistory' => $hireHistory,
            'expenseHistory' => $expenseHistory,
        ]);
    }
}
