<?php

namespace App\Services;

use App\Models\Job;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class JobNumberGenerator
{
    public const SETTING_KEY = 'job_number_next_sequence';

    public function next(string $direction, string $mode): string
    {
        return DB::transaction(function () use ($direction, $mode) {
            $sequence = $this->takeNextSequence();

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

    /**
     * A persisted, row-locked counter — not "count the table and add one".
     * That used to be the whole mechanism, but a deleted job now (with soft
     * deletes) drops out of the count, so the next generated number could
     * collide with an existing one. This can't collide, and it's what lets
     * a GM bump the starting number to continue a paper ledger's numbering
     * (see the Numbering settings screen) instead of restarting at 1.
     */
    private function takeNextSequence(): int
    {
        $setting = Setting::query()->lockForUpdate()->where('key', self::SETTING_KEY)->first();

        if (! $setting) {
            // First run after upgrading from the old count()-based scheme —
            // seed from whatever's already in the table (including
            // soft-deleted rows) so existing installs continue seamlessly
            // instead of restarting at 1.
            $setting = Setting::create(['key' => self::SETTING_KEY, 'value' => (string) $this->highestExistingSequence()]);
        }

        $next = ((int) $setting->value) + 1;
        $setting->update(['value' => (string) $next]);

        return $next;
    }

    private function highestExistingSequence(): int
    {
        return Job::withTrashed()->pluck('job_no')
            ->map(fn (string $jobNo) => (int) substr($jobNo, -4))
            ->max() ?? 0;
    }
}
