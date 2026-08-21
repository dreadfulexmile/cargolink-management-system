<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LorryExpense extends Model
{
    use SoftDeletes;

    public const CATEGORIES = ['lease', 'diesel', 'repair', 'maintenance', 'driver_fee', 'yard_ot', 'other'];

    protected $fillable = ['lorry_id', 'category', 'expense_date', 'amount', 'notes'];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function lorry(): BelongsTo
    {
        return $this->belongsTo(Lorry::class);
    }
}
