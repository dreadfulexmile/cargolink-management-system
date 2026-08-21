<?php

namespace App\Services;

use App\Models\Job;
use Illuminate\Support\Facades\DB;

class JobNumberGenerator
{
    public function next(string $direction, string $mode): string
    {
        return DB::transaction(function () use ($direction, $mode) {
            $sequence = Job::lockForUpdate()->count() + 1;

            $now = now('Asia/Colombo');
            $directionAbbr = strtoupper(substr($direction, 0, 3));
            $modeAbbr = strtoupper($mode);

            return sprintf(
                '%s/%s/%s/%s/%s',
                $directionAbbr,
                $modeAbbr,
                $now->format('y'),
                $now->format('m'),
                str_pad((string) $sequence, 4, '0', STR_PAD_LEFT)
            );
        });
    }
}
