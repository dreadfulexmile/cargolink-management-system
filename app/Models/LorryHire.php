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
        'from_location', 'to_location', 'distance_km', 'started_at', 'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'amount' => 'decimal:2',
            'held_hours' => 'decimal:2',
            'held_hourly_rate' => 'decimal:2',
            'held_fee' => 'decimal:2',
            'distance_km' => 'decimal:2',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function lorry(): BelongsTo
    {
        return $this->belongsTo(Lorry::class);
    }
}
