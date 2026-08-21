<?php

namespace App\Livewire\Concerns;

use Carbon\Carbon;

/**
 * A "from / to" date filter capped at a one-year span, used in place of an
 * unbounded "All Time" option — an unrestricted range would mean uncapped
 * queries across every job/expense/invoice ever entered.
 */
trait HasDateRangeFilter
{
    public string $dateFrom = '';

    public string $dateTo = '';

    public ?string $dateRangeError = null;

    protected function initDateRange(): void
    {
        $this->dateFrom = now('Asia/Colombo')->startOfMonth()->format('Y-m-d');
        $this->dateTo = now('Asia/Colombo')->format('Y-m-d');
    }

    public function updatedDateFrom(): void
    {
        $this->clampDateRange();
    }

    public function updatedDateTo(): void
    {
        $this->clampDateRange();
    }

    /**
     * Keeps the range valid and within a one-year span — swaps reversed bounds and
     * pulls the far end back in rather than silently dropping the filter.
     */
    private function clampDateRange(): void
    {
        $this->dateRangeError = null;

        if (! $this->dateFrom || ! $this->dateTo) {
            return;
        }

        $from = Carbon::parse($this->dateFrom, 'Asia/Colombo');
        $to = Carbon::parse($this->dateTo, 'Asia/Colombo');

        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }

        if ($from->diffInDays($to) > 365) {
            $to = $from->copy()->addDays(365);
            $this->dateRangeError = 'Date range cannot exceed 1 year — end date adjusted.';
        }

        $this->dateFrom = $from->format('Y-m-d');
        $this->dateTo = $to->format('Y-m-d');
    }

    protected function dateRangeStart(): Carbon
    {
        return Carbon::parse($this->dateFrom, 'Asia/Colombo')->startOfDay();
    }

    protected function dateRangeEnd(): Carbon
    {
        return Carbon::parse($this->dateTo, 'Asia/Colombo')->endOfDay();
    }
}
