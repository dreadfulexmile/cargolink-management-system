<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DirectorTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = ['txn_date', 'description', 'debit', 'credit'];

    protected function casts(): array
    {
        return [
            'txn_date' => 'date',
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }
}
