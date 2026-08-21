<?php

namespace App\Models;

use App\Models\Concerns\CascadesSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lorry extends Model
{
    use CascadesSoftDeletes, SoftDeletes;

    protected $fillable = ['reg_no', 'name', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function hires(): HasMany
    {
        return $this->hasMany(LorryHire::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(LorryExpense::class);
    }

    public function totalHireIncome(): string
    {
        return (string) $this->hires()->sum('amount');
    }

    public function totalExpenses(): string
    {
        return (string) $this->expenses()->sum('amount');
    }

    public function netResult(): string
    {
        return bcsub($this->totalHireIncome(), $this->totalExpenses(), 2);
    }

    protected function cascadeDeletes(): array
    {
        return ['hires', 'expenses'];
    }
}
