<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LorryHire extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lorry_id', 'hire_date', 'hirer_name', 'amount',
        'held_hours', 'held_hourly_rate', 'held_fee', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'amount' => 'decimal:2',
            'held_hours' => 'decimal:2',
            'held_hourly_rate' => 'decimal:2',
            'held_fee' => 'decimal:2',
        ];
    }

    public function lorry(): BelongsTo
    {
        return $this->belongsTo(Lorry::class);
    }
}
