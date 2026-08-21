<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceLine extends Model
{
    use SoftDeletes;

    protected $fillable = ['invoice_id', 'job_cost_line_id', 'description', 'amount', 'kind'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function jobCostLine(): BelongsTo
    {
        return $this->belongsTo(JobCostLine::class);
    }
}
