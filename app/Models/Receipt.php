<?php

namespace App\Models;

use App\Models\Concerns\CascadesSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * The money a customer actually handed over in one go — a single cheque/bank
 * transfer/cash amount that can be split across several of their invoices.
 * See payments.receipt_id: each Payment carved out of this receipt still
 * belongs to exactly one invoice, this just groups the ones that came from
 * the same handover so recalculating/removing one is a single operation.
 */
class Receipt extends Model
{
    use CascadesSoftDeletes, SoftDeletes;

    protected $fillable = ['customer_id', 'amount', 'method', 'reference', 'received_on'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'received_on' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    protected function cascadeDeletes(): array
    {
        return ['payments'];
    }
}
