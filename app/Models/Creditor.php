<?php

namespace App\Models;

use App\Models\Concerns\CascadesSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Creditor extends Model
{
    use CascadesSoftDeletes, SoftDeletes;

    protected $fillable = [
        'name', 'type', 'outstanding', 'note',
        'monthly_repayment', 'repayment_due_day', 'repayment_term_months',
    ];

    protected function casts(): array
    {
        return [
            'outstanding' => 'decimal:2',
            'monthly_repayment' => 'decimal:2',
            'repayment_due_day' => 'integer',
            'repayment_term_months' => 'integer',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CreditorPayment::class);
    }

    protected function cascadeDeletes(): array
    {
        return ['payments'];
    }
}
