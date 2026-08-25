<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class InvoiceNumberGenerator
{
    public const SETTING_KEY = 'invoice_number_next_sequence';

    public function next(): string
    {
        return DB::transaction(function () {
            $sequence = $this->takeNextSequence();
            $now = now('Asia/Colombo');

            return sprintf('INV/%s/%s/%s', $now->format('y'), $now->format('m'), str_pad((string) $sequence, 4, '0', STR_PAD_LEFT));
        });
    }

    /**
     * See JobNumberGenerator::takeNextSequence() — same persisted, row-locked
     * counter, same reasoning (deletion-safe, and how the starting number
     * gets bumped to continue a paper ledger).
     */
    private function takeNextSequence(): int
    {
        $setting = Setting::query()->lockForUpdate()->where('key', self::SETTING_KEY)->first();

        if (! $setting) {
            $setting = Setting::create(['key' => self::SETTING_KEY, 'value' => (string) $this->highestExistingSequence()]);
        }

        $next = ((int) $setting->value) + 1;
        $setting->update(['value' => (string) $next]);

        return $next;
    }

    private function highestExistingSequence(): int
    {
        return Invoice::withTrashed()->pluck('invoice_no')
            ->map(fn (string $invoiceNo) => (int) substr($invoiceNo, -4))
            ->max() ?? 0;
    }
}
