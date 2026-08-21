<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChargeType extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'kind', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function costLines(): HasMany
    {
        return $this->hasMany(JobCostLine::class);
    }
}
