<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobServiceCost extends Model
{
    use SoftDeletes;

    protected $fillable = ['job_id', 'job_cost_line_id', 'charge_type_id', 'paid_to', 'description', 'amount'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    // Kept for rows recorded before internal service costs switched to categorizing by
    // charge type instead of linking a specific job cost line — see chargeType() below.
    public function costLine(): BelongsTo
    {
        return $this->belongsTo(JobCostLine::class, 'job_cost_line_id');
    }

    public function chargeType(): BelongsTo
    {
        return $this->belongsTo(ChargeType::class);
    }

    /**
     * The single label for this internal cost's category — the charge type it's tagged
     * with, falling back to the linked cost line's own label for older rows.
     */
    public function displayCategory(): string
    {
        return $this->chargeType?->name ?? $this->costLine?->displayDescription() ?? '—';
    }
}
