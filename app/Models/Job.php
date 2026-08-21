<?php

namespace App\Models;

use App\Models\Concerns\CascadesSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use CascadesSoftDeletes, HasFactory, SoftDeletes;

    protected $fillable = [
        'job_no', 'mode', 'direction', 'customer_id', 'salesperson_id',
        'vessel_flight', 'vessel_date', 'port_loading', 'port_discharge',
        'mbl_no', 'hbl_no', 'cargo_description', 'container_no', 'quantity',
        'cusdec_no', 'status', 'customer_incentive', 'job_commission', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'vessel_date' => 'date',
            'customer_incentive' => 'decimal:2',
            'job_commission' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    public function costLines(): HasMany
    {
        return $this->hasMany(JobCostLine::class);
    }

    /**
     * The actual internal cost paid to deliver a service line (e.g. a subcontractor for
     * "TRANSPORT CHARGES") — never mirrored onto the invoice, only used to compute real profit.
     */
    public function serviceCosts(): HasMany
    {
        return $this->hasMany(JobServiceCost::class);
    }

    public function advances(): HasMany
    {
        return $this->hasMany(JobAdvance::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    protected function cascadeDeletes(): array
    {
        return ['advances', 'costLines', 'invoice', 'serviceCosts'];
    }
}
