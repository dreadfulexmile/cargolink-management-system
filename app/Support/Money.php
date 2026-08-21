<?php

namespace App\Support;

class Money
{
    public static function format(string|int|float|null $amount, bool $cents = true, bool $symbol = true): string
    {
        $amount = bcadd((string) ($amount ?? '0'), '0', 2);
        $formatted = number_format((float) $amount, $cents ? 2 : 0);

        return $symbol ? 'Rs '.$formatted : $formatted;
    }
}
