<?php

namespace App\Models;

use App\Models\Concerns\CascadesSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobCostLine extends Model
{
    use CascadesSoftDeletes, SoftDeletes;

    protected $fillable = ['job_id', 'charge_type_id', 'kind', 'description', 'amount'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function chargeType(): BelongsTo
    {
        return $this->belongsTo(ChargeType::class);
    }

    public function invoiceLine(): HasOne
    {
        return $this->hasOne(InvoiceLine::class);
    }

    /**
     * The single canonical label for this cost line, used everywhere it's displayed
     * (job page, invoice draft, invoice PDF) so the job and its invoice never show
     * different wording for the same charge.
     */
    public function displayDescription(): string
    {
        return $this->description ?: ($this->chargeType?->name ?? 'Charge');
    }

    /**
     * Mirrors this cost line onto the job's invoice (if one already exists), so edits
     * made on the job after invoicing don't leave the invoice showing stale figures.
     */
    public function syncInvoiceLine(): void
    {
        $invoice = $this->job->invoice;

        if (! $invoice) {
            return;
        }

        $invoice->lines()->updateOrCreate(
            ['job_cost_line_id' => $this->id],
            ['description' => $this->displayDescription(), 'kind' => $this->kind, 'amount' => $this->amount]
        );

        $invoice->recalculate();
    }

    protected function cascadeDeletes(): array
    {
        return ['invoiceLine'];
    }
}
