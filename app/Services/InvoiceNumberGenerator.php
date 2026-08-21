<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceNumberGenerator
{
    public function next(): string
    {
        return DB::transaction(function () {
            $sequence = Invoice::lockForUpdate()->count() + 1;
            $now = now('Asia/Colombo');

            return sprintf('INV/%s/%s/%s', $now->format('y'), $now->format('m'), str_pad((string) $sequence, 4, '0', STR_PAD_LEFT));
        });
    }
}
