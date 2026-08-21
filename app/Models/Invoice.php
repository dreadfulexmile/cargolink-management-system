<?php

namespace App\Models;

use App\Models\Concerns\CascadesSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use CascadesSoftDeletes, SoftDeletes;

    protected $fillable = [
        'invoice_no', 'job_id', 'customer_id', 'invoice_date',
        'subtotal', 'advance_total', 'balance_due', 'status',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'subtotal' => 'decimal:2',
            'advance_total' => 'decimal:2',
            'balance_due' => 'decimal:2',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentsTotal(): string
    {
        return $this->payments->reduce(
            fn (string $carry, Payment $payment) => bcadd($carry, (string) $payment->amount, 2),
            '0.00'
        );
    }

    /**
     * Recomputes subtotal/balance/status from the current lines and payments. Called after
     * any line or payment changes, whether made on the invoice itself or mirrored in from the job.
     */
    public function recalculate(): void
    {
        $subtotal = (string) $this->lines()->sum('amount');
        $paid = (string) $this->payments()->sum('amount');
        $balance = bcsub($subtotal, bcadd((string) $this->advance_total, $paid, 2), 2);

        $status = match (true) {
            bccomp($balance, '0', 2) <= 0 => 'paid',
            bccomp($paid, '0', 2) > 0 || bccomp((string) $this->advance_total, '0', 2) > 0 => 'part_paid',
            default => 'unpaid',
        };

        $this->update([
            'subtotal' => $subtotal,
            'balance_due' => max($balance, '0.00'),
            'status' => $status,
        ]);

        $this->refresh();
    }

    protected function cascadeDeletes(): array
    {
        return ['lines', 'payments'];
    }
}
