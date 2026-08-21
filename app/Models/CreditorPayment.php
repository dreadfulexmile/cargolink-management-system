<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditorPayment extends Model
{
    use SoftDeletes;

    protected $fillable = ['creditor_id', 'period', 'due_date', 'amount', 'paid_on'];

    protected function casts(): array
    {
        return [
            'period' => 'date',
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'paid_on' => 'date',
        ];
    }

    public function creditor(): BelongsTo
    {
        return $this->belongsTo(Creditor::class);
    }

    public function isOverdue(): bool
    {
        return $this->paid_on === null && $this->due_date !== null && $this->due_date->isPast();
    }
}
