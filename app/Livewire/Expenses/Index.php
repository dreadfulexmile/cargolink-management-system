<?php

namespace App\Livewire\Expenses;

use App\Livewire\Concerns\HasDateRangeFilter;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Expenses')]
class Index extends Component
{
    use WithPagination;
    use HasDateRangeFilter;

    public bool $showForm = false;

    public ?int $editingId = null;

    public int $expense_category_id = 0;

    public string $expense_date = '';

    public string $amount = '';

    public string $payee = '';

    public string $note = '';

    public function mount(): void
    {
        $this->initDateRange();
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

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $expense = Expense::findOrFail($id);
        $this->editingId = $expense->id;
        $this->expense_category_id = $expense->expense_category_id;
        $this->expense_date = $expense->expense_date->format('Y-m-d');
        $this->amount = (string) $expense->amount;
        $this->payee = (string) $expense->payee;
        $this->note = (string) $expense->note;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payee' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:500',
        ]);

        if ($this->editingId) {
            Expense::findOrFail($this->editingId)->update($data);
        } else {
            Expense::create($data);
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        Expense::findOrFail($id)->delete();
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'expense_category_id', 'amount', 'payee', 'note']);
        $this->expense_date = now('Asia/Colombo')->format('Y-m-d');
        $this->resetValidation();
    }

    public function render()
    {
        $query = Expense::query()
            ->whereBetween('expense_date', [$this->dateRangeStart()->toDateString(), $this->dateRangeEnd()->toDateString()]);

        $rangeTotal = (clone $query)->sum('amount');

        $expenses = $query->with('category')->latest('expense_date')->paginate(20);

        return view('livewire.expenses.index', [
            'expenses' => $expenses,
            'categories' => ExpenseCategory::where('is_active', true)->orderBy('group')->orderBy('name')->get(),
            'rangeTotal' => $rangeTotal,
        ]);
    }
}
