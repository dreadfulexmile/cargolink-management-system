<?php

namespace App\Models;

use App\Models\Concerns\CascadesSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use CascadesSoftDeletes, SoftDeletes;

    protected $fillable = ['reg_no', 'has_lease', 'monthly_rental', 'lease_due_day', 'lease_term_months', 'is_active'];

    protected function casts(): array
    {
        return [
            'has_lease' => 'boolean',
            'monthly_rental' => 'decimal:2',
            'lease_due_day' => 'integer',
            'lease_term_months' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function leasePayments(): HasMany
    {
        return $this->hasMany(LeasePayment::class);
    }

    protected function cascadeDeletes(): array
    {
        return ['leasePayments'];
    }
}
