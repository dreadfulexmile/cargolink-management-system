<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = ['invoice_id', 'receipt_id', 'amount', 'method', 'reference', 'paid_on'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_on' => 'date',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // Null when this was recorded directly against one invoice (the common case) —
    // only set when it's one slice of a customer-level Receipt split across invoices.
    public function receipt(): BelongsTo
    {
        return $this->belongsTo(Receipt::class);
    }
}
